<?php
namespace Csp;
//框架启动 程序 主要注册 加载器
class CsphpAutoload{
    //已注册的命名空间
    public static $nsArr = array(
        //默认
        'Csp'=>array('basePath'=>__DIR__, 'fileExt'=>'.php')
    );

    public function __construct(){
    }
    /**
     * 注册一个命名空间，及其
     * @param $path      不以 / 结尾
     * @param $nsPrefix  命名空间前缀
     * @param $fileExt   默认的文件扩展名
     */
    public static function addNamespace($path, $nsPrefix, $fileExt='.php'){
        $nsPrefix = trim($nsPrefix, '\\').'\\';
        self::$nsArr[$nsPrefix] = array('basePath'=>realpath($path), 'fileExt'=>$fileExt);
        return true;
    }

    /**
     * 加载一个类，查找 并加载相关文件
     * @param $clsName
     */
    public static function load($clsName){
        $basePath = null;
        foreach (self::$nsArr as $nsPrefix=>$nsCfg){
            //echo '<pre>';print_r(array(strpos($clsName, $nsPrefix),$clsName,$nsPrefix));
            if(strpos($clsName, $nsPrefix)===0){
                $basePath      = rtrim($nsCfg['basePath'],'\\/').'/';
                $fileSuffix    = $nsCfg['fileExt'];
                $clsNameToPath = str_replace('\\','/',substr($clsName,strlen($nsPrefix)));
                $clsNameToPath = ltrim($clsNameToPath,'/');
                $filePath      = $basePath.$clsNameToPath.$fileSuffix;
                //print_r(['base'=>$basePath,'f'=>$filePath]);
                //echo "<pre>include class file:\n ".$filePath;//exit;
                if(file_exists($filePath)){
                    require $filePath;
                }else{
                    //echo "Not exists file ".$filePath;
                    return false;
                }
            }
        }
        return false;
    }
}
///注册 auto loader
spl_autoload_register(function ($className) { CsphpAutoload::load($className);});

