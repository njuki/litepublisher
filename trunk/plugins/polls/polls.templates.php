<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpolltemplates extends titems {

  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
$this->dbversion = false;
    parent::create();
$this->basename = 'polls' . DIRECTORY_SEPARATOR . 'templates';

    $this->data['defadd'] = false;
  }

public function add($type, $title, array $items) {
$types = tpolltypes::i();

    if ($poll['rate'] > 0) {
      $args->votes = array_sum($votes);
      $args->rate =1 + $poll['rate'] / 10;
      $args->worst = 1;
      $args->best = count($items);
      $result .= $theme->parsearg($this->templates['microformat'], $args);
    }
    
$this->items[++$this->autoid] = array(
'type' => $type,
'title' => $title,
'items' => $items,
'opened' => $opened,
'closed' => $closed
);
$this->save();
return $this->autoid;
}  

  public function setdefadd($v) {
    if ($v == $this->defadd) return;
    $this->data['defadd'] = $v;
    $this->data['garbage'] = ! $v;
    $this->save();
    $posts = tposts::i();
    if ($v) {
      $posts->added = $this->postadded;
      $posts->deleted = $this->postdeleted;
      $posts->aftercontent = $this->afterpost;
      $posts->syncmeta = true;
    } else {
      $posts->delete_event_class('added', get_class($this));
      $posts->delete_event_class('deleted', get_class($this));
      $posts->delete_event_class('aftercontent', get_class($this));
    }
  }
  
  public function postadded($idpost) {
    $post = tpost::i($idpost);
    $post->meta->poll = $this->add($this->deftitle, 'opened', $this->deftype, explode(',', $this->defitems));
  }
  
  public function afterpost(tpost $post, &$content) {
    if (isset($post->meta->poll)) {
      $content = $this->gethtml($post->meta->poll, true) . $content;
    }
  }
  
  public function postdeleted($id) {
    if (!dbversion) return;
    $meta = tmetapost::i($id);
    if (isset($meta->poll)) {
      $this->delete($meta->poll);
    }
  }
  
  public function filter(&$content) {
      if (preg_match_all('/\[poll\=(\d*?)\]/', $content, $m, PREG_SET_ORDER)) {
$polls = tpolls::i();
        foreach ($m as $item) {
$id = (int) $item[1];
if ($polls->itemexists($id)) {
$html = $polls->gethtml($id);
} else {
$html = '';
}

          $content = str_replace($item[0], $html, $content);
        }
      }
    }

}//class