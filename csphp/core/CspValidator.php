<?php
namespace Csp\core;
use \Csphp;
use \Csp\core\CspException;


class CspValidator{
    //自定义的正则规则列表 name=>regexpStr
    public static $regexpRuleList = array(

    );
    //自定义的 规则逻辑定义如下 func($argValue, $ruleArg){}; 返回 true 或者 array('code'=>errorno,'msg'=>tips)
    public static $funcRuleList = array(

    );
    //自定义的枚举字典 name=>enumStr,Or enumArray
    public static $enumMapList = array(

    );


    //最后的验证信息
    public static $voidInfo = null;


    public function __construct(){

    }

    /**
     * 注册一个正则表达式规则
     * @param $regexpName   string 正则表达式名称，可用于 regexp 的参数如 “require,regexp:regexpName”
     * @param $regexp
     */
    public static function registerRegexpRule($regexpName, $regexp){
        self::$regexpRuleList[$regexpName] = $regexp;
    }

    /**
     * 注册一个自定义的 验证方法
     * @param $name string 验证器名称
     * @param $callableExp callable 自定义的验证方法 ruleFunc($argValue, $ruleArg){};
     * @param $regexp
     */
    public static function registerValidator($name, $callableExp){
        self::$funcRuleList[$name] = $callableExp;
    }

    /**
     * 注册 枚举数据字典，存储的值为数组
     * @param $name         string name 枚举名称
     * @param $dataArrOrStr array|string 枚举数组 或者是以 | 号分隔的字符串
     */
    public static function registerEnumMap($emumName, $dataArrOrStr){
        self::$enumMapList[$emumName] = is_array($dataArrOrStr) ? $dataArrOrStr : explode('|', $dataArrOrStr);
    }


    /**
     * 重置验证器状态
     * @param string $rule
     */
    public static function reset($argValue, $rule){
        self::$voidInfo = array(
            'value' =>$argValue,
            'rule'  =>$rule,
            'msg'   =>'OK',
            'code'  =>0
        );
    }

    /**
     * 获取验证信息，通常在 发现验证失败后提取
     * @return null
     */
    public static function getVoidInfo($vName='', $tips=''){
        self::$voidInfo['name'] = $vName ? $vName : "";
        self::$voidInfo['tips'] = $vName ? $vName.' '.self::$voidInfo['msg'] : self::$voidInfo['msg'];

        if($tips){
            self::$voidInfo['tips'] = $tips.'; '.self::$voidInfo['tips'];
        }
        return self::$voidInfo;
    }

    /**
     *
     * @param $code
     * @param $msg
     */
    public static function voidInfo($code, $msg){
        return array('code'=>$code, 'msg'=>$msg);
    }

