<?php

function update472() {
litepublisher::$urlmap->delete('/comusers.htm');
  litepublisher::$urlmap->addget('/comusers.htm', 'tcomusers');
}