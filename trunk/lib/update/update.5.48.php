<?php

function update548() {
litepublisher::$site->jquery_version = '1.9.0';
  litepublisher::$site->jqueryui_version = '1.9.2';
  litepublisher::$site->save();
  
  $t = ttemplate::i();
  $t-footer = str_replace('2012', '2013', $t-footer);
  $t->save();
}