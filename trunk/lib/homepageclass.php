<?php

class THomepage extends TEventClass implements  ITemplate {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'homepage' ;
$this->data['idurl'] = 0;
    $this->data['text'] = '';
    $this->data['hideposts'] = false;
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
    if ($urlmap->pagenumber == 1) $result .= $this->text;
    if ($this->hideposts) return $result;
    $items =  $this->getitems();
    $TemplatePost = &TTemplatePost::instance();
    $result .= $TemplatePost->PrintPosts($items);
    $Posts = tposts::instance();
    $result .=$TemplatePost->PrintNaviPages($options->home, $urlmap->pagenumber, ceil($Posts->archivescount / $options->postsperpage));
    return $result;
  }
  
  public function getitems() {
    global $options, $urlmap;
    $Posts = tposts::instance();
    return $Posts->GetPublishedRange($urlmap->pagenumber, $options->postsperpage);
  }
  
  public function settext($s) {
    global $options;
    if ($this->text != $s) {
      $this->data['text'] = $s;
      $this->save();
      $urlmap = turlmap::instance();
      $urlmap->setexpired($this->idurl);
    }
  }
  
  public function sethideposts($value) {
    global $options;
    if ($this->hideposts != $value) {
      $this->data['hideposts'] = $value;
      $this->save();
      $urlmap = turlmap::instance();
      $urlmap->setexpired($this->idurl);
    }
  }
  
}//class

?>