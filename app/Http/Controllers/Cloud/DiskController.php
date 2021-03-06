<?php

namespace App\Http\Controllers\Cloud;

use App\Core\Constructors\Controller\DataHandler;
use App\Core\Constructors\Controller\FilterConstructor;
use App\Core\Enums\ErrorCode;
use App\Exceptions\NoLoginException;
use App\Http\Controllers\Controller;

use App\Service\Disk\Config\DiskConfig;
use App\Service\Disk\DiskNodeEnum;
use App\Service\Disk\Other\DiskSign;
use App\Service\Disk\VendorEnum;
use ErrorException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Cloud\Disk;
use Illuminate\Support\Facades\DB;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * 磁盘
 * @date : 2022/4/21 19:26
 * @author : 孤鸿渺影
 */
class DiskController extends Controller
{

    /**
     * 列表
     * @Author:System Generate
     * @Date:2022-04-21 19:25:03
     * @return string
     */
    public function index(): string
    {
        return resourceConstructor(new Disk())->index();
    }

    /**
     * 获取当前用户的所有磁盘
     * @throws ErrorException
     * @throws InvalidArgumentException
     */
    public function getAllOnlineMemberDisk(Request $request): string
    {
        $model = new Disk();
        $filterConstructor = new FilterConstructor($model, $request, []);
        $model = $filterConstructor->filter([
            'user_uuid' => onlineMember()->getUuid()
        ]);
        $diskList = $model->get([
            'id',
            'uuid',
            'is_default',
            'max_size',
            'name',
            'node',
            'node as node_name',
            'user_uuid',
            'vendor',
            'vendor as vendor_name',
            'base_path',
            'bucket',
            'created_at',
            DB::raw('(select count(1) from resources where resources.disk_uuid = disk.uuid) as resources_count')
        ]);
        $diskConfig = (new DiskConfig(['is_default' => 1]))->toArray();
        $diskList = $diskList->toArray();
        foreach ($diskList as $key => $item) {
            if ($item['is_default']) {
                $diskList[$key]['vendor'] = $diskConfig['vendor'];
                $diskList[$key]['max_size'] = $diskConfig['max_size'];
                $diskList[$key]['node'] = $diskConfig['node'];
                $diskList[$key]['bucket'] = $diskConfig['bucket'];
                $diskList[$key]['base_path'] = $diskConfig['base_path'];
                $diskList[$key]['access_path'] = $diskConfig['access_path'];
                $diskList[$key]['node_name'] = DiskNodeEnum::getMessage($diskConfig['node']);
                $diskList[$key]['vendor_name'] = VendorEnum::getMessage($diskConfig['vendor']);
            }
        }
        return api_response_show($diskList);
    }

    /**
     * 获取供应商列表
     * @date : 2022/4/30 0:02
     * @return string
     * @author : 孤鸿渺影
     */
    public function getVendorList()
    {
        return api_response_show([
            'aliyun-oss' => '阿里云OSS'
        ]);
    }

