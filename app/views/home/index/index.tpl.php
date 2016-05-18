<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>

        <title> Hello Csphp </title>

        <meta name="generator"  content="editplus" />
        <meta name="author"     content="" />
        <meta name="keywords"   content="" />
        <meta name="description" content="" />

        <!--

        静态资源引入相关

        可以一次性 引入多个资源
        <?php $this->js("demo,-demo,/demo,-dirname/abc");?>

        -->

        <!--
        注：引入一个 当前模块的 的 demo.css
        <?php $this->css("demo");?>

        注：- 号开头 则表示 模块根 为前缀
        <?php $this->css("-demo");?>

        注：- 号开头 则表示 模块根 为前缀， 第二个参数为 指定 http 前棳，
        <?php $this->css("-dirname/demo",'home');?>

        注：- 号开头 则表示 模块根 为前缀,第三个参数为 标签添加自定义参数
        <?php $this->css("-dirname/demo",'api',['charset'=>'utf-8']);?>

        注：/ 号开头 则表示 站点根 为前缀
        <?php $this->css("/demo");?>

        获取静态资源版本号: <?php echo $this->getStaticsVersion();?>

        给某个第三方资源加上版本号: <?php echo $this->wrapByStaticsVersion('http://www.a.com/abc.js');?>

        -->

        <!--

        给页面注入 JS 数据
        可以在模模中 通过 $this->jsData('test','test-v1'); 注入，
        或者 在其它任意位置 Csphp::view()->jsData('test3','v3');
        注入的JS变量名为 $csphpConfig
        如 var $csphpConfig={};

        -->
        <?php
        //你可以在模板中为 $csphpConfig 注入数据，
        $this->jsData('test','test-v1');
        //可以直接给数组
        $this->jsData(['test2'=>'test-v2']);

        //你也可以在 程序的任意地方调用
        Csphp::view()->jsData('test3','v3');

        //不带参数时表示在模板中输出 JS变量数据
        $this->jsData();
        ?>



    </head>

    <body>
        <h1>Hello Csphp, now is <?php echo date("Y-m-d H:i:s");?></h1>
        <hr>
        <div>html Ouput : <?php $this->o('<div></div>');?></div><br>
        <div>ifo Output: <?php $this->ifo(true,"yes","no");?></div><br>
        <div>ifo Output: <?php $this->ifo(false,"yes","no");?></div><br>
        <div><?php printf("TimeUse: %.4fms ", Csphp::getTimeUse()*1000);;?></div><br>
        <hr>
        <!-- 加载子模板的几种方式 -->
        <!-- 当前控制器的模板目录 可以用 . 表示 -->
        <?php $this->widget('.index_widget',                array('by'=>'tpl_1')); $ti=2;?>

        <!-- 当前模块的模板目录，可以省略 或者 用 @m-view/ 表示 -->
        <?php $this->widget('index/index_widget',           array('by'=>'tpl_'.($ti++)) );?>
        <?php $this->widget('@m-view/index/index_widget',   array('by'=>'tpl_'.($ti++)) );?>

        <!-- 应用模板根目录 可以用 / 或者  @view/ 表示 -->
        <?php $this->widget('/home/index/index_widget',     array('by'=>'tpl_'.($ti++)) );?>
        <?php $this->widget('@view/home/index/index_widget',array('by'=>'tpl_'.($ti++)) );?>

        <?php $this->xpipe('');?>
        <?php $this->ajax('');?>

        <pre><?php print_r(Csphp::router()->routeInfo);?></pre>


    </body>
</html>
