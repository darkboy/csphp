<?php
namespace Csp\core;
use \Csphp;

/**
 * Class CspResponse
 *
 * Csphp::response()->httpCode=200;
 * Csphp::response()->bodyData=null;//array or str
 * Csphp::response()->headerData=null;//array
 * Csphp::response()->setHeader($k,$v);
 * Csphp::response()->setCookie($k,$v, $ttl, $path, $domain);
 *
 * Csphp::response()->redirect($url,$type);
 *
 * Csphp::response()->setBody($strOrArray);
 * Csphp::response()->send();
 *
 * @package Csp\core
 *
 */
class CspResponse{

    public $httpVer  = '1.1';

    public $httpCode = 200;
    public $bodyStr  = '';
    public $headers  = array();

    //是否已经发送送信息
    public $headersHadSend  = false;

    //重定向的类型
    const REDIRECT_TYPE_301         = 'REDIRECT_TYPE_301';
    const REDIRECT_TYPE_302         = 'REDIRECT_TYPE_302';
    const REDIRECT_TYPE_LOCATION    = 'REDIRECT_TYPE_LOCATION';
    const REDIRECT_TYPE_JS_BY_TAG   = 'REDIRECT_TYPE_JS_BY_TAG';
    const REDIRECT_TYPE_JS_TOP_TAG  = 'REDIRECT_TYPE_JS_TOP_TAG';


    public $httpCodeMap = array (
        100 => "HTTP/1.1 100 Continue",
        101 => "HTTP/1.1 101 Switching Protocols",
        200 => "HTTP/1.1 200 OK",
        201 => "HTTP/1.1 201 Created",
        202 => "HTTP/1.1 202 Accepted",
        203 => "HTTP/1.1 203 Non-Authoritative Information",
        204 => "HTTP/1.1 204 No Content",
        205 => "HTTP/1.1 205 Reset Content",
        206 => "HTTP/1.1 206 Partial Content",
        300 => "HTTP/1.1 300 Multiple Choices",
        301 => "HTTP/1.1 301 Moved Permanently",
        302 => "HTTP/1.1 302 Found",
        303 => "HTTP/1.1 303 See Other",
        304 => "HTTP/1.1 304 Not Modified",
        305 => "HTTP/1.1 305 Use Proxy",
        307 => "HTTP/1.1 307 Temporary Redirect",
        400 => "HTTP/1.1 400 Bad Request",
        401 => "HTTP/1.1 401 Unauthorized",
        402 => "HTTP/1.1 402 Payment Required",
        403 => "HTTP/1.1 403 Forbidden",
        404 => "HTTP/1.1 404 Not Found",
        405 => "HTTP/1.1 405 Method Not Allowed",
        406 => "HTTP/1.1 406 Not Acceptable",
        407 => "HTTP/1.1 407 Proxy Authentication Required",
        408 => "HTTP/1.1 408 Request Time-out",
        409 => "HTTP/1.1 409 Conflict",
        410 => "HTTP/1.1 410 Gone",
        411 => "HTTP/1.1 411 Length Required",
        412 => "HTTP/1.1 412 Precondition Failed",
        413 => "HTTP/1.1 413 Request Entity Too Large",
        414 => "HTTP/1.1 414 Request-URI Too Large",
        415 => "HTTP/1.1 415 Unsupported Media Type",
        416 => "HTTP/1.1 416 Requested range not satisfiable",
        417 => "HTTP/1.1 417 Expectation Failed",
        500 => "HTTP/1.1 500 Internal Server Error",
        501 => "HTTP/1.1 501 Not Implemented",
        502 => "HTTP/1.1 502 Bad Gateway",
        503 => "HTTP/1.1 503 Service Unavailable",
        504 => "HTTP/1.1 504 Gateway Time-out"
    );

