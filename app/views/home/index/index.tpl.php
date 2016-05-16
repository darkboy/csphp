<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>

        <title> Hello Csphp </title>

        <meta name="generator"  content="editplus" />
        <meta name="author"     content="" />
        <meta name="keywords"   content="" />
        <meta name="description" content="" />

        <!-- <script type="text/javascript" src="/statics/home/js/demo.js"></script> -->
        <?php $this->js("demo");?>
        <!-- <link rel="stylesheet" href="/statics/home/css/demo.css"/> -->
        <?php $this->css("demo");?>
        <!-- some data register by app output json format with js like var=$csphpConfig={}; -->
        <?php $this->jsData();?>

    </head>

    <body>
        <h1>Hello Csphp, now is <?php echo date("Y-m-d H:i:s");?></h1>
        <hr>
        <div>Ouput html: <?php $this->o('<div></div>');?></div><br>
        <div>ifo output: <?php $this->ifo(true,"yes","no");?></div><br>

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
