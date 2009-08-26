<?php

class TImporter extends TEventClass {
public $dom;

  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
$this->basename = 'importer';
}

public function ImportFromString(&$s) {
    $this->dom = new domDocument;
    $this->dom->loadXML($s);
return $this->import();
}

public function importxmlfile($filename) {

}

}//class
?>