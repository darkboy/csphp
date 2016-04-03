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
        <?php $this->data();?>

    </head>

    <body>
        <h1>Hello Csphp, now is <?php echo date("Y-m-d H:i:s");?></h1>
        <hr>
        <div><?php $this->o('<div></div>');?></div><br>
        <div><?php $this->ifo(true,"yes","no");?></div><br>

        <!-- 加载子模板的四种方式 -->
        <?php $this->widget('.index_widget');?>
        <?php $this->widget('/index/index_widget');?>
        <?php $this->widget('@m-view/index/index_widget');?>
        <?php $this->widget('@view/home/index/index_widget');?>

        <pre><?php print_r(Csphp::router()->routeInfo);?></pre>

    </body>
</html>
