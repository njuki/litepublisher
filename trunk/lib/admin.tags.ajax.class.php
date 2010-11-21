<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tajaxtageditor extends tajaxposteditor  {
  
  public static function instance() {
    return getinstance(__class__);
  }

public function install() {
litepublisher::$urlmap->addget('/admin/ajaxtageditor.htm', get_class($this));
}
  
  public function request($arg) {
if ($err = self::auth()) return $err;
return $this->getcontent();
}

public function getcontent() {
$tags = tadminhtml::getparam('type', 'tags') == 'tags' ? ttags::instance : tcategories::instance();if ($err = self::auth()) return $err;
    $id = tadminmenu::idget();
    if ($id > 0) {
      if (!$tags->itemexists($id)) return self::error403();
    }

$theme = tview::instance(tviews::instance()->defaults['admin'])->theme;
   $html = tadminhtml ::instance();
    $html->section = 'tags';
$lang = tlocal::instance('tags');

switch ($_GET['get']) {
case 'view':
$result = tadminviews::getcomboview($tags->contents->getvalue($id, 'idview'));
if ($icons = tadminicons::getradio($post->icon)) {
$result .= $html->h2->icons;
$result .= $icons;
}
break;

case 'seo':
$form = new tautoform($post, 'editor', 'editor');
$form->add($form->url, $form->keywords, $form->description);
$result = $form->getcontent();
break;

case 'contenttabs':
    $args = targs::instance();
$args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$post->id&get");
$result = $html->contenttabs($args);
break;

case 'text':
$result = $this->geteditor('excerpt', $post->excerpt);
break;

default:
$result = var_export($_GET, true);
}
return turlmap::htmlheader(false) . $result;
}

}//class
?>