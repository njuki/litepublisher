<?php

function update421() {
litepublisher::$site->jquery_version = '1.5';
litepublisher::$site->save();

$parser = tthemeparser::instance();
if (!isset($parser->data['replacelang'])) {
$parser->data['replacelang'] = true;
$parser->save();
}

}