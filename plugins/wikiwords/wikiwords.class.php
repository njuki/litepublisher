<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class twikiwords extends titems {
private $fix;

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
$this->dbversion = dbversion;
    parent::create();
    $this->table = 'wikiwords';
    $this->addevents('edited');
$this->fix = array();
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
if ($item['post'] == 0) return $item['word'];
$post = tpost::instance($item['post']);
return "<a href=\"$post->link#wikiword-$id\" title=\"{$item['word']}\">{$item['word']}</a>";
}
  
    public function add($word, $idpost) {
$word = trim($word);
$id = $this->IndexOf('word', $word);
if ($id > 0) return $id;
return $this->additem(array(
'post' => $idpost,
'word' => $word
    ));
  }

  public function edit($id, $word) {
return $this->setvalue($id, 'word', $word);
  }

public function postadded($idpost) {
if (count($this->fix) == 0) return;
foreach ($this->fix as $id => $post) {
if ($idpost == $post->id) $this->setvalue($id, 'post', $idpost);
}
}
  
  public function finddeleted() {
    $signs = $this->db->queryassoc("select id, hash from $this->thistable");
    if (!$signs) return array();
    $db = litepublisher::$db;
    $posts = tposts::instance();
    $db->table = $posts->rawtable;
    $deleted = array();
    foreach ($signs as $item) {
      $hash = $item['hash'];
      if (!$db->findid("locate('$hash', rawcontent) > 0")) $deleted[] = $item['id'];
      sleep(2);
    }
    
    return $deleted;
  }
  
  public function deletedeleted(array $deleted) {
    if (count($deleted) > 0) {
      $items = sprintf('(%s)', implode(',', $deleted));
      $this->db->delete("id in $items");
      $this->getdb($this->votestable)->delete("id in $items");
      sleep(2);
    }
  }
  
  public function optimize() {
    if ($this->finddeleted) $this->deletedeleted($this->finddeleted());
    $db = $this->getdb($this->userstable);
    $db->delete("id not in (select distinct user from $db->prefix$this->votestable)");
  }

  public function beforefilter($post, &$content) {
    if (preg_match_all('/\[wiki:(.*?)\]/i', $content, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
$word = $item[1];
$id = $this->add($word, $post->id);
if ($post->id == 0) $this->fix[$id] = $post;
$content = str_replace($item[0], "<a name=\"wikiword-$id\">$word</a>", $content);
}
}
  }

  public function filter(&$content) {
    if (preg_match_all('/\[\[(.*?)\]\]/i', $content, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
$word = $item[1];
$id =$this->add($word, 0);
$content = str_replace($item[0], "\$wikiwords->word_$id", $content);
}
}
}

}//class
?>