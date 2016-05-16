
<div style="display:block;margin-bottom: 10px;border-bottom: dashed 1px gray; padding-bottom: 10px;">Hello widget called by <?php $this->o($by);?> : <?php echo Csphp::getTimeUse();?></div>

<?php Csphp::log()->logDebug("Debug...");?>
<?php Csphp::log()->logInfo("Debug {{test}}",['test'=>'demoLogVar']);?>
<?php Csphp::log()->logWarning("Debug {{test}}",['test'=>'demoLogVar']);?>
<?php Csphp::log()->logError(['err'=>'errmsg'],['test'=>'demoLogVar']);?>