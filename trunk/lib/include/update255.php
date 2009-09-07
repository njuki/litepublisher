<?php
class TTempClasses extends TItems {
  public $classes;

  protected function CreateData() {
    parent::CreateData();
$this->basename = 'tempclasses';
    $this->AddDataMap('classes', array());
  }

public function Setbasename($value) {
$this->basename = $value;
}

}//class

function Update255() {
global $paths, $Urlmap, $Options;
$Options->version = '2.55';

      $inifile = parse_ini_file($paths['lib'] . 'install' . DIRECTORY_SEPARATOR . 'classes.ini', true);

$classes = new TTempClasses ();
$classes->Lock();
$classes->Setbasename ( 'classes');
$classes->items = TClasses::$items;
$classes->classes = $inifile['classes'];
$classes->Unlock();

    @header("Location: $Options->url/admin/service/?update=1");
    exit();

}
?>