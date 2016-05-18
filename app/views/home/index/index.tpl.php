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

        给苛个第三方资源加上版本号: <?php echo $this->wrapByStaticsVersion('http://www.a.com/abc.js');?>
        -->

        <!-- some data register by app output json format with js like var=$csphpConfig={}; -->
        <?php
        //你可以在模板中为 $csphpConfig 注入数据，
        $this->jsData('test','test-v1');
        $this->jsData(['test2'=>'test-v2']);
        //你也可以在 程序的任意地方调用
        Csphp::view()->jsData('test3','v3');

        $this->jsData();?>



    </head>

    <body>
        <h1>Hello Csphp, now is <?php echo date("Y-m-d H:i:s");?></h1>
        <hr>
        <div>html Ouput : <?php $this->o('<div></div>');?></div><br>
        <div>ifo Output: <?php $this->ifo(true,"yes","no");?></div><br>
        <div>ifo Output: <?php $this->ifo(false,"yes","no");?></div><br>

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

        <pre><?php printf("\n\nTimeUse:%.4f\n", Csphp::getTimeUse()); print_r(Csphp::router()->routeInfo);?></pre>


    </body>
</html>
