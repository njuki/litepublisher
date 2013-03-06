<?php

function update551() {
litepublisher::$classes->add('tsinglemenu', 'menu.class.php');

$js = tjsmerger::i();
  $js->add('default', '/js/litepublisher/filelist.min.js');
}