    /**
     * 获取节点列表
     * @date : 2022/4/30 0:02
     * @return string
     * @author : 孤鸿渺影
     */
    public function getNodeList()
    {

        return api_response_show(['beijing' => '北京', 'shanghai' => '上海', 'tianjin' => '天津', 'chongqing' => '重庆', 'akesu' => '阿克苏', 'anning' => '安宁', 'anqing' => '安庆', 'anshan' => '鞍山', 'anshun' => '安顺', 'anyang' => '安阳', 'baicheng' => '白城', 'baishan' => '白山', 'baiyin' => '白银', 'bengbu' => '蚌埠', 'baoding' => '保定', 'baoji' => '宝鸡', 'baoshan' => '保山', 'bazhong' => '巴中', 'beihai' => '北海', 'benxi' => '本溪', 'binzhou' => '滨州', 'bole' => '博乐', 'bozhou' => '亳州', 'cangzhou' => '沧州', 'changde' => '常德', 'changji' => '昌吉', 'changshu' => '常熟', 'changzhou' => '常州', 'chaohu' => '巢湖', 'chaoyang' => '朝阳', 'chaozhou' => '潮州', 'chengde' => '承德', 'chengdu' => '成都', 'chenggu' => '城固', 'chenzhou' => '郴州', 'chibi' => '赤壁', 'chifeng' => '赤峰', 'chishui' => '赤水', 'chizhou' => '池州', 'chongzuo' => '崇左', 'chuxiong' => '楚雄', 'chuzhou' => '滁州', 'cixi' => '慈溪', 'conghua' => '从化', 'dali' => '大理', 'dalian' => '大连', 'dandong' => '丹东', 'danyang' => '丹阳', 'daqing' => '大庆', 'datong' => '大同', 'dazhou' => '达州', 'deyang' => '德阳', 'dezhou' => '德州', 'dongguan' => '东莞', 'dongyang' => '东阳', 'dongying' => '东营', 'douyun' => '都匀', 'dunhua' => '敦化', 'eerduosi' => '鄂尔多斯', 'enshi' => '恩施', 'fangchenggang' => '防城港', 'feicheng' => '肥城', 'fenghua' => '奉化', 'fushun' => '抚顺', 'fuxin' => '阜新', 'fuyang' => '阜阳', 'fuyang1' => '富阳', 'fuzhou' => '福州', 'fuzhou1' => '抚州', 'ganyu' => '赣榆', 'ganzhou' => '赣州', 'gaoming' => '高明', 'gaoyou' => '高邮', 'geermu' => '格尔木', 'gejiu' => '个旧', 'gongyi' => '巩义', 'guangan' => '广安', 'guangyuan' => '广元', 'guangzhou' => '广州', 'gubaotou' => '古包头', 'guigang' => '贵港', 'guilin' => '桂林', 'guiyang' => '贵阳', 'guyuan' => '固原', 'haerbin' => '哈尔滨', 'haicheng' => '海城', 'haikou' => '海口', 'haimen' => '海门', 'haining' => '海宁', 'hami' => '哈密', 'handan' => '邯郸', 'hangzhou' => '杭州', 'hanzhong' => '汉中', 'hebi' => '鹤壁', 'hefei' => '合肥', 'hengshui' => '衡水', 'hengyang' => '衡阳', 'hetian' => '和田', 'heyuan' => '河源', 'heze' => '菏泽', 'huadou' => '花都', 'huaian' => '淮安', 'huaibei' => '淮北', 'huaihua' => '怀化', 'huainan' => '淮南', 'huanggang' => '黄冈', 'huangshan' => '黄山', 'huangshi' => '黄石', 'huhehaote' => '呼和浩特', 'huizhou' => '惠州', 'huludao' => '葫芦岛', 'huzhou' => '湖州', 'jiamusi' => '佳木斯', 'jian' => '吉安', 'jiangdou' => '江都', 'jiangmen' => '江门', 'jiangyin' => '江阴', 'jiaonan' => '胶南', 'jiaozhou' => '胶州', 'jiaozuo' => '焦作', 'jiashan' => '嘉善', 'jiaxing' => '嘉兴', 'jiexiu' => '介休', 'jilin' => '吉林', 'jimo' => '即墨', 'jinan' => '济南', 'jincheng' => '晋城', 'jingdezhen' => '景德镇', 'jinghong' => '景洪', 'jingjiang' => '靖江', 'jingmen' => '荆门', 'jingzhou' => '荆州', 'jinhua' => '金华', 'jining1' => '集宁', 'jining' => '济宁', 'jinjiang' => '晋江', 'jintan' => '金坛', 'jinzhong' => '晋中', 'jinzhou' => '锦州', 'jishou' => '吉首', 'jiujiang' => '九江', 'jiuquan' => '酒泉', 'jixi' => '鸡西', 'jiyuan' => '济源', 'jurong' => '句容', 'kaifeng' => '开封', 'kaili' => '凯里', 'kaiping' => '开平', 'kaiyuan' => '开远', 'kashen' => '喀什', 'kelamayi' => '克拉玛依', 'kuerle' => '库尔勒', 'kuitun' => '奎屯', 'kunming' => '昆明', 'kunshan' => '昆山', 'laibin' => '来宾', 'laiwu' => '莱芜', 'laixi' => '莱西', 'laizhou' => '莱州', 'langfang' => '廊坊', 'lanzhou' => '兰州', 'lasa' => '拉萨', 'leshan' => '乐山', 'lianyungang' => '连云港', 'liaocheng' => '聊城', 'liaoyang' => '辽阳', 'liaoyuan' => '辽源', 'lijiang' => '丽江', 'linan' => '临安', 'lincang' => '临沧', 'linfen' => '临汾', 'lingbao' => '灵宝', 'linhe' => '临河', 'linxia' => '临夏', 'linyi' => '临沂', 'lishui' => '丽水', 'liuan' => '六安', 'liupanshui' => '六盘水', 'liuzhou' => '柳州', 'liyang' => '溧阳', 'longhai' => '龙海', 'longyan' => '龙岩', 'loudi' => '娄底', 'luohe' => '漯河', 'luoyang' => '洛阳', 'luxi' => '潞西', 'luzhou' => '泸州', 'lvliang' => '吕梁', 'lvshun' => '旅顺', 'maanshan' => '马鞍山', 'maoming' => '茂名', 'meihekou' => '梅河口', 'meishan' => '眉山', 'meizhou' => '梅州', 'mianxian' => '勉县', 'mianyang' => '绵阳', 'mudanjiang' => '牡丹江', 'nanan' => '南安', 'nanchang' => '南昌', 'nanchong' => '南充', 'nanjing' => '南京', 'nanning' => '南宁', 'nanping' => '南平', 'nantong' => '南通', 'nanyang' => '南阳', 'neijiang' => '内江', 'ningbo' => '宁波', 'ningde' => '宁德', 'panjin' => '盘锦', 'panzhihua' => '攀枝花', 'penglai' => '蓬莱', 'pingdingshan' => '平顶山', 'pingdu' => '平度', 'pinghu' => '平湖', 'pingliang' => '平凉', 'pingxiang' => '萍乡', 'pulandian' => '普兰店', 'puning' => '普宁', 'putian' => '莆田', 'puyang' => '濮阳', 'qiannan' => '黔南', 'qidong' => '启东', 'qingdao' => '青岛', 'qingyang' => '庆阳', 'qingyuan' => '清远', 'qingzhou' => '青州', 'qinhuangdao' => '秦皇岛', 'qinzhou' => '钦州', 'qionghai' => '琼海', 'qiqihaer' => '齐齐哈尔', 'quanzhou' => '泉州', 'qujing' => '曲靖', 'quzhou' => '衢州', 'rikaze' => '日喀则', 'rizhao' => '日照', 'rongcheng' => '荣成', 'rugao' => '如皋', 'ruian' => '瑞安', 'rushan' => '乳山', 'sanmenxia' => '三门峡', 'sanming' => '三明', 'sanya' => '三亚', 'xiamen' => '厦门', 'shan' => '佛山', 'shangluo' => '商洛', 'shangqiu' => '商丘', 'shangrao' => '上饶', 'shangyu' => '上虞', 'shantou' => '汕头', 'ankang' => '安康', 'shaoguan' => '韶关', 'shaoxing' => '绍兴', 'shaoyang' => '邵阳', 'shenyang' => '沈阳', 'shenzhen' => '深圳', 'shihezi' => '石河子', 'shijiazhuang' => '石家庄', 'shilin' => '石林', 'shishi' => '石狮', 'shiyan' => '十堰', 'shouguang' => '寿光', 'shuangyashan' => '双鸭山', 'shuozhou' => '朔州', 'shuyang' => '沭阳', 'simao' => '思茅', 'siping' => '四平', 'songyuan' => '松原', 'suining' => '遂宁', 'suizhou' => '随州', 'suzhou' => '苏州', 'tacheng' => '塔城', 'taian' => '泰安', 'taicang' => '太仓', 'taixing' => '泰兴', 'taiyuan' => '太原', 'taizhou' => '泰州', 'taizhou1' => '台州', 'tangshan' => '唐山', 'tengchong' => '腾冲', 'tengzhou' => '滕州', 'tianmen' => '天门', 'tianshui' => '天水', 'tieling' => '铁岭', 'tongchuan' => '铜川', 'tongliao' => '通辽', 'tongling' => '铜陵', 'tonglu' => '桐庐', 'tongren' => '铜仁', 'tongxiang' => '桐乡', 'tongzhou' => '通州', 'tonghua' => '通化', 'tulufan' => '吐鲁番', 'wafangdian' => '瓦房店', 'weifang' => '潍坊', 'weihai' => '威海', 'weinan' => '渭南', 'wendeng' => '文登', 'wenling' => '温岭', 'wenzhou' => '温州', 'wuhai' => '乌海', 'wuhan' => '武汉', 'wuhu' => '芜湖', 'wujiang' => '吴江', 'wulanhaote' => '乌兰浩特', 'wuwei' => '武威', 'wuxi' => '无锡', 'wuzhou' => '梧州', 'xian' => '西安', 'xiangcheng' => '项城', 'xiangfan' => '襄樊', 'xianggelila' => '香格里拉', 'xiangshan' => '象山', 'xiangtan' => '湘潭', 'xiangxiang' => '湘乡', 'xianning' => '咸宁', 'xiantao' => '仙桃', 'xianyang' => '咸阳', 'xicang' => '西藏', 'xichang' => '西昌', 'xingtai' => '邢台', 'xingyi' => '兴义', 'xining' => '西宁', 'xinxiang' => '新乡', 'xinyang' => '信阳', 'xinyu' => '新余', 'xinzhou' => '忻州', 'suqian' => '宿迁', 'suyu' => '宿豫', 'suzhou1' => '宿州', 'xuancheng' => '宣城', 'xuchang' => '许昌', 'xuzhou' => '徐州', 'yaan' => '雅安', 'yakeshi' => '牙克石', 'yanan' => '延安', 'yanbian' => '延边', 'yancheng' => '盐城', 'yangjiang' => '阳江', 'yangquan' => '阳泉', 'yangzhou' => '扬州', 'yanji' => '延吉', 'yantai' => '烟台', 'yanzhou' => '兖州', 'yibin' => '宜宾', 'yichang' => '宜昌', 'yichun' => '宜春', 'yichun1' => '伊春', 'yili' => '伊犁', 'yinchuan' => '银川', 'yingkou' => '营口', 'yingtan' => '鹰潭', 'yining' => '伊宁', 'yiwu' => '义乌', 'yixing' => '宜兴', 'yiyang' => '益阳', 'yongkang' => '永康', 'yongzhou' => '永州', 'yueyang' => '岳阳', 'yuhuan' => '玉环', 'yulin1' => '榆林', 'yulin' => '玉林', 'yuncheng' => '运城', 'yuxi' => '玉溪', 'yuyao' => '余姚', 'zaozhuang' => '枣庄', 'zengcheng' => '增城', 'changchun' => '长春', 'changhai' => '长海', 'zhangjiagang' => '张家港', 'zhangjiajie' => '张家界', 'zhangjiakou' => '张家口', 'changle' => '长乐', 'zhangqiu' => '章丘', 'changsha' => '长沙', 'zhangye' => '张掖', 'changzhi' => '长治', 'zhangzhou' => '漳州', 'zhanjiang' => '湛江', 'zhaodong' => '肇东', 'zhaoqing' => '肇庆', 'zhaotong' => '昭通', 'zhengzhou' => '郑州', 'zhenjiang' => '镇江', 'zhongshan' => '中山', 'zhoukou' => '周口', 'zhoushan' => '舟山', 'zhucheng' => '诸城', 'zhuhai' => '珠海', 'zhuji' => '诸暨', 'zhumadian' => '驻马店', 'zhuzhou' => '株洲', 'zibo' => '淄博', 'zigong' => '自贡', 'zunyi' => '遵义', 'wulumuqi' => '乌鲁木齐', 'fuqing' => '福清', 'ezhou' => '鄂州', 'baotou' => '包头', 'xiaoshan' => '萧山', 'xuanhua' => '宣化', 'jiangyou' => '江油', 'ziyang' => '资阳', 'xinji' => '辛集', 'foshan' => '佛山', 'wanzhou' => '万州', 'zoucheng' => '邹城', 'shaowu' => '邵武', 'jiangyan' => '姜堰', 'xiangyin' => '湘阴', 'songjiang' => '松江', 'qitaihe' => '七台河', 'liling' => '醴陵', 'fuling' => '涪陵', 'gongzhuling' => '公主岭', 'shexian' => '歙县', 'xinghua' => '兴化']);
    }

