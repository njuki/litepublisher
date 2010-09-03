<?php

class tadminyoutubefeed extends tplugin {

  public static function instance() {
    return getinstance(__class__);
  }

public function getcontent() {
switch ($_POST['step']) {
case 1: return $this->getstep1();
case 2: return $this->getstep2();
}
}

public function getstep1() {
}

public function getstep2() {
}

public function processform() {
switch ($_POST['step']) {
case 1:
$url = trim($_POST['url']);
if ($s = http::get($url)) {

}

case 2:

}
}

}//class
?>