<?php

function update499() {
$item = litepublisher::$urlmap->findurl('/admin/subscribers/');
  litepublisher::$urlmap->setvalue($item['id'], 'type', 'get');
}