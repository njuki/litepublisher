<?php

function update504() {
  litepublisher::$site->jquery_version = '1.7';

$parser = tthemeparser::i();
$parser->data['removephp'] = true;
$parser->save();
}