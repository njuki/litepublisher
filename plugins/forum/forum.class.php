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
$this->data['idperm'] = 0;
$this->data['rootcat'] = 0;
$this->data['moderate'] = false;
$this->data['comstatus'] = 'reg';
$this->data['comboitems'] = '';
  }

  public function themeparsed(ttheme $theme) {
if (($theme->name == 'forum') && !strpos($theme->templates['content.post'], '$forum.comboitems')) {
$html = tadminhtml::i();
$section = $html->section;
$html->push_section('forum');
$lang = tlocal::admin('forum');

$combo = str_replace('\'', '"', $theme->parse($html->combocats));
//$this->categories_changed();

$theme->templates['content.post'] .= $combo;
$theme->templates['content.excerpts'] .= $combo;

$theme->templates['content.post'] = str_replace('\'', '"', str_replace('$post.content',
'$post.content ' . $theme->replacelang($html->editlink, $lang),
 $theme->templates['content.post']));

$theme->templates['index'] = str_replace('$custom.breadcrumbs', '$forum.breadcrumbs',
$theme->templates['index']);
$html->pop_section();
}
}

public function categories_changed() {
$this->comboitems = $this->getcats($this->rootcat, '');
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


public function getbreadcrumbs() {
$context = litepublisher::$urlmap->context;
if ($context instanceof tpost) {
$idcat = $context->idcat;
} elseif ($context instanceof tcategories) {
$idcat = $context->id;
} else {
$idcat = 0;
}

if ($idcat == 0) return '';
$filename = litepublisher::$paths->cache . $idcat . '.breadcrumbs.php';
if ($result = tfilestorage::getfile($filename)) return $result;
$result = $this->build_breadcrumbs($idcat);
tfilestorage::setfile($filename, $result);
return $result;
}

public function build_breadcrumbs($idcat) {
$cats = tcategories::i();
$cats->loadall();
$list = array($idcat);
while ()
}
foreach ($list as $id) {
}
return $result;
}

}//class