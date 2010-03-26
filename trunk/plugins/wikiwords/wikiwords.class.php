<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class twikiwords extends titems {
  public $itemsposts;
  private $fix;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->fix = array();
    $this->addevents('edited');
    $this->table = 'wikiwords';
    if (!$this->dbversion)  $this->data['itemsposts'] = array();
    $this->itemsposts = new titemspostsowner ($this);
  }
  
  public function __get($name) {
    if (strbegin($name, 'word_')) {
      $id = (int) substr($name, strlen('word_'));
      if (($id > 0) && $this->itemexists($id)) {
        return $this->getlink($id);
      }
      return '';
    }
    
    return parent::__get($name);
  }
  
  public function getlink($id) {
    $item = $this->getitem($id);
    $word = $item['word'];
    $items = $this->itemsposts->getposts($id);
    switch (count($items)) {
      case 0:
      return sprintf('<strong>%s</strong>', $word);
      
      case 1:
      $post = tpost::instance($items[0]);
      return sprintf('<a href="%1$s#wikiword-%3$d" title="%2$s">%2$s</a>', $post->link, $word, $id);
      
      default:
      $links = array();
      $posts = tposts::instance();
      $posts->loaditems($items);
      foreach ($items as $idpost) {
        $post = tpost::instance($idpost);
        $links[] = sprintf('<a href="%1$s#wikiword-%3$d" title="%2$s">%2$s</a>', $post->link, $post->title, $id);
      }
      
      return sprintf('<strong>%s</strong> (%s)', $word, implode(', ', $links));
    }
  }
  
  public function add($word, $idpost) {
    $word = trim(strip_tags($word));
    if ($word == '') return false;
    $id = $this->IndexOf('word', $word);
    if (!$id) $id = $this->additem(array('word' => $word));
    if (($idpost > 0) && !$this->itemsposts->exists($idpost, $id)) {
      $this->itemsposts->add($idpost, $id);
    }
    return $id;
  }
  
  public function edit($id, $word) {
    return $this->setvalue($id, 'word', $word);
  }
  
  public function delete($id) {
    if (!$this->itemexists($id)) return false;
    $this->itemsposts->deleteitem($id);
    return parent::delete($id);
  }
  
  public function deleteword($word) {
    if ($id = $this->IndexOf('word', $word)) return $this->delete($id);
  }
  
  public function getword($word) {
    if ($id =$this->add($word, 0)) {
      return '$wikiwords.word_' . $id;
    }
    return '';
  }
  
  public function postadded($idpost) {
    if (count($this->fix) == 0) return;
    $this->lock();
    foreach ($this->fix as $id => $post) {
      if ($idpost == $post->id) {
        $this->itemsposts->add($idpost, $id);
        unset($this->fix[$id]);
      }
    }
    $this->unlock();
  }
  
  public function postdeleted($idpost) {
    $this->itemsposts->deletepost($idpost);
  }
  
  public function beforefilter($post, &$content) {
    $this->createwords($post, $content);
  }
  
  public function filter(&$content) {
    $this->replacewords($content);
  }
  
  public function createwords($post, &$content) {
    $result = array();
    if (preg_match_all('/\[wiki:(.*?)\]/i', $content, $m, PREG_SET_ORDER)) {
      $this->lock();
      foreach ($m as $item) {
        $word = $item[1];
        if ($id = $this->add($word, $post->id)) {
          $result[] = $id;
          if ($post->id == 0) $this->fix[$id] = $post;
          $content = str_replace($item[0], "<a name=\"wikiword-$id\">$word</a>", $content);
        }
      }
      $this->unlock();
    }
    return $result;
  }
  
  public function replacewords(&$content) {
    $result = array();
    if (preg_match_all('/\[\[(.*?)\]\]/i', $content, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
        $word = $item[1];
        if ($id =$this->add($word, 0)) {
          $result[] = $id;
          $content = str_replace($item[0], "\$wikiwords.word_$id", $content);
        }
      }
    }
    return $result;
  }
  
}//class
?>