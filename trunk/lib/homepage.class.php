<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class thomepage extends tevents implements  itemplate, itemplate2, imenu  {
  
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
    if (litepublisher::$urlmap->page == 1) $result .= sprintf($theme->content->simple, $this->text);
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
  
  public function setstedajax($value) {
    $this->setvalue('stdajx', $value);
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
  
  public function getsitebar() {
    if ($this->defaultswidgets) {
      $widgets = twidgets::instance();
      if ($this->showstandartcontent) {
        $std = tstdwidgets::instance();
        $std->disableajax = true;
        //чтобы кеш брался из другого файла, но есть опасность сохранения виджетов негде было
        $id = $widgets->id;
        $widgets->id = 'homepage';
        $result = $widgets->getcontent();
        $widgets->id = $id;
        return $result;
      }
      return $widgets->getcontent();
    }else {
      $widgets = twidgets::instance('homepage');
      return $widgets->getcontent();
    }
  }
// imenu  
  public function getparent() { return 0; }
  public function setparent($id) {}
  public function getorder() { return 0; }
  public function setorder($order) {}

}//class
?>