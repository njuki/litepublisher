<?php

class TSecondru extends TPlugin {
public $ru;
 
 public static function &Instance() {
  return GetInstance(__class__);
 }

  protected function CreateData() {
global $Options;
    parent::CreateData();
$this->ru = false;
}

public function Geturl() {
global $Options;
if ($this->ru) return $Options->Data['url'] . '/ru';
}

 public function BeforeRequest() {
      global $Options, $Urlmap, $paths;

    if ($this->ru = (strncmp('/ru/', $Urlmap->url, strlen('/ru/')) == 0) || ($Urlmap->url == '/ru')) {
      if ($Urlmap->url == '/ru') {
        $Urlmap->url = '/';
      } else {
        $Urlmap->url = substr($Urlmap->url, strlen('/ru'));
      }

      $paths['cache'] .= 'ru' . DIRECTORY_SEPARATOR;

$Options->Data['language'] = 'ru';
    }
}

}

?>