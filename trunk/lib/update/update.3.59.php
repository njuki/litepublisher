<?php
function update359() {
$notfound = tnotfound404::instance();
if (!isset($notfound->data['notify'])) }{
$notfound->data['notify'] = true;
$notfound->save();
}
  }

?>