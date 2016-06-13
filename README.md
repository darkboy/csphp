Csphp Framework
=====
A Concise but not Simple PHP framework v1.0.6

------------------------------------------------------------------

Plan.1.0
=====

    1. cli  route input and out support
    2. assets  publish statics resources
    3. implement 4  base:  obj ctrl  comp mod
    4. template  support，async (pipe ajax)  feature
    5. js,css manger in tpl
    6. url  creator considering for migration
    7. log review and debug
    8. event review and debug



Startup
=====

```php
	//启动应用程序 程序只有一个入口配置
	Csphp::createApp($cfg)->run();
```

------------------------------------------------------------------


exception handle for param 404 401 err
=====
```php

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

```

------------------------------------------------------------------

get input from request 
=====
```php

//获取请求类型                                                                                                                                    
Csphp::request()->getRequestType();//return ajax jsonp api web cli

//request input 通用的输入获取
Csphp::request()->param($kr,$def,$rule,$tips='',$errHandle);
//专用的获取方法
Csphp::request()->header($k);
Csphp::request()->post();
Csphp::request()->get();
Csphp::request()->cookie();
Csphp::request()->file();

//与URL相关的信息
Csphp::request()->getHost();
Csphp::request()->getReqUri();
Csphp::request()->getLastViewUrl();//用户最后一次浏览的 url

```

------------------------------------------------------------------

chk request type
=====
```php

//请求性质判断
Csphp::request()->isApi();
Csphp::request()->isAjax();
Csphp::request()->isJsonp();
Csphp::request()->isPost();
Csphp::request()->isGet();
Csphp::request()->isPut();
Csphp::request()->isRobot();
Csphp::request()->isPhone();

//用于判断 请求类型的相当配置
$cfg['jsonp_flag_vr']=array('g:callback', 'p:callback');
$cfg['ajax_flag_vr'] =array('g:_', 'p:_' );
$cfg['api_flag_vr']  =array('h:csp-api');

//检查当前请求是否符合条件
Csphp::isMatch($reqCond);
Csphp::request()->isMatch($reqCond);
$reqCond=array(
    'domain'=>'*',          //当前域名
    'router_prefix'=>'*',   //路由前缀
    'request_method'=>'*',  //HTTP 请求方法 GET POST PUT CLI
    'router_suffix'=>'*',   //路由后缀
    'entry_name'=>'*',      //入口名称
    'header_send'=>'headerkey,value',     //发送了某个头信息 value 可选
    'user_cond'=>'abc::abc',//用户自定义规则,是一个可调用的 回调或者服务定义器
);

```

------------------------------------------------------------------

get var by vroute
=====
```php

//use input 获取 用户输入
Csphp::v($vr,$def,$rule,$errHandle);
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

```

------------------------------------------------------------------

set or get data in page live time 
=====
```php

Csphp::data($k,$v);//for set
Csphp::data($k);   //for get

```

------------------------------------------------------------------

url constructor 
=====
```php

//url构造器
Csphp::url($r, $paramArrOrStr, $anchor, $hostKey='_default');
$cfg['host_keys'] = array(
	'_default'	=>'http://www.domain.com/',
	'home'		=>'http://www.domain.com/',
	'admin'		=>'http://admin.domain.com/',
	'statics'	=>'http://admin.domain.com/',
	'api'		=>'http://api.domain.com/',
	
);

```

------------------------------------------------------------------

response feature 
=====
```php

Csphp::response()->httpCode=200;
Csphp::response()->bodyData=null;//array or str
Csphp::response()->headerData=null;//array
Csphp::response()->setHeader($k,$v);
Csphp::response()->setCookie($k,$v, $ttl, $path, $domain);

Csphp::response()->redirect($url,$type);

Csphp::response()->setBody($strOrArray);
Csphp::response()->send();

```

------------------------------------------------------------------

log and debug func 
=====
```php

//调试与日志
Csphp::logDebug($cateGory='sys',$msg);	//DEBUG打开时，开启，用于调试
Csphp::logInfo($cateGory='sys',$msg);	//常规dump信息 如 accesslog
Csphp::logError($cateGory='sys',$msg);	//严重错误
Csphp::logWarning($cateGory='sys',$msg); //一般错误，暂不影响使用，如 rpc slow
$cfg['log_fromat']= "{time}\t{uri}:{route}\t{msg}";

Csphp::dump($msg);
Csphp::trace($msg, $cateGory);
Csphp::dump($msg);
Csphp::bmkStart($labelKey);
Csphp::bmkEnd($labelKey);
Csphp::halt();//alias for exit

```

------------------------------------------------------------------

load file or instantiate obj
=====
```php

//对象加载与实例化
Csphp::getPathByRoute($fRoute);
Csphp::getRealPathByRoute($fRoute);
Csphp::loadFile($fRoute);

Csphp::newClass($fRoute, $cfg=null);
Csphp::ctrl();
Csphp::mod();
Csphp::cls();
Csphp::ext();
Csphp::comp();

```

