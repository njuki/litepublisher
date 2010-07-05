<?php
function update356() {
ttheme::clearcache();

litepublisher::$classes->add('wordpress', 'wordpress.functions.php');

if (isset(litepublisher::$classes->items['tsourcefiles'])) {
litepublisher::$db->table = 'sourcefiles';
litepublisher::$db->update("content = ''", "filename like '%.php'");
}
  }

?>