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
$this->data['showchilds'] = true;
$this->data['showsane'] = true;

        $this->items[$id] = array(
    'idtag' => $idtag,
    'sortname' => 'count',
    'showsubitems' => true,
    'showcount' => true,
    'maxcount' => 0,
    'template' => 'categories'
    );

    $this->cats = tcategories::i();
  }
  
  public function beforecat(&$result) {
  $cats = $this->cats;
    $idcat = $cats->id;
  if (!$idcat) return;
      $idposts = $cats->getidposts($idcat);
$list = array();
foreach ($idposts as $idpost) {
$list = array_merge($list, tpost::i($idpost)->categories);
}      

$result .= $this->getbread($idcat, $list);
  }
  
  public function beforepost($post, &$result) {
if (count($post->categories)) $result .= $this->getbread($post->categories[0], $post->categories);
  }
  
  public function getbread($idcat, $list) {
if (!$idcat) return '';
$result = '';
array_clean($list);
array_delete_value($list, $idcat);

$cats = $this->cats;
$parents = $cats->getparents($idcat);
$parents = array_reverse($parents);
$childs = $cats->getchilds($idcats);
$list = array_diff($list, $parents);

    $theme = ttheme::i();
$args = new targs();
$tml = $this->tml['item'];
foreach ($parents as $id) {
$args->add($cat->getitem($id));
$items .= $theme->parsearg($tml, $args);
}

$args->add($cats->getitem($idcat));
$items .= $theme->parsearg($this->tml['active'], $args);
$args->items = $items;
$result .= $theme->parsearg($this->tml['items'], $args);

if ($this->showchilds) {
$result .= $cats->getsortedcontent(array(
    'item' =>$this->tml['childitem'],
    'subcount' =>$theme->getwidgettml($sidebar, $item['template'], 'subcount'),
    'subitems' => $item['showsubitems'] ? $theme->getwidgettml($sidebar, $item['template'], 'subitems') : '',
    ), $idcat,
    $item['sortname'], $item['maxcount'], $item['showcount']);
}

if ($this->showsame) {
}

return $result;
  }
  
}//class