    /**
     * 通用的输入获取器
     * @param $argValue         等检查的值，NULL为未提供
     *                          如 g:name = $_GET[name] c:ck=$_COOKIE['ck'] p:text=$_POST[text]
     * @param $ruleList         规则列表,规则说明如下：
     *    1. 单个规则的定义语法为 <rule_name>[:rule_arg]
     *    2. 多个规则可用 , 隔开
     *    3. 检查时从前面开始检查，只要有一个不符合则返回
     *    4. 当前控制器必须存在 Control::__chk_<rule_name>($argValue, $ruleArg) 成员方法
     *       或者 当前运行时环境，存储在函数 <rule_name>($argValue, $ruleArg)
     *       且其返回值规则为 通过验证返回 true ，不通过返回 array(code=>xxx,msg=>xxx)
     *    5. 内置的检查规则有
     *          norequire       表示参数不是必须的，但如果传递了参数，就需要符合其余的规则，必须放在第一个
     *          require         表示参数必须提供，但可能是空的
     *          noempty         表示参数不能为空，这个空与php的empty不同，它其它是 slen:0-0 的别名
     *          enum:a|b|c      表示参数是一个 enum 值 它的可选值为 a|b|c
     *          url             表示参数必须是一个url
     *          email           表示参数是一个email
     *          ip              表示参数是一个IP地址
     *          pcard           表示参数是一个身分证号
     *          pcode           表示参数是一个邮编
     *          num             表示参数必须是一个数字
     *          num:<i|N>-<i|N> 表示参数是一个某个范围的数字，num:i-10 小于10的值，num:3-i 大于3 ，num:3-5 大于
     *          slen:N-N        表示参数的字节长度必须在某个范围，
     *          wblen:0-14      表示参数微博长度必须在某个范围，微博长度的计算方法为: 一个汉字计2 一个单字节字符计 1
     *          regexp:reg_str" 表示参数必须符合 reg_str 正则表示规则（高级应用）
     *
     * @param $argDesc          自定义的参数错误提示信息
     * @param $voidCallBack     是否在参数错误时自动处理，在API JSAPI CLI 模式时 建议使用默认值 true
     *                          如果设置为 false , 用户可通过返回值是否 ===false 判断参数是否合法
     *                          且可以通过 control::lastParamError() 获取参数错误的描述与错误码
     *                          $callBack 参数除了 布尔值外，也可以是一个 callbackAble 值
     *
     *
     * 代码示例:
     *      CspValidator::validate($value, "num,enum:1|2|3");
     *      CspValidator::validate($value, "ip");
     *      CspValidator::validate($value, "norequire,email");
     *      CspValidator::validate($value, "require,phone");
     *      CspValidator::validate($value, "require,slen:3-5");
     *      CspValidator::validate($value, "require,regexp:#\d{4}#");
     *      CspValidator::validate($value, "require,regexp:regexpName");
     *
     * @return mixed            true or false 表示参数不合法
     */
    final public static function validate($argValue, $ruleList=''){
        //重置验证信息
        self::reset($argValue, $ruleList);

        if(empty($ruleList)) return true;
        $rs = explode(",", $ruleList);
        foreach($rs as $r){
            $r = trim($r);
            if(empty($r)) continue;
            //非必需参数规则，特殊处理，只要它未传递，就返回
            if($r==='norequire'){
                if($argValue===null) {return true;}
                continue;
            }

            //规则表达式为 用 : 隔开 前面为规则名，后面为规则参数
            $rData     = explode(":",$r,2);
            $ruleName  = $rData[0];
            $ruleArg   = isset($rData[1]) ? $rData[1] : '';

            $ruleFunction = $ruleName;          //规则可能是一个全局可访问的函数
            $ruleMethod   = "__chk_".$ruleName; //规则可能是一个当前ctrl可访问的成员方法

            $chkRst = true;
            if(method_exists(__CLASS__,$ruleMethod)){
                $chkRst = self::$ruleMethod($argValue, $ruleArg);
            }elseif( isset( self::$funcRuleList[$ruleName] ) ){
                //用户自定义的 注册在 $funcRuleList 中验证方法
                $chkRst = call_user_func_array(self::$funcRuleList[$ruleName], array($argValue, $ruleArg));
            }elseif(function_exists($ruleFunction)){
                $chkRst = $ruleFunction($argValue, $ruleArg);
            }else{
                throw new CspException("Can not find input validator [$ruleFunction] of $ruleList ");
                exit;
            }
            //验证器 返回 错误信息数组 或者 true
            if(is_array($chkRst)){
                self::$voidInfo['msg']  = $chkRst['msg'];
                self::$voidInfo['code'] = $chkRst['code'];
                return false;
            }
            if($chkRst!==true){
                return false;
            }
        }
        return true;//通过所有数据验证规则，返回 true
    }


    //参数检查规则
    private static function __chk_require($argValue, $ruleArg=''){
        if ($argValue===null){
            return self::voidInfo(40001, "参数是一个必选参数，当前值为null");
        }
        return true;
    }

    //检查输入是否是一个数字,并且在某个范围
    private static function __chk_num($argValue, $ruleArg=''){

        if(!is_numeric($argValue)){
            $argValue = is_scalar($argValue) ? $argValue : json_encode($argValue);
            return self::voidInfo(40002,"参数必须是一个数字，当前值 $argValue 不是一个数字");
        }

        return self::__chk_limit($argValue, $ruleArg, '数值大小');
    }
    //检查值是否在某个范围
    private final static function __chk_limit($argValue, $ruleArg='', $typeStr='数值大小'){
        if(empty($ruleArg)) return true;
        $ruleArg = trim($ruleArg);
        $ls = explode("-",$ruleArg);

        //var_dump($ruleArg);
        $ruleTips = array();
        $chk = true;
        if(is_numeric($ls[0])){
            $ruleTips[] = " >=".$ls[0]." ";
            $chk = $argValue >= $ls[0];

        }
        //APP::trace();
        if(is_numeric($ls[1])){
            $ruleTips[] = " <=".$ls[1]." ";
            $chk = $chk && ($argValue <= $ls[1]);

        }

        if(!$chk){
            $argValue = is_scalar($argValue) ? $argValue : json_encode($argValue);
            return self::voidInfo(40003, "参数的{$typeStr}范围为: ".implode(" AND ", $ruleTips)." , 目前为值为 $argValue ");
        }
        return true;
    }

