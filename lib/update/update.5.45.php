<?php

function update545() {
litepublisher::$site->jquery_version = '1.8.3';
  litepublisher::$site->jqueryui_version = '1.9.0';
  litepublisher::$site->save();
}