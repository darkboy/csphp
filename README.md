Csphp Framework
=====

A Concise but not Simple PHP framework 

------------------------------------------------------------------

Framework feature Summary
=====


Startup
=====

<pre><code>

//启动应用程序 程序只有一个入口配置
Csp::createApp($cfg)->run();

</code></pre>

------------------------------------------------------------------


exception handle for param 404 401 err
=====
<pre><code>

//err 不同的参数错误使用统一的处理方法 并且可以由用户配置
CspErrorHandle::cliParamError();
CspErrorHandle::ajaxParamError();
CspErrorHandle::apiParamError();
CspErrorHandle::webParamError();
CspErrorHandle::jsonpParamError();
CspErrorHandle::notFound404();
CspErrorHandle::deny401();
CspErrorHandle::error();
//用户可定制错误处理方法
$cfg['error_handle']=array(
    'web_param_error'   =>'CspErrorHandle::webParamError',
    'api_param_error'   =>'CspErrorHandle::apiParamError',
    'cli_param_error'   =>'CspErrorHandle::cliParamError',
    'ajax_param_error'  =>'CspErrorHandle::ajaxParamError',
    'jsonp_param_error' =>'CspErrorHandle::jsonpParamError',
    'not_found_404'     =>'CspErrorHandle::notFound404',
    'deny_401'          =>'CspErrorHandle::deny401',
    'error'             =>'CspErrorHandle::error',
);

</code></pre>

------------------------------------------------------------------

get input from request 
=====
<pre><code>

//request input
Csp::request()->param($kr,$def,$rule,$tips='',$errHandle);
Csp::request()->apiParam($kr,$def,$rule,$tips='',$errHandle);
Csp::request()->cliParam($kr,$def,$rule,$tips='',$errHandle);
Csp::request()->webParam($kr,$def,$rule,$tips='',$errHandle);
Csp::request()->ajaxParam($kr,$def,$rule,$tips='',$errHandle);
Csp::request()->jsonpParam($kr,$def,$rule,$tips='',$errHandle);
//获取请求类型
Csp::request()->getRequestType();//return ajax jsonp api web cli

Csp::request()->header($k);
Csp::request()->post();
Csp::request()->get();
Csp::request()->cookie();
Csp::request()->file();

//与URL相关的信息
Csp::request()->getHost();
Csp::request()->uri();
Csp::request()->lastViewUrl();//用户最后一次浏览的 url

</code></pre>

------------------------------------------------------------------

chk request type
=====
<pre><code>

//请求性质判断
Csp::request()->isApi();
Csp::request()->isAjax();
Csp::request()->isJsonp();
Csp::request()->isPost();
Csp::request()->isGet();
Csp::request()->isPut();
Csp::request()->isRobot();
Csp::request()->isPhone();

//用于判断 请求类型的相当配置
$cfg['jsonp_flag_vr']=array('g:callback', 'p:callback');
$cfg['ajax_flag_vr'] =array('g:_', 'p:_' );
$cfg['api_flag_vr']  =array('h:csp-api');

//检查当前请求是否符合条件
Csp::isMatch($reqCond);
Csp::request()->isMatch($reqCond);
$reqCond=array(
			'domain'=>'*',          //当前域名
            'router_prefix'=>'*',   //路由前缀
            'request_method'=>'*',  //HTTP 请求方法 GET POST PUT
            'router_suffix'=>'*',   //路由后缀
            'entry_name'=>'*',      //入口名称
            'header_send'=>'headerkey,value',     //发送了某个头信息 value 可选
            'user_cond'=>'abc::abc',//用户自定义规则,是一个可调用的 回调或者服务定义器
);

</code></pre>

------------------------------------------------------------------

get var by vroute
=====
<pre><code>

//use input 获取 用户输入
Csp::v($vr,$def,$rule,$errHandle);
//vr 规则，变量路由
$vr='-:a/b/c';//$sysCfg
$vr='s:a/b/c';//$_SERVER
$vr='g:a/b/c';//$_GET
$vr='p:a/b/c';//$_POST
$vr='c:a/b/c';//$_COOKIE
$vr='r:a/b/c';//$_REQUEST;
$vr='f:a/b/c';//$_FILES;
$vr='v:a/b/c';//var from route parse
$vr='h:a/b/c';//header
$vr='a:a/b/c';// value in v or r

</code></pre>

------------------------------------------------------------------

set or get data in page live time 
=====
<pre><code>

Csp::data($k,$v);//for set
Csp::data($k);   //for get

</code></pre>

------------------------------------------------------------------

url constructor 
=====
<pre><code>

//url构造器
Csp::url($r, $paramArrOrStr, $anchor, $hostKey='_default');
$cfg['host_keys'] = array(
	'_default'	=>'http://www.domain.com/',
	'home'		=>'http://www.domain.com/',
	'admin'		=>'http://admin.domain.com/',
	'statics'	=>'http://admin.domain.com/',
	'api'		=>'http://api.domain.com/',
	
);

