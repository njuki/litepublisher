<?php
function update586() {
  litepublisher::$options->solt = md5uniq();

    $expired = time() + 31536000;
    $cookie = md5uniq();
    litepublisher::$options->setcookies($cookie, $expired);
}