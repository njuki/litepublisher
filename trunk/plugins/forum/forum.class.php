<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tforum extends tplugin {

  public static function i() {
    return getinstance(__class__);
  }
  
  public function create() {
    parent::create();
$this->data['idview'] = 1;
$this->data['idviewidperm= 0;
$this->data['rootcat'] = 0;
$this->data['moderate'] = false;
$this->data['comstatus'] = 'reg';
$this->data['comboitems'] = '';
  }

  public function themeparsed(ttheme $theme) {
if (($theme->name == 'forum') && !strpos($theme->templates['content.post'], '$forum.comboitems')) {
$html = tadminhtml::i();
$html->section = 'forum';
$lang = tlocal::admin('forum');
$combo = $theme->parse($html->combocats);
$theme->templates['content.post'] .= $combo;
$theme->templates['content.excerpts'] .= $combo;

$theme->templates['content.post'] = str_replace('$post.content', '$post.content' . $theme->replacelang($html->editlink, $lang), $theme->templates['content.post']);
}
}

public function categories_changed() {
$this->comboitems = $this->getcats($this->rootcat);
$this->save();
}

    public function getcats($idparent, $pretitle) {
    $result = '';
    $cats = tcategories::i();
$cats->loadall();
$items = $cats->db->idselect("parent = $idparent order by title asc");
    foreach ($items as $id) {
$item = $cats->getitem($id);
      $result .= sprintf('<option value="%s" data-url="%s">%s%s</option>', $id, $item['url'], $pretitle, $item['title']);
$result .= $this->getcats($id, $item['title'] . ' / ');
    }
    return $result;
  }

}//class