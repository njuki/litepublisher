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

public function set(array $item) {
if (!isset($item['closed'])) $item['closed'] = $this->closed;
if (!isset($item['itemclosed'])) $item['itemclosed'] = $this->itemclosed;

$this->items[$item['type']] = $item;
$this->save();
}

public function build($type, $title, array $items) {
if (!isset($this->items[$type])) $this->error(sprintf('The "%s" type not exists', $type));
if (count($items) == 0) $this->error('Empty poll items');

$item = $this->items[$type];
    $theme = ttheme::i();
    $args = new targs();
    $args->id = '$id';
$args->type = $type;
    $args->title = $title;

$opened = '';
$closed = '';
    foreach ($items as $index => $text) {
      $args->checked = 0 == $index;
      $args->index = $index;
      $args->indexplus = $index + 1;
      $args->text = $text;
$args->votes = '$votes' . $index;
      $opened .= $theme->parsearg($item['item'], $args);
      $closed .= $theme->parsearg($item['itemclosed'], $args);
    }

$args->items = $opened;
$args->closed = $closed;
$args->id = '$id';
$args->type = $type;
    $args->title = $title;

      $args->rate ='$rate';
      $args->worst = 1;
      $args->best = count($items);

return array(
'type' => $type,
'title' => $title,
'items' => $items,
'opened' => $theme->parsearg($item['opened'], $args),
'closed' => $theme->parsearg($item['closed'], $args)
));
}  

}//class