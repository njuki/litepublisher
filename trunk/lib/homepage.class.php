<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class thomepage extends tevents implements  itemplate {

  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'homepage' ;
$this->data['idurl'] = 0;
    $this->data['hideposts'] = false;
$this->data['defaultswidgets'] = true;
$this->data['showstandartcontent'] = true;
    $this->data['text'] = '';
  }
  
  //ITemplate
public function request($arg) {}
public function gettitle() {}
public function gethead() {}
public function getkeywords() {}
public function getdescription() {}
  
  public function GetTemplateContent() {
    global $options, $urlmap;
    $result = '';
    if ($urlmap->page == 1) $result .= $this->text;
    if ($this->hideposts) return $result;
    $items =  $this->getitems();
$theme = ttheme::instance();
    $result .= $theme->getposts($items, false);
    $Posts = tposts::instance();
    $result .=$theme->getpages($options->home, $urlmap->page, ceil($Posts->archivescount / $options->postsperpage));
    return $result;
  }
  
  public function getitems() {
    global $options, $urlmap;
    $Posts = tposts::instance();
    return $Posts->GetPublishedRange($urlmap->page, $options->postsperpage);
  }
  
  public function settext($s) {
$this->setvalue('text', $s);
}

  public function sethideposts($value) {
$this->setvalue('hideposts', $value);
}

public function setstedajax($value) {
$this->setvalue('stdajx', $value);
}

private function setvalue($name, $value) {
    if ($this->data[$name] != $value) {
      $this->data[$name] = $value;
      $this->save();
      $urlmap = turlmap::instance();
      $urlmap->setexpired($this->idurl);
    }
  }

public function getsitebar() {
if ($this->specsitebars) {
$widgets = twidgets::instance('homepage');
return $widgets->getcontent();
} else {
$widgets = twidgets::instance();
if (!$this->showstandartcontent) return $widgets->getcontent();


}

}//class
?>