<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title> Hello Csphp </title>
        <meta name="generator" content="editplus" />
        <meta name="author" content="" />
        <meta name="keywords" content="" />
        <meta name="description" content="" />
        <?php $this->js("a,b,c");?>
        <?php $this->css("a,b,c");?>
        <?php $this->data();?>
    </head>

    <body>
        <h1>Hello Csphp, now is <?php echo date("Y-m-d H:i:s");?></h1>
        <hr>
        <div><?php $this->o('<div></div>');?></div><br>
        <div><?php $this->ifo(true,"yes","no");?></div><br>
        <?php $this->widget(array(),'.index_widget');?>
    </body>
</html>
