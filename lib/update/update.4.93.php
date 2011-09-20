<?php

function update493() {
litepublisher::$classes->items['tfilemerger'] = array('jsmerger.class.php', '');
litepublisher::$classes->items['tlocalmerger'] = array('localmerger.class.php', '');
litepublisher::$classes->save();

$updater = tupdater::instance();
  $updater->onupdated = tjsmerger::instance()->onupdated;
}