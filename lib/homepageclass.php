<?php

class thomepage extends TEventClass implements  ITemplate {
private $sitebars;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'homepage' ;
$this->data['idurl'] = 0;
    $this->data['text'] = '';
    $this->data['hideposts'] = false;
$this->data['showwidgets'] = true;
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
$this->setvalue('text', $s);
}

  public function sethideposts($value) {
$this->setvalue('hideposts', $value);
}

public function setshowwidgets($value) {
$this->setvalue('showwidgets', $value);
}

private function setvalue($name, $value) {
    if ($this->data[$name] != $value) {
      $this->data[$name] = $value;
      $this->save();
      $urlmap = turlmap::instance();
      $urlmap->setexpired($this->idurl);
    }
  }

public function getsitebar($index, &$s) {
if (!$this->showwidgets) return;
if ($index == 0) $this->sortwidgets();
$before  = '';
$theme = ttheme::instance();
$std = tstdwidgets::instance();
foreach ($this->sitebars[$index] as $name => $ajax) {
$content = $std->getcontent($name);
if ($ajax) {
$i = strpos($s, "widget$name");
$i = strpos($s, '>', $i);
      $s = substr_replace($s, $content, $i+ 1, 0);
} else {
$before .= $theme->getwidget($std->items[$name]['title'], $content, $name, $index);
}
}
$s = $before . $s;
}


//распределить между сайтбарами стандартные виджеты
private function sortwidgets() {
$sitebars = tsitebars::instance();
$last = $sitebars->count - 1;
$this->sitebars = array(0 => array());
$std = tstdwidgets::instance();
foreach ($std->names as $name) {
if (!isset($std->items[$name])) {
$classic[] = $name;
} else {
if ($std->items[$name]['ajax']) $this->ajaxwidgets[] = $name;
}
}



}
  
}//class

?>