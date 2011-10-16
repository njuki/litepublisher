<?php

function update503() {
if (!litepublisher::$urlmap->finditem('/users.htm')) {
  litepublisher::$urlmap->add('/users.htm', 'tuserpages', 'url', 'get');
}
}