    //检查输入是否为空 与 内置的 empty 不同，这个验证只是验证字符串是否长度为0，与 slen:0-0 相同
    private static function __chk_noempty($argValue, $ruleArg=''){
        if(strlen($argValue)===0){
            return self::voidInfo(40004, "参数不能为空,当前值长度为0");
        }
        return true;
    }
    //检查参数的字节长度
    private static function __chk_slen($argValue, $ruleArg=''){
        $slen = strlen($argValue);
        return self::__chk_limit($slen, $ruleArg, '字节长度');
    }

    //检查参数的类微博长度，一个汉字计 2 一个单字节字符计 1
    private static function __chk_wblen($argValue, $ruleArg=''){
        $wblen = (strlen($argValue) + mb_strlen($argValue, 'UTF-8')) / 4;
        return self::__chk_limit($wblen, $ruleArg, '长度');
    }

    //检查参数是否是一个EMAIL
    private static function __chk_email($argValue, $ruleArg=''){
        return self::__chk_by_regexp($argValue, $ruleArg, 'email');
    }
    //检查参数是否是一个URL
    private static function __chk_url($argValue, $ruleArg=''){
        return self::__chk_by_regexp($argValue, $ruleArg, 'url');
    }
    //检查参数是否是一个 pcode 邮政编码
    private static function __chk_pcode($argValue, $ruleArg=''){
        return self::__chk_by_regexp($argValue, $ruleArg, 'pcode');
    }
    //检查参数是否是一个 pcard 身分证号码
    private static function __chk_pcard($argValue, $ruleArg=''){
        return self::__chk_by_regexp($argValue, $ruleArg, 'pcard');
    }
    //检查参数是否是一个 ip地址
    private static function __chk_ip($argValue, $ruleArg=''){
        return self::__chk_by_regexp($argValue, $ruleArg, 'ip');
    }
    //检查参数是否是一个 isbn 号
    private static function __chk_isbn($argValue, $ruleArg=''){
        return self::__chk_by_regexp($argValue, $ruleArg, 'isbn');
    }
    //检查参数是否是一个 phone 电话号码
    private static function __chk_phone($argValue, $ruleArg=''){
        return self::__chk_by_regexp($argValue, $ruleArg, 'phone');
    }

