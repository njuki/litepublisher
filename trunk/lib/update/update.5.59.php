<?php

function update559() {
litepublisher::$classes->items['poststatus'] = array('kernel.admin.php', '', 'admin.posteditor.class.php');
litepublisher::$classes->save();

}