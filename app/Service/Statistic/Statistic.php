<?php


namespace App\Service\Statistic;


use App\Models\Blog\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Statistic
{


    private int $cacheTime = 6000;
    /**
     * 博客数量统计
     * Date : 2021/4/27 22:36
     * Author : 孤鸿渺影
     * @return array
     */
    public function blogStatisticTotalData(): array
    {
        $sql = 'SELECT
	( SELECT count( 1 ) FROM article ) AS article_total,
	( SELECT sum( visited ) FROM article ) AS article_visit_total,
	( SELECT count( 1 ) FROM comments ) AS comment_total,
	( SELECT count( 1 ) FROM article_likes ) AS article_like_total,
	( SELECT count( 1 ) FROM labels ) AS label_total,
	( SELECT count( 1 ) FROM log_visited ) AS visited_total';

        $data = DB::select($sql);
        $data = json_decode(json_encode($data), true);
        $data[0]['time'] = time();
        return $data[0];
    }

    /**
     * 访问时间节点统计
     * Date : 2021/4/26 14:12
     * Author : 孤鸿渺影
     * @return array
     */
    public function visitedLineChart()
    {

        $data = [
            'time' => time(),
            'yesterday' => $this->yesterdayVisitedLine(),
            'today' => $this->todayVisitedLine()
        ];
        return $data;
    }

    /**
     *
     * Date : 2021/4/28 11:44
     * Author : 孤鸿渺影
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function cacheVisitedLineChart()
    {
        Cache::tags('statistic')->set('visitedLineChart',$this->visitedLineChart(),$this->cacheTime);
    }

    /**
     *
     * @param array $list
     * Date : 2021/4/28 14:55
     * Author : 孤鸿渺影
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function refresh($list = [])
    {
        $refreshList = [];
        if(is_string($list)){
            array_push($refreshList,$list);
        }elseif (is_array($list)){
            $refreshList = $list;
        }
        foreach ($refreshList as $statistic){
            if(method_exists($this,$statistic)){
                Cache::tags('statistic')->set($statistic,$this->{$statistic}(),$this->cacheTime);
            }
        }
    }
    /**
     * 今日时段访问流量
     * Date : 2021/4/27 22:36
     * Author : 孤鸿渺影
     * @return array|mixed
     */
    private function todayVisitedLine()
    {
        $list = DB::select("SELECT from_unixtime(time,'%m-%d %H') time,count(1) total FROM `log_visited` where from_unixtime(time,'%m-%d %H') >= DATE_FORMAT(NOW() - interval 24 hour,'%m-%d %H')  GROUP BY from_unixtime(time,'%m-%d %H');");
        $list = json_decode(json_encode($list), true);
        $nowDay = date('H');
        $hasTime = [];
        $nowDate = date("m-d");
        $lastDate = date("m-d", strtotime("-1 day"));
        if (empty($list)) {
            $list[] = [
                'time' => $nowDate . ' ' . $nowDay
            ];
        }
        //检查缺失日期
        foreach ($list as $item) {
            $hasTime[] = explode(' ', $item['time'])[1];
        }
        //补全日期
        for ($i = 0; $i < 24; $i++) {
            if ($i < 10) {
                $i = '0' . $i;
            }
            if (!in_array($i, $hasTime)) {
                if ($i > $nowDay) {
                    $list[] = [
                        'time' => $lastDate . " " . $i,
                        'total' => 0
                    ];
                } else {
                    $list[] = [
                        'time' => $nowDate . " " . $i,
                        'total' => 0
                    ];
                }
            }
        }
        //排序日期
        $len = count($list);
        for ($i = 0; $i < $len - 1; $i++) {
            for ($j = 0; $j < $len - $i - 1; $j++) {
                if ($list[$j]['time'] > $list[$j + 1]['time']) {

                    $tmp = $list[$j];
                    $list[$j] = $list[$j + 1];
                    $list[$j + 1] = $tmp;
                }
            }
        }
        return $list;
    }

    /**
     * 昨日时段访问流量
     * Date : 2021/4/27 22:35
     * Author : 孤鸿渺影
     * @return array|mixed
     */
    private function yesterdayVisitedLine()
    {
        $list = DB::select("SELECT from_unixtime( time, '%m-%d %H' ) time, count( 1 ) total FROM `log_visited` WHERE from_unixtime( time, '%m-%d %H' ) >= DATE_FORMAT( NOW( ) - INTERVAL 48 HOUR, '%m-%d %H' ) AND from_unixtime( time, '%m-%d %H' ) < DATE_FORMAT( NOW( ) - INTERVAL 23 HOUR, '%m-%d %H' ) GROUP BY from_unixtime( time, '%m-%d %H' );");
        $list = json_decode(json_encode($list), true);

        $nowDay = date('H');
        $hasTime = [];
        $nowDate = date("m-d", strtotime("-1 day"));
        $lastDate = date("m-d", strtotime("-2 day"));
        if (empty($list)) {
            $list[] = [
                'time' => $nowDate . ' ' . $nowDay
            ];
        }
        //检查缺失日期
        foreach ($list as $item) {
            $hasTime[] = explode(' ', $item['time'])[1];
        }
        //补全日期
        for ($i = 0; $i < 24; $i++) {
            if ($i < 10) {
                $i = '0' . $i;
            }
            if (!in_array($i, $hasTime)) {
                if ($i > $nowDay) {
                    $list[] = [
                        'time' => $lastDate . " " . $i,
                        'total' => 0
                    ];
                } else {
                    $list[] = [
                        'time' => $nowDate . " " . $i,
                        'total' => 0
                    ];
                }
            }
        }
        //排序日期
        $len = count($list);
        for ($i = 0; $i < $len - 1; $i++) {
            for ($j = 0; $j < $len - $i - 1; $j++) {
                if ($list[$j]['time'] > $list[$j + 1]['time']) {

                    $tmp = $list[$j];
                    $list[$j] = $list[$j + 1];
                    $list[$j + 1] = $tmp;
                }
            }
        }
        return $list;
    }

    /**
     * 文章分类统计
     * Date : 2021/4/26 14:12
     * Author : 孤鸿渺影
     * @return array
     */
    public function articleCategoryChart()
    {
        $list = DB::select('SELECT data_table.category_name,data_table.total FROM (SELECT category.category_name category_name,count( 1 ) total FROM article RIGHT JOIN category ON article.category_uuid = category.category_uuid GROUP BY category.category_name ) data_table LIMIT 5');
        $list = json_decode(json_encode($list), true);
        $count = (new Article())->count();
        $nowListCount = 0;
        foreach ($list as $item) {
            $nowListCount += $item['total'];
        }
        if ($nowListCount < $count) {
            $list[] = [
                'category_name' => '其它',
                'total' => $count - $nowListCount
            ];
        }
        $data = [
            'time' => time(),
            'list' => $list
        ];
        return $data;
    }


    /**
     * 用户省份分布图
     * Date : 2021/4/26 14:11
     * Author : 孤鸿渺影
     * @return array
     */
    public function userProvinceChart()
    {
        $list = DB::select('SELECT map_province.province_name,count( 1 ) total FROM users RIGHT JOIN map_province ON LEFT ( users.user_address, 2 ) = map_province.province_id GROUP BY map_province.province_name;');
        $data = [
            'time' => time(),
            'list' => $list
        ];
        return $data;
    }


    public function newTotal()
    {
        $sql = 'SELECT
	( SELECT count( 1 ) FROM log_visited WHERE time > UNIX_TIMESTAMP( CAST( SYSDATE( ) AS DATE ) ) ) AS new_visited,
	( SELECT count( 1 ) FROM article WHERE created_at > UNIX_TIMESTAMP( CAST( SYSDATE( ) AS DATE ) ) ) AS new_article,
	( SELECT count( 1 ) FROM article_likes WHERE created_at > UNIX_TIMESTAMP( CAST( SYSDATE( ) AS DATE ) ) ) AS new_article_like,
	( SELECT count( 1 ) FROM comments WHERE created_at > UNIX_TIMESTAMP( CAST( SYSDATE( ) AS DATE ) ) ) AS new_comment,
	( SELECT count( 1 ) FROM users WHERE created_at > UNIX_TIMESTAMP( CAST( SYSDATE( ) AS DATE ) ) ) AS new_user,
	( SELECT count( 1 ) FROM leaves WHERE created_at > UNIX_TIMESTAMP( CAST( SYSDATE( ) AS DATE ) ) ) AS new_leave';

        $data = DB::select($sql);
        $data = json_decode(json_encode($data), true);
        $data = [
            'time' => time(),
            'data' => $data[0]
        ];
        return $data;

    }
}