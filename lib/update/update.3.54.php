<?php
function update353() {
tfiler::delete(litepublisher::$paths->data . 'themes' . DIRECTORY_SEPARATOR, false, false);
    litepublisher::$urlmap->clearcache();
  }

?>