------------------------------------------------------------------

file route rule
=====
```php

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

```

------------------------------------------------------------------

Components feature
=====
```php

//组件的使用
Csphp::comp($comRoute, $cfg, $accessKey='')->anyMethod();
$cfg['components']=array(
    //这个key是访问名称
    'access_key'=> array(
        'cond'  =>$reqCond,//什么条件下加载组件
        'cls'   =>'',//对象定位路由
        'args'  =>array(

        )
    )
);

//组件的生命周期
Csphp::comp($accessKey)->start();
Csphp::comp($accessKey)->alterStart();
//php shutdown 的时候执行
Csphp::comp($accessKey)->stop();

Csphp::comp($accessKey)->anyOtherMethod();

```

------------------------------------------------------------------

Filters  feature
=====
```php

Csphp::runFilters($fRoute, $cfg=null);

```

------------------------------------------------------------------

hooks  feature
=====
```php

Csphp::doHooks();
Csphp::hook($hookName, $eventCbFunc);

```

------------------------------------------------------------------

event  feature
=====
```php

Csphp::fireEvent($eventName, $senderObj, $data=null);
Csphp::on($eventName, $eventCbFunc);

```

------------------------------------------------------------------

Tpl and controler
=====
```php

Csphp::$tplVars = array();
Csphp::controler()->assign($k, $v);
Csphp::controler()->assign($kvArr);
Csphp::controler()->fetch($tplRoute, $data);
Csphp::controler()->render($tplRoute, $data, $cacheOpt);

Csphp::controler()->display($tplRoure, $data, $cacheOpt);
Csphp::controler()->ajaxRst($rst, $code=0, $msg='OK', $tips=null);
Csphp::controler()->jsonpRst($cbName, $rst, $code=0, $msg='OK', $tips=null);
Csphp::controler()->isXXX();// isPost isAjax isApi isGet isPut isPhone isRobot

//模板
Csphp::tpl()->assign();
Csphp::tpl()->fetch();
Csphp::tpl()->render();
Csphp::tpl()->layout();
Csphp::tpl()->widget();

```

------------------------------------------------------------------

asset use
=====
```php

//前端资源,解决多机部署，域名分离，灰度上线问题
Csphp::asset($fRroute, $hostKey);
$cfg['asset']=array(
    'base_path'=>'/',
    'publish_path'=>'',
);

```

------------------------------------------------------------------

route feature
=====
```php

//初始化路由信息 解释 路由配置
Csphp::router()->init();
//动态添加路由配置
Csphp::router()->runTimeRouteRegister();
Csphp::router()->parse($req);
$routeInfo = array(
    'req_route'=>'a/b/CtrlclassName/actionMethod/vn1-v1/vn2-v2',
    'clean_route'=>'a/b/CtrlclassName/actionMethod',//清除变量后的路由
    'hit_rule'  =>'',   //命中中的 路由规则，可能是系统或者用户定义的: sys::xxxx, user::
    'real_rule'  =>'',  //最终要执行的 路由
    'ctrl_obj'  =>$ctrlObj,     //ctrl obj
    'action'    =>$actionName,  //str
    'route_var' =>array(
        'vn1'=>'v1',
        'vn2'=>'v2',
    )
);

//解释URL上的参数 如 .../vn-1/vn2-2 将产生 {vn:1,vn2:2} 的路由参数
Csphp::router()->cleanRoute();

//从路由配置中查找符合条件的规则
Csphp::router()->findRoute();
//路由配置规则 示例
$cfg['router'][] = array(
    //触发条件 参见 $reqCond 描述
    'req_cond'      =>$reqCond,
    //一些前置后置选项，主要是删除/增加， 前缀，后缀
    'pre_action'    =>array('delSuffix'=>'', 'delPrefix'=>'', 'addSuffix'=>'', 'addPrefix'=>''),
    //路由规则
    'rules' =>array(
        'user/{uid}'        =>'user/info',
        'user/post/{uid}'   =>'user/post',
        'user/get'          =>'user/info',
    )
);



Csphp::$vr = array();
Csphp::router()->parseVar();
Csphp::router()->getRouterVar();

Csphp::router()->getRouter();
Csphp::router()->controler();//ctr obj
Csphp::router()->getAction();//str action method name

Csphp::doAction();
```

------------------------------------------------------------------

common functions
=====
```php

//common functions
Csphp::toolkit()->xxx();
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

```

------------------------------------------------------------------

autoload files
=====
```php

//在框架引导期间 会自动加载的目录或者文件，值为加载的目录深度,如果加载目录为文件则后面的值无效，始终为 0
$cfg['auto_include_path']=array(
  'file'    =>0,
  'path'    =>1,
  'path2'   =>2,
);

```

------------------------------------------------------------------

modelbase feature
=====
```php

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

```

------------------------------------------------------------------

db feature
=====
```php

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

```

------------------------------------------------------------------

