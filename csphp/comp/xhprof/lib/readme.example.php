<?php
//在需要检查的程序中加入,如下两行

//开头插入如下这行
require 'start.xhprof.php';

//需要检测的代码,写在中间....

//结束处
require 'end.xhprof.php';

/*
下面是一些参数说明
    Inclusive Time                  包括子函数所有执行时间。
    Exclusive Time/Self Time        函数执行本身花费的时间，不包括子树执行时间。
    Wall Time                       花去了的时间或挂钟时间。
    CPU Time                        用户耗的时间+内核耗的时间
    Inclusive CPU                   包括子函数一起所占用的CPU
    Exclusive CPU                   函数自身所占用的CPU
 */

