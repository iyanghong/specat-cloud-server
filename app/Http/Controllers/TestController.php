<?php

namespace App\Http\Controllers;

use App\Core\Enums\ErrorCode;
use App\Models\Cloud\Resource;
use App\Models\User;
use App\Service\Disk\Config\DiskConfig;
use App\Service\Disk\DiskFactory;
use App\Service\Disk\DiskNodeEnum;
use App\Service\Disk\VendorEnum;


class TestController extends Controller
{

    public function main()
    {
        $resourcesModel = new Resource();
        $resource = $resourcesModel->findIdOrUuid('844065c8c49b11ec8b120242ac110002');
        var_dump($resource->getResourceDirectory());

//        return api_response_show($list);

    }

    public function resolveCity()
    {
        $str = "北京 BEIJING
上海 SHANGHAI
天津 TIANJIN
重庆 CHONGQING
阿克苏 AKESU
安宁 ANNING
安庆 ANQING
鞍山 ANSHAN
安顺 ANSHUN
安阳 ANYANG
白城 BAICHENG
白山 BAISHAN
白银 BAIYIN
蚌埠 BENGBU
保定 BAODING
宝鸡 BAOJI
保山 BAOSHAN
巴中 BAZHONG
北海 BEIHAI
本溪 BENXI
滨州 BINZHOU
博乐 BOLE
亳州 BOZHOU
沧州 CANGZHOU
常德 CHANGDE
昌吉 CHANGJI
常熟 CHANGSHU
常州 CHANGZHOU
巢湖 CHAOHU
朝阳 CHAOYANG
潮州 CHAOZHOU
承德 CHENGDE
成都 CHENGDU
城固 CHENGGU
郴州 CHENZHOU
赤壁 CHIBI
赤峰 CHIFENG
赤水 CHISHUI
池州 CHIZHOU
崇左 CHONGZUO
楚雄 CHUXIONG
滁州 CHUZHOU
慈溪 CIXI
从化 CONGHUA
大理 DALI
大连 DALIAN
丹东 DANDONG
丹阳 DANYANG
大庆 DAQING
大同 DATONG
达州 DAZHOU
德阳 DEYANG
德州 DEZHOU
东莞 DONGGUAN
东阳 DONGYANG
东营 DONGYING
都匀 DOUYUN
敦化 DUNHUA
鄂尔多斯 EERDUOSI
恩施 ENSHI
防城港 FANGCHENGGANG
肥城 FEICHENG
奉化 FENGHUA
抚顺 FUSHUN
阜新 FUXIN
阜阳 FUYANG
富阳 FUYANG1
福州 FUZHOU
抚州 FUZHOU1
赣榆 GANYU
赣州 GANZHOU
高明 GAOMING
高邮 GAOYOU
格尔木 GEERMU
个旧 GEJIU
巩义 GONGYI
广安 GUANGAN
广元 GUANGYUAN
广州 GUANGZHOU
古包头 GUBAOTOU
贵港 GUIGANG
桂林 GUILIN
贵阳 GUIYANG
固原 GUYUAN
哈尔滨 HAERBIN
海城 HAICHENG
海口 HAIKOU
海门 HAIMEN
海宁 HAINING
哈密 HAMI
邯郸 HANDAN
杭州 HANGZHOU
汉中 HANZHONG
鹤壁 HEBI
合肥 HEFEI
衡水 HENGSHUI
衡阳 HENGYANG
和田 HETIAN
河源 HEYUAN
菏泽 HEZE
花都 HUADOU
淮安 HUAIAN
淮北 HUAIBEI
怀化 HUAIHUA
淮南 HUAINAN
黄冈 HUANGGANG
黄山 HUANGSHAN
黄石 HUANGSHI
呼和浩特 HUHEHAOTE
惠州 HUIZHOU
葫芦岛 HULUDAO
湖州 HUZHOU
佳木斯 JIAMUSI
吉安 JIAN
江都 JIANGDOU
江门 JIANGMEN
江阴 JIANGYIN
胶南 JIAONAN
胶州 JIAOZHOU
焦作 JIAOZUO
嘉善 JIASHAN
嘉兴 JIAXING
介休 JIEXIU
吉林 JILIN
即墨 JIMO
济南 JINAN
晋城 JINCHENG
景德镇 JINGDEZHEN
景洪 JINGHONG
靖江 JINGJIANG
荆门 JINGMEN
荆州 JINGZHOU
金华 JINHUA
集宁 JINING1
济宁 JINING
晋江 JINJIANG
金坛 JINTAN
晋中 JINZHONG
锦州 JINZHOU
吉首 JISHOU
九江 JIUJIANG
酒泉 JIUQUAN
鸡西 JIXI
济源 JIYUAN
句容 JURONG
开封 KAIFENG
凯里 KAILI
开平 KAIPING
开远 KAIYUAN
喀什 KASHEN
克拉玛依 KELAMAYI
库尔勒 KUERLE
奎屯 KUITUN
昆明 KUNMING
昆山 KUNSHAN
来宾 LAIBIN
莱芜 LAIWU
莱西 LAIXI
莱州 LAIZHOU
廊坊 LANGFANG
兰州 LANZHOU
拉萨 LASA
乐山 LESHAN
连云港 LIANYUNGANG
聊城 LIAOCHENG
辽阳 LIAOYANG
辽源 LIAOYUAN
丽江 LIJIANG
临安 LINAN
临沧 LINCANG
临汾 LINFEN
灵宝 LINGBAO
临河 LINHE
临夏 LINXIA
临沂 LINYI
丽水 LISHUI
六安 LIUAN
六盘水 LIUPANSHUI
柳州 LIUZHOU
溧阳 LIYANG
龙海 LONGHAI
龙岩 LONGYAN
娄底 LOUDI
漯河 LUOHE
洛阳 LUOYANG
潞西 LUXI
泸州 LUZHOU
吕梁 LVLIANG
旅顺 LVSHUN
马鞍山 MAANSHAN
茂名 MAOMING
梅河口 MEIHEKOU
眉山 MEISHAN
梅州 MEIZHOU
勉县 MIANXIAN
绵阳 MIANYANG
牡丹江 MUDANJIANG
南安 NANAN
南昌 NANCHANG
南充 NANCHONG
南京 NANJING
南宁 NANNING
南平 NANPING
南通 NANTONG
南阳 NANYANG
内江 NEIJIANG
宁波 NINGBO
宁德 NINGDE
盘锦 PANJIN
攀枝花 PANZHIHUA
蓬莱 PENGLAI
平顶山 PINGDINGSHAN
平度 PINGDU
平湖 PINGHU
平凉 PINGLIANG
萍乡 PINGXIANG
普兰店 PULANDIAN
普宁 PUNING
莆田 PUTIAN
濮阳 PUYANG
黔南 QIANNAN
启东 QIDONG
青岛 QINGDAO
庆阳 QINGYANG
清远 QINGYUAN
青州 QINGZHOU
秦皇岛 QINHUANGDAO
钦州 QINZHOU
琼海 QIONGHAI
齐齐哈尔 QIQIHAER
泉州 QUANZHOU
曲靖 QUJING
衢州 QUZHOU
日喀则 RIKAZE
日照 RIZHAO
荣成 RONGCHENG
如皋 RUGAO
瑞安 RUIAN
乳山 RUSHAN
三门峡 SANMENXIA
三明 SANMING
三亚 SANYA
厦门 XIAMEN
佛山 SHAN
商洛 SHANGLUO
商丘 SHANGQIU
上饶 SHANGRAO
上虞 SHANGYU
汕头 SHANTOU
安康 ANKANG
韶关 SHAOGUAN
绍兴 SHAOXING
邵阳 SHAOYANG
沈阳 SHENYANG
深圳 SHENZHEN
石河子 SHIHEZI
石家庄 SHIJIAZHUANG
石林 SHILIN
石狮 SHISHI
十堰 SHIYAN
寿光 SHOUGUANG
双鸭山 SHUANGYASHAN
朔州 SHUOZHOU
沭阳 SHUYANG
思茅 SIMAO
四平 SIPING
松原 SONGYUAN
遂宁 SUINING
随州 SUIZHOU
苏州 SUZHOU
塔城 TACHENG
泰安 TAIAN
太仓 TAICANG
泰兴 TAIXING
太原 TAIYUAN
泰州 TAIZHOU
台州 TAIZHOU1
唐山 TANGSHAN
腾冲 TENGCHONG
滕州 TENGZHOU
天门 TIANMEN
天水 TIANSHUI
铁岭 TIELING
铜川 TONGCHUAN
通辽 TONGLIAO
铜陵 TONGLING
桐庐 TONGLU
铜仁 TONGREN
桐乡 TONGXIANG
通州 TONGZHOU
通化 TONGHUA
吐鲁番 TULUFAN
瓦房店 WAFANGDIAN
潍坊 WEIFANG
威海 WEIHAI
渭南 WEINAN
文登 WENDENG
温岭 WENLING
温州 WENZHOU
乌海 WUHAI
武汉 WUHAN
芜湖 WUHU
吴江 WUJIANG
乌兰浩特 WULANHAOTE
武威 WUWEI
无锡 WUXI
梧州 WUZHOU
西安 XIAN
项城 XIANGCHENG
襄樊 XIANGFAN
香格里拉 XIANGGELILA
象山 XIANGSHAN
湘潭 XIANGTAN
湘乡 XIANGXIANG
咸宁 XIANNING
仙桃 XIANTAO
咸阳 XIANYANG
西藏 XICANG
西昌 XICHANG
邢台 XINGTAI
兴义 XINGYI
西宁 XINING
新乡 XINXIANG
信阳 XINYANG
新余 XINYU
忻州 XINZHOU
宿迁 SUQIAN
宿豫 SUYU
宿州 SUZHOU1
宣城 XUANCHENG
许昌 XUCHANG
徐州 XUZHOU
雅安 YAAN
牙克石 YAKESHI
延安 YANAN
延边 YANBIAN
盐城 YANCHENG
阳江 YANGJIANG
阳泉 YANGQUAN
扬州 YANGZHOU
延吉 YANJI
烟台 YANTAI
兖州 YANZHOU
宜宾 YIBIN
宜昌 YICHANG
宜春 YICHUN
伊春 YICHUN1
伊犁 YILI
银川 YINCHUAN
营口 YINGKOU
鹰潭 YINGTAN
伊宁 YINING
义乌 YIWU
宜兴 YIXING
益阳 YIYANG
永康 YONGKANG
永州 YONGZHOU
岳阳 YUEYANG
玉环 YUHUAN
榆林 YULIN1
玉林 YULIN
运城 YUNCHENG
玉溪 YUXI
余姚 YUYAO
枣庄 ZAOZHUANG
增城 ZENGCHENG
长春 CHANGCHUN
长海 CHANGHAI
张家港 ZHANGJIAGANG
张家界 ZHANGJIAJIE
张家口 ZHANGJIAKOU
长乐 CHANGLE
章丘 ZHANGQIU
长沙 CHANGSHA
张掖 ZHANGYE
长治 CHANGZHI
漳州 ZHANGZHOU
湛江 ZHANJIANG
肇东 ZHAODONG
肇庆 ZHAOQING
昭通 ZHAOTONG
郑州 ZHENGZHOU
镇江 ZHENJIANG
中山 ZHONGSHAN
周口 ZHOUKOU
舟山 ZHOUSHAN
诸城 ZHUCHENG
珠海 ZHUHAI
诸暨 ZHUJI
驻马店 ZHUMADIAN
株洲 ZHUZHOU
淄博 ZIBO
自贡 ZIGONG
遵义 ZUNYI
乌鲁木齐 WULUMUQI
福清 FUQING
鄂州 EZHOU
包头 BAOTOU
萧山 XIAOSHAN
宣化 XUANHUA
江油 JIANGYOU
资阳 ZIYANG
辛集 XINJI
佛山 FOSHAN
万州 WANZHOU
邹城 ZOUCHENG
邵武 SHAOWU
姜堰 JIANGYAN
湘阴 XIANGYIN
松江 SONGJIANG
七台河 QITAIHE
醴陵 LILING
涪陵 FULING
公主岭 GONGZHULING
歙县 SHEXIAN
兴化 XINGHUA";
        $arrStr = explode("\n", $str);
        $data = [];
        foreach ($arrStr as $key => $item) {
            $arrItem = explode(" ", $item);
            $data[$arrItem[0]] = strtolower($arrItem[1]);
            echo "'" . strtolower($arrItem[1]) . "' => '" . $arrItem[0] . "',";
        }
//        echo json_encode($data);
    }

    private function testUpload()
    {
        $file = request()->file('file', null);
        if ($file == null) {
            return api_response_action(false, ErrorCode::$ENUM_PARAM_NULL_ERROR, '请上传文件');
        }
        $diyPath = request()->input('path', '');
        $diyPath = $diyPath ?? '';
        $disk = DiskFactory::build('aliyun-oss');
        $flag = $disk->upload($file, $diyPath);
        if ($flag == false) {
            return api_response_action(false, ErrorCode::$ENUM_ACTION_ERROR, $disk->getMessage());
        }
        return api_response_action(true, ErrorCode::$ENUM_SUCCESS, $disk->getMessage(), [
            'url' => $disk->getPath()
        ]);
    }

    public function addUser()
    {
        return resourceConstructor(new User())->store(request());
    }

}
