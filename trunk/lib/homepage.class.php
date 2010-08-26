<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class thomepage extends tevents implements  itemplate, itemplate2, imenu  {
  public $sitebars;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'homepage' ;
    $this->data['idurl'] = 0;
    $this->data['image'] = '';
    $this->data['hideposts'] = false;
    $this->data['defaultsitebar'] = true;
    $this->data['ajax'] = false;
    $this->addmap('sitebars', array(array(), array(), array()));
    $this->data['text'] = '';
    $this->data['tmlfile'] = '';
    $this->data['theme'] = '';
  }
  
  //ITemplate
public function request($arg) {}
public function gettitle() {}
public function gethead() {}
public function getkeywords() {}
public function getdescription() {}
  
  public function getcont() {
    $result = '';
    $theme = ttheme::instance();
    if (litepublisher::$urlmap->page == 1) {
$image = $this->image;
if ($image != '') {
if (!strbegin($image, 'http://')) $image = litepublisher::$options->files . $image;
$image = sprintf('<img src="%s" algt="Home image" />', $image);
}
$result .= $theme->simple($image . $this->text);
}
    if ($this->hideposts) return $result;
    $items =  $this->getitems();
    
    $result .= $theme->getposts($items, false);
    $Posts = tposts::instance();
    $result .=$theme->getpages(litepublisher::$options->home, litepublisher::$urlmap->page, ceil($Posts->archivescount / litepublisher::$options->perpage));
    return $result;
  }
  
  public function getitems() {
    $Posts = tposts::instance();
    return $Posts->GetPublishedRange(litepublisher::$urlmap->page, litepublisher::$options->perpage);
  }
  
  public function settext($s) {
    $this->setvalue('text', $s);
  }
  
  public function sethideposts($value) {
    $this->setvalue('hideposts', $value);
  }
  
  private function setvalue($name, $value) {
    if ($this->data[$name] != $value) {
      $this->data[$name] = $value;
      $this->save();
      litepublisher::$urlmap->setexpired($this->idurl);
    }
  }
  
  //ITemplate2
public function afterrequest(&$content) {}
  
  public function getwidgets(array &$items, $sitebar) {
    if ($this->defaultsitebar) {
      if (!$this->ajax) {
        foreach ($items as $i => $item) {
          $items[$i]['ajax'] = false;
        }
      }
    }else {
      if (isset($this->sitebars[$sitebar])) $items = $this->sitebars[$sitebar];
    }
  }
  
  // imenu
public function getparent() { return 0; }
public function setparent($id) {}
public function getorder() { return 0; }
public function setorder($order) {}
  
}//class
?>