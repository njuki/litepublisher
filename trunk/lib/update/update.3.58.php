<?php
function update358() {
litepublisher::$options->dateformat = tthemeparser::strftimetodate(litepublisher::$options->dateformat);
ttheme::clearcache();
  }

?>