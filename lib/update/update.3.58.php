<?php
function update358() {
litepublisher::$options->dateformat = tthemeparser::strftimeto date(litepublisher::$options->dateformat);
ttheme::clearcache();
  }

?>