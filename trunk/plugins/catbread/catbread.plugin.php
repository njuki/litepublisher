<?php
/**
* lite publisher
* copyright (c) 2010 - 2013 vladimir yushko http://litepublisher.ru/ http://litepublisher.com/
* dual licensed under the mit (mit.txt)
* and gpl (gpl.txt) licenses.
**/

class catbread extends  tplugin {
  public $tml;
  public $cats;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addmap('tml', array());
$this->data['showhome'] = true;
$this->data['showchilds'] = true;
$this->data['childsortname'] = 'title';
$this->data['showsame'] = true;

    $this->cats = tcategories::i();
  }
  
  public function beforecat(&$result) {
  $cats = $this->cats;
    $idcat = $cats->id;
  if (!$idcat) return;
$result .= $this->getbread($idcat);

if ($this->showsame) {
      $idposts = $cats->getidposts($idcat);
$list = array();
foreach ($idposts as $idpost) {
$list = array_merge($list, tpost::i($idpost)->categories);
}      

array_clean($list);
array_delete_value($list, $idcat);
$result .= $this->getsimilar($list);
}

return $result;
  }

  public function themeparsed($theme) {
$tag = '$' . get_class($this) . '.catlinks';
if (strpos($theme->templates['content.post'], $tag)) return;
$theme->templates['content.post'] = '$' . get_class($this) . '.beforepost' .
str_replace('$post.catlinks', '$post.catlinks ' . $tag, $theme->templates['content.post']);

//for shop
if (isset($theme->templates['shop.product'])) $theme->templates['shop.product'] = '$' . get_class($this) . '.beforepost' .
str_replace('$post.catlinks', '$post.catlinks ' . $tag, $theme->templates['shop.product']);

}

    public function getbeforepost() {
$post = ttheme::$vars['post'];
if (!count($post->categories)) return '';
return  $this->getbread($post->categories[0]);
  }

public function getcatlinks() {
if (!$this->showsame) return '';
$post = ttheme::$vars['post'];
if (!count($post->categories)) return '';
return  $this->getsimilar($post->categories);
}
  
  public function getbread($idcat) {
if (!$idcat) return '';
$result = '';
$cats = $this->cats;
$cats->loadall();
$parents = $cats->getparents($idcat);
$parents = array_reverse($parents);

$showchilds = false;
if ($this->showchilds) {
foreach ($cats->items as $id => $item) {
if ($idcat == intval($item['parent'])) {
$showchilds = true;
break;
}
}
}

    $theme = ttheme::i();
$args = new targs();
$tml = $this->tml['item'];
$items = '';
if ($this->showhome) {
$args->url = '/';
$args->title = tlocal::i()->home;
$items .= $theme->parsearg($tml, $args);
}

foreach ($parents as $id) {
$args->add($cats->getitem($id));
$items .= $theme->parsearg($tml, $args);
}

$args->add($cats->getitem($idcat));
$items .= $theme->parsearg($this->tml['active'], $args);
if ($showchilds) $items .= $theme->parsearg($this->tml['child'], $args);

$args->item = $items;
$result .= $theme->parsearg($this->tml['items'], $args);

if ($showchilds) {
$args->item = $this->getchildcats($idcat);
$result .= $theme->parsearg($this->tml['childitems'], $args);
}

return sprintf($this->tml['container'], $result);
  }
  
  public function getchildcats($parent) {
$cats = $this->cats;
    $sorted = $cats->getsorted($parent, $this->childsortname, 0);
    if (!count($sorted)) return '';
    $result = '';
    $theme = ttheme::i();
    $args = new targs();
    $args->parent = $parent;
$tml_sub = $this->tml['childsubitems'];
    foreach($sorted as $id) {
      $item = $cats->getitem($id);
      $args->add($item);
if ($tml_sub && ($subitems = $this->getchildcats($id))) {
$args->subitems = $subitems;
      $args->subitems = $theme->parsearg($this->tml['childsubitems'], $args);
} else {
      $args->subitems = '';
}

      $result .= $theme->parsearg($this->tml['childitem'],$args);
    }

return $result;  
}

public function getsimilar($list) {
if (!$this->showsame || !count($list)) return '';
$cats = $this->cats;
$cats->loadall();
$parents = array();
foreach ($list as $id) {
$parents[] = $cats->getvalue($id, 'parent');
}

array_clean($parents);
if (!count($parents)) return '';

/* without db cant sort
$similar = array();
foreach ($cats->items as $id => $item) {
if (in_array($item['parent'], $parents)) $similar[] = $id;
}
*/

$parents = implode(',', $parents);
$list = implode(',', $list);
$similar = $cats->db->idselect("parent in ($parents) and id not in ($list) order by $this->childsortname asc");
array_clean($similar);
if (!count($similar)) return '';

    $theme = ttheme::i();
$args = new targs();
$items = '';
foreach ($similar as $id) {
$args->add($cats->getitem($id));
$items .= $theme->parsearg($this->tml['sameitem'], $args);
}

$args->item = $items;
return $theme->parsearg($this->tml['sameitems'], $args);
}

}//class