</code></pre>

------------------------------------------------------------------

response feature 
=====
<pre><code>

Csp::response()->httpCode=200;
Csp::response()->bodyData=null;//array or str
Csp::response()->headerData=null;//array
Csp::response()->setHeader($k,$v);
Csp::response()->setCookie($k,$v, $ttl, $path, $domain);

Csp::response()->redirect($url,$type);

Csp::response()->setBody($strOrArray);
Csp::response()->send();

</code></pre>

------------------------------------------------------------------

log and debug func 
=====
<pre><code>

//调试与日志
Csp::logInfo($cateGory='sys',$msg);
Csp::logError($cateGory='sys',$msg);
Csp::logWarning($cateGory='sys',$msg);
$cfg['log_fromat']= "{time}\t{uri}:{route}\t{msg}";

Csp::dump($msg);
Csp::trace($msg, $cateGory);
Csp::dump($msg);
Csp::bmkStart($labelKey);
Csp::bmkEnd($labelKey);
Csp::halt();//alias for exit

</code></pre>

------------------------------------------------------------------

load file or instantiate obj
=====
<pre><code>

//对象加载与实例化
Csp::getPathByRoute($fRoute);
Csp::getRealPathByRoute($fRoute);
Csp::loadFile($fRoute);

Csp::newClass($fRoute, $cfg=null);
Csp::ctrl();
Csp::mod();
Csp::cls();
Csp::ext();
Csp::comp();

</code></pre>

------------------------------------------------------------------

file route rule
=====
<pre><code>

//类文件 与 对象 别名定位规则，类文件路由
$fRoute='@com/a/b/c'; //优先从应用目录查找
$fRoute='@f-com/a/b/c';//框架系统组件
$fRoute='@a-com/a/b/c';//应用组件
$fRoute='@ctrl/a/b/c';//控制器
$fRoute='@mod/a/b/c'; //模型
$fRoute='@tpl/a/b/c'; //模板 示图
$fRoute='@ext/a/b/c'; //扩展类，或者第三方类库，优先从应用目录查找
$fRoute='@f-ext/a/b/c'; //框架扩展类
$fRoute='@a-ext/a/b/c'; //应用扩展类

$fRoute='@static/a/b/c.js';  //应用静态文件,应包含扩展名
$fRoute='@static/a/b/c.css'; //应用静态文件

</code></pre>

------------------------------------------------------------------

Components feature
=====
<pre><code>

//组件的使用
Csp::comp($comRoute, $cfg, $accessKey='')->anyMethod();
$cfg['compents']=array(
    //这个key是访问名称
    'access_key'=> array(
        'cond'  =>$reqCond,//什么条件下加载组件
        'cls'   =>'',//对象定位路由
        'args'  =>array(

        )
    )
);

Csp::comp($accessKey)->anyMethod();

</code></pre>

------------------------------------------------------------------

Filters  feature
=====
<pre><code>

Csp::runFilters($fRoute, $cfg=null);

</code></pre>

------------------------------------------------------------------

hooks  feature
=====
<pre><code>

Csp::doHooks();
Csp::hook($hookName, $eventCbFunc);

</code></pre>

------------------------------------------------------------------

event  feature
=====
<pre><code>

Csp::fireEvent($eventName, $senderObj, $data=null);
Csp::on($eventName, $eventCbFunc);

</code></pre>

------------------------------------------------------------------

Tpl and controler
=====
<pre><code>

Csp::$tplVars = array();
Csp::controler()->assign($k, $v);
Csp::controler()->assign($kvArr);
Csp::controler()->fetch($tplRoute, $data);
Csp::controler()->render($tplRoute, $data, $cacheOpt);

Csp::controler()->display($tplRoure, $data, $cacheOpt);
Csp::controler()->ajaxRst($rst, $code=0, $msg='OK', $tips=null);
Csp::controler()->jsonpRst($cbName, $rst, $code=0, $msg='OK', $tips=null);
Csp::controler()->isXXX();// isPost isAjax isApi isGet isPut isPhone isRobot

//模板
Csp::tpl()->assign();
Csp::tpl()->fetch();
Csp::tpl()->render();
Csp::tpl()->layout();
Csp::tpl()->widget();

</code></pre>

------------------------------------------------------------------

asset use
=====
<pre><code>

//前端资源,解释多机部署，域名分离，无逢上线问题
Csp::asset($fRroute, $hostKey);
$cfg['asset']=array(
    'base_path'=>'/',
    'publish_path'=>'',
);

</code></pre>

------------------------------------------------------------------

route feature
=====
<pre><code>

