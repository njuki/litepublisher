<?php
function update585() {
  litepublisher::$site->data['mapoptions'] = array(
'version' => 'version',
'language' => 'language',
);

litepublisher::$site->save();
}