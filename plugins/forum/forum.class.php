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
$this->data['rootcat'] = 0;
  }

  public function themeparsed(ttheme $theme) {
if (($theme->name == 'forum') && !strpos($theme->templates['content.post'], '$forum.comboitems')) {
$theme->templates['content.post'] .= $this->combo_html;
$theme->templates['content.post'] = str_replace('$post.content', '$post.content' . $this->editlink, $theme->templates['content.post']);
}
}

public function getcomboitems() {
$filename = litepublisher::$paths->cache . 'forum.comboitems.php';
if ($result = tfilestorage::getfile($filename)) return $result;
$result = $this->get_categories();
tfilestorage::setfile($filename, $result);
return $result;
}

    public function get_categories() {
    $result = '';
    $categories = tcategories::i();
    $categories->loadall();
    if (count($items) == 0) $items = array_keys($categories->items);
    foreach ($items as $id) {
      $result .= sprintf('<option value="%s" %s>%s</option>', $id, $id == $idselected ? 'selected' : '', tadminhtml::specchars($categories->getvalue($id, 'title')));
    }
    return $result;
  }

}//class