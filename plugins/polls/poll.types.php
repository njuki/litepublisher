<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpoltypes extends titems {

  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
$this->dbversion = false;
    parent::create();
$this->basename = 'polls' . DIRECTORY_SEPARATOR . 'types';
  }

public function set($type, $tmlitem, $tmlitems) {
$this->items[$type] = array(
'item' => $tmlitem,
'items' => $tmlitems
);
$this->save();
}

public function getmicroformat($idpoll) {
$item = tpolls::i()->getitem($idpoll);
    if ($item['rate'] ==  0) return '';

$args = new targs();
      $args->votes = $item['total'];
      $args->rate =1 + $poll['rate'] / 10;
      $args->worst = 1;
      $args->best = count($items);
return ttheme::i()->parsearg($this->tml_microformat, $args);
}
    
public function build($type, $title, array $items) {
if (!isset($this->items[$type])) $this->error(sprintf('The "%s" type not exists', $type));
if (count($items) == 0) $this->error('Empty poll items');

    $theme = ttheme::i();
    $args = new targs();
    $args->id = '$id';
$args->type = $type;
    $args->title = $title;
    $tmlitem = $this->getvalue($type, 'item');
$pollitems = '';
    foreach ($items as $index => $itemtext) {
      $args->checked = 0 == $index;
      $args->index = $index;
      $args->indexplus = $index + 1;
      $args->text = $itemtext;
      $pollitems .= $theme->parsearg($tmlitem, $args);
    }

$args->items = $pollitems;
$args->id = '$id';
$args->type = $type;
    $args->title = $title;

$tmlitems = $this->getvalue($type, 'items');
$tml = $theme->parsearg($tmlitems, $args);

return array(
'type' => $type,
'title' => $title,
'items' => $items,
'tml' => $tml
));
}  

}//class