//初始化路由信息 解释 路由配置
Csp::router()->init();
//动态添加路由配置
Csp::router()->runTimeRouteRegister();
Csp::router()->parse($req);
$routeInfo = array(
    'req_route'=>'a/b/classname/actionMethod/vn1-v1/vn2-v2',
    'clean_route'=>'a/b/classname/actionMethod',//清除变量后的路由
    'hit_rule'  =>'',   //命中中的 路由规则，可能是系统或者用户定义的: sys::xxxx, user::
    'real_rule'  =>'',  //最终要执行的 路由
    'ctrl_obj'  =>$ctrlObj,     //ctrl obj
    'action'    =>$actionName,  //str
    'route_var' =>array(
        'vn1'=>'v1',
        'vn2'=>'v2',
    )
);
Csp::router()->findRoute();//从路由配置中查找符合条件的规则


Csp::$vr = array();
Csp::router()->parseVar();
Csp::router()->getRouterVar();

Csp::router()->getRouter();
Csp::router()->controler();//ctr obj
Csp::router()->getAction();//str action method name

Csp::doAction();
</code></pre>

------------------------------------------------------------------

common functions
=====
<pre><code>

//common functions
Csp::toolkit()->xxx();
//config
$cfg['domain']  ='';
//项目所使用的域名列表
$cfg['hosts']=array(
    'api'   =>'http://api.domain.com/',
    'static'=>'http://static.domain.com/',
    'home'  =>'http://www.domain.com/',
    'sub'   =>'http://sub.domain.com/',
    'sub2'  =>'http://sub2.domain.com/',
);

</code></pre>

------------------------------------------------------------------

autoload files
=====
<pre><code>

//在框架引导期间 会自动加载的目录或者文件，值为加载的目录深度,如果加载目录为文件则后面的值无效，始终为 0
$cfg['auto_include_path']=array(
  'file'    =>0,
  'path'    =>1,
  'path2'   =>2,
);

</code></pre>

------------------------------------------------------------------

modelbase feature
=====
<pre><code>

//配置主键与表名，在子类的 构造器中执行
ModelBase::$tableName   = 'table1';
ModelBase::$pkName      = 'id';
ModelBase::$dbObj       = null;
ModelBase::initModel($pkName, $tbName, $dbName, $useCache);
ModelBase::getModel($pkName, $tbName, $dbName, $useCache);
ModelBase::validate();

ModelBase::db();                      //获取DB数据库操作客户端实例
ModelBase::dbError();                 //获取错误信息

ModelBase::create($data);             //给定K V数组，生成一行记录
ModelBase::update($data,$pkOrCond);   //给定K V数组，条件，更新记录,慎用批量更新条件，
ModelBase::get($pkOrCond);            //给定一个主键值，或者 条件，获取一行记录
ModelBase::gets($pks);                //给定一个主键值数组，获取N行记录
ModelBase::delete($pkOrCond);         //给定一个主键值，或者 条件，删除记录，慎用批量删除条件
ModelBase::deletes($pks);             //给定一个主键值数组，删除N行记录
ModelBase::count($cond);              //根据条件，统计记录数量

//根据条件 原子性的自增或者自减操作 $field=$field + $num ,$num 可以是负数
ModelBase::increment($field, $num, $pkOrCond, $min=null, $max=null);

//根据条件查找记录
ModelBase::find($cond, $orderStr='', $page=1, $pagesize=200);
ModelBase::findPks($cond, $order='', $page=1, $pagesize=200);

ModelBase::formatFind($fk, $cond, $order='', $page=1, $pagesize=200);
ModelBase::formatGet($fk, $pkOrCond);
ModelBase::formatGets($fk, $pks);
ModelBase::formatData($fk, $data, $isMultiRow=false);

</code></pre>

------------------------------------------------------------------

db feature
=====
<pre><code>

$cfg['dbName']=array(
	'host'	 => DB_HOST_W,
	'port'	 => DB_PORT,
	'user'	 => DB_USER,
	'pwd'	 => DB_PASSWD,
	'charset'=> DB_CHARSET,
	'tbpre'	 => DB_PREFIX,
	'dbName' => DB_NAME,
	'slaves' => array(
		array(
			'host'	 => DB_HOST_R1
		),
		array(
			'host'	 => DB_HOST_R2
		),

	)

);

//自定义SQL拼接
ModelBase::db()->sql($sqlTpl, $dataMap)->fetchOne($sql=null);  //执行查询 返回一条数据 1维
ModelBase::db()->sql($sqlTpl, $dataMap)->fetchRows($sql=null); //执行查询 返回多行数据 2维
ModelBase::db()->sql($sqlTpl, $dataMap)->exec($sql=null);      //执行查询 执行一条SQL命令

//
ModelBase::db()->getLink();

//链式查询 强需求么？
ModelBase::db()->queryCmd();
ModelBase::db()->queryCmd()
    ->table($tbA)
    ->select($fields)
    ->select($tbA, $fields)
    ->leftjoin($tbB, $myField='mField', $tbAField='')
    ->xxxjoin($tbB, "")
    ->join($tbB, "")
    ->where($field,$op, $v, $v2=null)
    ->where($field,$v)
    ->where($sql)
    ->orderby($field,'desc')
    ->orderby($field,'asc')
    ->orderby('id desc,id2 asc')
    ->limit()
    ->exec();

</code></pre>

------------------------------------------------------------------