    public $mimetypes = array(
        'ez' => 'application/andrew-inset',
        'hqx' => 'application/mac-binhex40',
        'cpt' => 'application/mac-compactpro',
        'doc' => 'application/msword',
        'bin' => 'application/octet-stream',
        'dms' => 'application/octet-stream',
        'lha' => 'application/octet-stream',
        'lzh' => 'application/octet-stream',
        'exe' => 'application/octet-stream',
        'class' => 'application/octet-stream',
        'so' => 'application/octet-stream',
        'dll' => 'application/octet-stream',
        'oda' => 'application/oda',
        'pdf' => 'application/pdf',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        'smi' => 'application/smil',
        'smil' => 'application/smil',
        'mif' => 'application/vnd.mif',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'wbxml' => 'application/vnd.wap.wbxml',
        'wmlc' => 'application/vnd.wap.wmlc',
        'wmlsc' => 'application/vnd.wap.wmlscriptc',
        'bcpio' => 'application/x-bcpio',
        'vcd' => 'application/x-cdlink',
        'pgn' => 'application/x-chess-pgn',
        'cpio' => 'application/x-cpio',
        'csh' => 'application/x-csh',
        'dcr' => 'application/x-director',
        'dir' => 'application/x-director',
        'dxr' => 'application/x-director',
        'dvi' => 'application/x-dvi',
        'spl' => 'application/x-futuresplash',
        'gtar' => 'application/x-gtar',
        'hdf' => 'application/x-hdf',
        'js' => 'application/x-javascript',
        'json' => 'application/json',
        'skp' => 'application/x-koan',
        'skd' => 'application/x-koan',
        'skt' => 'application/x-koan',
        'skm' => 'application/x-koan',
        'latex' => 'application/x-latex',
        'nc' => 'application/x-netcdf',
        'cdf' => 'application/x-netcdf',
        'sh' => 'application/x-sh',
        'shar' => 'application/x-shar',
        'swf' => 'application/x-shockwave-flash',
        'sit' => 'application/x-stuffit',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc',
        'tar' => 'application/x-tar',
        'tcl' => 'application/x-tcl',
        'tex' => 'application/x-tex',
        'texinfo' => 'application/x-texinfo',
        'texi' => 'application/x-texinfo',
        't' => 'application/x-troff',
        'tr' => 'application/x-troff',
        'roff' => 'application/x-troff',
        'man' => 'application/x-troff-man',
        'me' => 'application/x-troff-me',
        'ms' => 'application/x-troff-ms',
        'ustar' => 'application/x-ustar',
        'src' => 'application/x-wais-source',
        'xhtml' => 'application/xhtml+xml',
        'xht' => 'application/xhtml+xml',
        'zip' => 'application/zip',
        'au' => 'audio/basic',
        'snd' => 'audio/basic',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'kar' => 'audio/midi',
        'mpga' => 'audio/mpeg',
        'mp2' => 'audio/mpeg',
        'mp3' => 'audio/mpeg',
        'aif' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff',
        'aifc' => 'audio/x-aiff',
        'm3u' => 'audio/x-mpegurl',
        'ram' => 'audio/x-pn-realaudio',
        'rm' => 'audio/x-pn-realaudio',
        'rpm' => 'audio/x-pn-realaudio-plugin',
        'ra' => 'audio/x-realaudio',
        'wav' => 'audio/x-wav',
        'pdb' => 'chemical/x-pdb',
        'xyz' => 'chemical/x-xyz',
        'bmp' => 'image/bmp',
        'gif' => 'image/gif',
        'ief' => 'image/ief',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jpe' => 'image/jpeg',
        'png' => 'image/png',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'djvu' => 'image/vnd.djvu',
        'djv' => 'image/vnd.djvu',
        'wbmp' => 'image/vnd.wap.wbmp',
        'ras' => 'image/x-cmu-raster',
        'pnm' => 'image/x-portable-anymap',
        'pbm' => 'image/x-portable-bitmap',
        'pgm' => 'image/x-portable-graymap',
        'ppm' => 'image/x-portable-pixmap',
        'rgb' => 'image/x-rgb',
        'xbm' => 'image/x-xbitmap',
        'xpm' => 'image/x-xpixmap',
        'xwd' => 'image/x-xwindowdump',
        'igs' => 'model/iges',
        'iges' => 'model/iges',
        'msh' => 'model/mesh',
        'mesh' => 'model/mesh',
        'silo' => 'model/mesh',
        'wrl' => 'model/vrml',
        'vrml' => 'model/vrml',
        'css' => 'text/css',
        'html' => 'text/html',
        'htm' => 'text/html',
        'asc' => 'text/plain',
        'txt' => 'text/plain',
        'rtx' => 'text/richtext',
        'rtf' => 'text/rtf',
        'sgml' => 'text/sgml',
        'sgm' => 'text/sgml',
        'tsv' => 'text/tab-separated-values',
        'wml' => 'text/vnd.wap.wml',
        'wmls' => 'text/vnd.wap.wmlscript',
        'etx' => 'text/x-setext',
        'xsl' => 'text/xml',
        'xml' => 'text/xml',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mpe' => 'video/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        'mxu' => 'video/vnd.mpegurl',
        'avi' => 'video/x-msvideo',
        'movie' => 'video/x-sgi-movie',
        'ice' => 'x-conference/x-cooltalk',
    );
    public function __construct(){
    }