    /**
     * 创建
     * @Author:System Generate
     * @Date:2022-04-21 17:30:44
     * @return string
     */
    public function storeCustomer(): string
    {
        return (new DiskSign())->customer();
    }

    /**
     * 领取系统默认磁盘
     * @date : 2022/4/21 19:17
     * @return string
     * @author : 孤鸿渺影
     */
    public function storeDefault(): string
    {
        return (new DiskSign())->default();
    }

    /**
     * 详情(多条件)
     * @Author:System Generate
     * @Date:2022-04-21 19:25:03
     * @param Request $request
     * @return string
     */
    public function get(Request $request): string
    {
        return resourceConstructor(new Disk())->get($request);
    }

    /**
     * 详情
     * @Author:System Generate
     * @Date:2022-04-21 19:25:03
     * @param $id
     * @return string
     */
    public function show($id): string
    {
        return resourceConstructor(new Disk())->show($id);
    }

    /**
     * 修改
     * @Author:System Generate
     * @Date:2022-04-21 19:25:03
     * @param Request $request
     * @param $id
     * @return string
     */
    public function update(Request $request, $id): string
    {
        $diskModel = new Disk();
        $validator = $diskModel->updateValidator($id);
        if (!$validator->isSuccess()) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_VALIDATE_ERROR, $validator->getMessage());
        }
        $data = $validator->getData();

        /* @var $config Model */

        $config = $diskModel->findIdOrUuid($id);
        if (!$config) {
            return api_response_action(false, ErrorCode::$ENUM_NO_DATA_ERROR, "磁盘不存在");
        }
        if ($config->access_key_id != $data['access_key_id']) {
            $data['access_key_id'] = maskCrypt()->encrypt($data['access_key_id']);
        }
        if ($config->access_key_secret != $data['access_key_secret']) {
            $data['access_key_secret'] = maskCrypt()->encrypt($data['access_key_secret']);
        }
        /* @var $diskModel Builder */
        $flag = $config->update($data);
        if ($flag) {
            systemConfig()->refresh();//刷新缓存
            return api_response_action(true, ErrorCode::$ENUM_SUCCESS, '修改成功');
        }
        return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR);
    }

    /**
     * 删除
     * @Author:System Generate
     * @Date:2022-04-21 19:25:03
     * @param $id
     * @return string
     */
    public function destroy($id): string
    {
        return resourceConstructor(new Disk())->destroy($id);
    }
}
