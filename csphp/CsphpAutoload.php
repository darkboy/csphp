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
        self::$nsArr[$nsPrefix] = array('basePath'=>realpath($path),'fileExt'=>$fileExt);
        return true;
    }

    /**
     * 加载一个类，查找 并加载相关文件
     * @param $clsName
     */
    public static function load($clsName){
        //var_dump($clsName);
        $basePath = null;
        foreach (self::$nsArr as $nsPrefix=>$nsCfg){
            //echo '<pre>';print_r(array(strpos($clsName, $nsPrefix),$clsName,$nsPrefix));
            if(strpos($clsName, $nsPrefix)===0){
                $basePath   = $nsCfg['basePath'];
                $fileSuffix = $nsCfg['fileExt'];
                $filePath = $basePath.str_replace('\\','/',substr($clsName,strlen($nsPrefix))).$fileSuffix;
                //echo $filePath;
                if(file_exists($filePath)){
                    //echo $filePath;exit;
                    require $filePath;

                }else{
                    return false;
                }
            }
        }
        return false;
    }
}
///注册 auto loader
spl_autoload_register(function ($className) { CsphpAutoload::load($className);});
//加载兼容性文件
require __DIR__.'/compat.php';