    /**
     * 更改响应数据
     * @param $str 响应字符串
     * @param bool $isAppend    是否追加到末尾
     */
    public function setBodyStr($str, $isAppend=false){
        if($isAppend){
            $this->bodyStr.= $str;
        }else{
            $this->bodyStr = $str;
        }
    }

    /**
     * 设置一条header信息，
     *      如果是传递一个参数，则整个字符串作为一个header信息
     *      如果是传递二个参数，则第一个参数为 header name, 后面的为值
     *
     * @param $k        头信息串 或者头信息名
     * @param null $v   头信息值
     */
    public function setHeader($k,$v=null){
        if(func_num_args()==1){
            $this->headers[] = is_numeric($k) ? $this->httpCodeMap[$k] : $k;
        }else{
            $this->headers[] = $k.' '.$v;
        }
    }


    /**
     * 设置一个 cookie
     * @param $cookieName       cookie 名
     * @param $v                cookie 值
     * @param int $ttl          有效期，默认为 0 即浏览期间，可以是正负值，秒为单位
     * @param string $path      默认为 /
     * @param null $domain      默认为当前域名
     * @param bool $secure      如果设置为 true 则只能在https中使用
     * @param bool $httpOnly    如果设置为 true 则不能通过js编辑
     */
    public function setCookie($cookieName,$v, $ttl=0, $path='/',$domain=null, $secure=false, $httpOnly=false){
        if($domain===null){
            $domain = Csphp::request()->getHost();
        }
        $expire = $ttl===0 ? 0 : time()+$ttl;
        setcookie($cookieName, $v, $expire, $path, $domain,$secure, $httpOnly);
    }
    /*
     * 删除一个cookie
     */
    public function delCookie($ckNameOrNameArr){
        $ckArr = is_array($ckNameOrNameArr) ? : array($ckNameOrNameArr);
        foreach($ckArr as $ck){
            $this->setCookie($ck, -1000);
        }
    }


    /**
     * 程序重定向
     * @param $target   重定向目标
     * @param $type     类型      CspResponse::REDIRECT_TYPE_*
     */
    public function redirect($target, $type=self::REDIRECT_TYPE_LOCATION){

        $url = '';

        switch (strtoupper($type)){
            //慎用 301 浏览器会缓存，
            case self::REDIRECT_TYPE_301 :
                $this->setHeader("HTTP/1.1 301 Moved Permanently");
                $this->setHeader('Location', $url);
                break;

            case self::REDIRECT_TYPE_LOCATION :
            case self::REDIRECT_TYPE_302 :
                $this->setHeader('Location', $url);
                break;

            case self::REDIRECT_TYPE_JS_BY_TAG :
                $this->setBodyStr('<script>window.location.href="'.addslashes($url).'";</script>');
                break;

            //用于iframe等框架集中的页面
            case self::REDIRECT_TYPE_JS_TOP_TAG :
                $this->setBodyStr('<script>window.top.location.href="'.addslashes($url).'";</script>');
                break;

            default:
                throw new CspException("Error redirect type : : {$type} ");
                break;
		}
        $this->send();
    }

