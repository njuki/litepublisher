<?php
function update354() {
tfiler::delete(litepublisher::$paths->data . 'themes' . DIRECTORY_SEPARATOR, false, false);
    litepublisher::$urlmap->clearcache();

litepublisher::$options->filtercommentstatus = true;
  }

?>