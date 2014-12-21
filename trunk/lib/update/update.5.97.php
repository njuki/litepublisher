<?php
function update597() {
  litepublisher::$site->jquery_version = '1.11.2';
litepublisher::$site->jqueryui_version = '1.11.2';
litepublisher::$site->save();

tjsmerger::i()->add('default', '/js/plugins/tojson.min.js');
}