    /**
     * 发送401认证登录框，提交的用户名密码将存储在
     *      $_SERVER['PHP_AUTH_USER'] $_SERVER['PHP_AUTH_PW']
     *
     */
    public function sendAuth401($tips='请输入你的账号'){
        $this->setHeader('WWW-Authenticate: Basic realm="'.$tips.'"');
        $this->setHeader(401);
        //$_SERVER['PHP_AUTH_USER'] $_SERVER['PHP_AUTH_PW']
    }

    /**
     * 发送一个文件给客户端，浏览器端通常表现为 打开下载窗口
     * @param $file             实际要发送的文件路径
     * @param null $saveName    自动保存时的文件名
     * @param string $contentType   内容类型,可以是 $this->mimetypes 的Key
     */
    public function sendFile($file, $saveName=null, $contentType='application/octet-stream'){
        if(isset($this->mimetypes[$contentType])){
            $contentType = $this->mimetypes[$contentType];
        }
        $this->setHeader('Content-Type', $contentType);
        if($saveName===null){
            $saveName = basename($file);
        }
        $this->setHeader('Content-Disposition: attachment; filename="' . $saveName . '"');
        header('Content-Transfer-Encoding: binary');
        readfile($file);
    }

    /**
     * 设置一个 content-type
     * @param $typeOrName 可能是一个标准的 content-type 值或者 名称 如 json
     */
    public function setContentType($typeOrName){
        if(isset($this->mimetypes[strtolower($typeOrName)])){
            $typeOrName = $this->mimetypes[$typeOrName];
        }
        $this->setHeader('Content-Type', $typeOrName);
    }

    /**
     * 发送响应数据，先发送 header 再 发送 数据
     */
    public function send(){
        $this->sendHeader();
        $this->sendBody();
    }

    /**
     * 发送头信息
     */
    private function sendHeader(){
        foreach($this->headers as $hs){
            header($hs);
        }
        $this->headersHadSend = true;
    }

    /**
     * 发送响应数据
     */
    private function sendBody(){
        echo $this->bodyStr;
    }

}
/*

//常见的http头信息

//200 正常状态
header('HTTP/1.1 200 OK');

// 301 永久重定向，记得在后面要加重定向地址 Location:$url
header('HTTP/1.1 301 Moved Permanently');

// 重定向，其实就是302 暂时重定向
header('Location: http://www.maiyoule.com/');

// 设置页面304 没有修改
header('HTTP/1.1 304 Not Modified');

// 显示登录框，
header('HTTP/1.1 401 Unauthorized');
header('WWW-Authenticate: Basic realm="登录信息"');
echo '显示的信息！';

// 403 禁止访问
header('HTTP/1.1 403 Forbidden');

// 404 错误
header('HTTP/1.1 404 Not Found');

// 500 服务器错误
header('HTTP/1.1 500 Internal Server Error');

// 3秒后重定向指定地址(也就是刷新到新页面与 <meta http-equiv="refresh" content="10;http://www.maiyoule.com/ /> 相同)
header('Refresh: 3; url=http://www.maiyoule.com/');
echo '10后跳转到http://www.maiyoule.com';

// 重写 X-Powered-By 值
header('X-Powered-By: PHP/5.3.0');
header('X-Powered-By: Brain/0.6b');

//设置上下文语言
header('Content-language: en');

// 设置页面最后修改时间（多用于防缓存）
$time = time() - 60; //建议使用filetime函数来设置页面缓存时间
header('Last-Modified: '.gmdate('D, d M Y H:i:s', $time).' GMT');

// 设置内容长度
header('Content-Length: 39344');

// 设置头文件类型，可以用于流文件或者文件下载
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="example.zip"');
header('Content-Transfer-Encoding: binary');
readfile('example.zip');//读取文件到客户端

//禁用页面缓存
header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Pragma: no-cache');

//设置页面头信息
header('Content-Type: text/html; charset=iso-8859-1');
header('Content-Type: text/html; charset=utf-8');
header('Content-Type: text/plain');
header('Content-Type: image/jpeg');
header('Content-Type: application/zip');
header('Content-Type: application/pdf');
header('Content-Type: audio/mpeg');
header('Content-Type: application/x-shockwave-flash');
 */

