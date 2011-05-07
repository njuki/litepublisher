<?php

function update458() {
if (class_exists('texternallinks')) {
$plugin = texternallinks::instance();
$plugin->data['exclude'] = array();
$plugin->save();

litepublisher::$classes->Add('tadminexternallinks', 'adminexternallinks.plugin.php', 'externallinks');
}
}