    private final static function __chk_by_regexp($argValue, $ruleArg='', $type){
        $types = array(
            'email' => array('#[a-z0-9&\-_.]+@[\w\-_]+([\w\-.]+)?\.[\w\-]+#is', "Email地址", 40005),
            'url'   => array('#(http|https|ftp|ftps)://([\w-]+\.)+[\w-]+(/[\w-./?%&=]*)?#i', "Email地址", 40006),
            'pcode' => array('#^[1-9][0-9]{5}$#', "邮政编码", 40007),
            'pcard' => array('#^[\d]{15}$|^[\d]{18}|^[\d]{17}X$#i', "身份证号码", 40008),
            'ip'    => array('#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', "IP地址", 40009),
            'isbn'  => array('#^978[\d]{10}$|^978-[\d]{10}$#', "ISBN号码", 40010),
            'phone' => array('#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$|^17[0-9]\d{8}$#', "手机号码", 40011)
        );

        $p = $types[$type][0];
        if(!preg_match($p, $argValue)){
            return self::voidInfo($types[$type][2], "参数必须是一个".$types[$type][1]);
        }
        return true;
    }
    //参数必须符合某个正则 不包含半角逗号
    private final static function __chk_regexp($argValue, $ruleArg=''){
        if(isset(self::$regexpRuleList[$ruleArg])){
            $ruleArg = self::$regexpRuleList[$ruleArg];
        }
        if(!preg_match($ruleArg, $argValue)){
            return self::voidInfo(40012, "参数不符合正则表达式规则 $ruleArg ");
        }
        return true;
    }
    //参数必须是枚举类型 如 "enum:aaaa|bbb|ccc" 或者 自定义的 “enum:enumName”
    private final static function __chk_enum($argValue, $ruleArg=''){
        if(isset(self::$enumMapList[$ruleArg])){
            $enums = self::$enumMapList[$ruleArg];
        }else{
            $enums = explode("|",trim($ruleArg));
        }

        if(!in_array($argValue, $enums)){
            $argValue = is_scalar($argValue) ? $argValue : json_encode($argValue);
            return self::voidInfo(40013, "参数必须是以下枚举值之一 $ruleArg 当前值为 $argValue ！");
        }
        return true;
    }
    //enum 别名
    private final static function __chk_select($argValue, $ruleArg='') {
        return self::__chk_enum($argValue, $ruleArg);
    }
    //-----------------------------------------------------------------------------------
    /*
     * 专用于上传文件的检查的参数检查方法
     * $ruleArg 配置为 一个query格式的字符串 如 chkupload:types=_doc_&max_size=2&max_h=300
     * $ruleArg = http_build_query(array(
     *      'types'=>'jpg|gif', //文件类型限制，扩展名 用 | 隔开，可使用别名 _doc_  _pic_
     *      'max_size'=>0.1,    //大小限制 最大 单位M      默认的 4 即最大上传4M
     *      'min_size'=>0,      //大小限制 最小 单位M      默认为0，
     *      'min_h'=>300,       //像素限制 最小高度 单位 px 默认为不限
     *      'max_h'=>300,       //像素限制 最大高度 单位 px 默认为不限
     *      'min_w'=>300,       //像素限制 最小宽度 单位 px 默认为不限
     *      'max_w'=>300,       //像素限制 最大宽度 单位 px 默认为不限
     *      'scale'=>110*110    //宽*高的固定大小
     *      'wfile'=>''         //上传文件的转移目录，绝对路径，
     *                          //可使用 __auto__ 别名，自动生成，将在 var/upload/下创建文件
     * ));
     */
    private final static function __chk_chkupload($argValue, $ruleArg=''){
        $argArr = array();
        $ruleArg && parse_str($ruleArg,$argArr);
        $typeArr = explode('/', $argValue['type']);
        if(isset($argArr['types']) && $argArr['types']){//判断文件类型
            if(!in_array($typeArr[1], explode('|', $argArr['types']))){
                return self::voidInfo(40014, "只允计上传文件类型为[".$argArr['types']."]的文件 当前类型: ".$typeArr[1]);
            }
        }

        if(isset($argArr['max_size']) && $argArr['max_size']){
            if($argValue['size'] > ($argArr['max_size']*1024*1024)){
                return self::voidInfo(40014, "文件大小大于".$argArr['max_size']."M");
            }
        }

        if(isset($argArr['min_size']) && $argArr['min_size']){
            if($argValue['size'] < ($argArr['min_size']*1024*1024)){
                return self::voidInfo(40014, "文件大小小于".$argArr['max_size']."M");
            }
        }

        if($typeArr[0]=='image'){
            $imageArr = getimagesize($argValue['tmp_name']);

            if(isset($argArr['max_w']) && isset($imageArr['0']) && $imageArr['0']){
                if($imageArr['0'] > $argArr['max_w']){
                    return self::voidInfo(40014, "宽度大于".$argArr['max_w']."px");
                }
            }

            if(isset($argArr['min_w']) && isset($imageArr['0']) && $imageArr['0']){
                if($imageArr['0'] < $argArr['min_w']){
                    return self::voidInfo(40014, "宽度小于".$argArr['min_w']."px");
                }
            }

            if(isset($imageArr['1']) && $imageArr['1']){
                if(isset($argArr['max_h']) && $imageArr['1'] > $argArr['max_h']){
                    return self::voidInfo(40014, "高度大于".$argArr['max_h']."px");
                }
            }

            if(isset($argArr['min_h']) && isset($imageArr['1']) && $imageArr['1']){
                if($imageArr['1'] < $argArr['min_h']){
                    return self::voidInfo(40014, "高度小于".$argArr['min_h']."px");
                }
            }

            if(isset($argArr['scale']) && $argArr['scale']){
                $tArr = explode('*', $argArr['scale']);
                if($tArr[0]!=$imageArr[0] || $tArr[1] !=$imageArr[1]){
                    return self::voidInfo(40014, "文件尺寸必须为".$argArr['scale']);
                }
            }

        }
        return true;
    }
}
