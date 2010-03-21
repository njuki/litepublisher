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
$word = $item['word'];
if ($item['post'] == 0) return $word;
// найти все посты  с одинаковыми словами
if ($this->dbversion) {
$items = $this->select("id <> $id and word = ". dbquote($word), '');
} else {
$items = array();
foreach ($this->items as $idword => $worditem) {
if (($word == $worditem['word']) && (4id != $idword)) $items[] = $idword;
}
}

if (count($items) == 0) {
$post = tpost::instance($item['post']);
return "<a href=\"$post->link#wikiword-$id\" title=\"{$item['word']}\">{$item['word']}</a>";
} else {
$posts = tposts::instance();
}
}
  
    public function add($word, $idpost) {
$word = trim(strip_tags($word));
if ($word == '') return false;
$id = $this->IndexOf('word', $word);
if ($id > 0) {
$item = $this->getitem($id);
if ((0 == $item['post']) && (0 != $idpost)) {
$this->setvalue($id, 'post', $idpost);
if (!$this->dbversion) $this->save();
return $id;
} elseif ($idpost == $item['post']) {
return $id;
} else {
return $this->additem(array(
'parent' => $item['post'],
'post' => $idpost,
'word' => ''
    ));
}
}

return $this->additem(array(
'parent' => 0,
'post' => $idpost,
'word' => $word
    ));
  }

  public function edit($id, $word) {
return $this->setvalue($id, 'word', $word);
  }

public function delete($id) {
if (!$this->itemexists($id)) return false;
$item = $this->getitem($id);
if ($item['parent'] == 0) {
// назначить дежателем слова первого найденного
$idnext = $this->IndexOf('parent', $id);
if ($idnext > 0) {
$nextitem = $this->getitem($idnext);
$nextitem['word'] = $item['word'];
$nextitem['parent'] = 0;
$this->items[$idnext] = $nextitem;
if ($this->dbversion) {
$this->db->updateassoc($nextitem);
$this->db->update("parent =$idnext", "parent = $id");
} else {
foreach ($this->items as $idword => $itemword) {
if ($id == $itemword['parent']) $this->items['idword]['parent'] = $idnext;
}
}
}
return parent::delete($id);
}

public function deleteword($word) {
}

public function postadded($idpost) {
if (count($this->fix) == 0) return;
$this->lock();
foreach ($this->fix as $id => $post) {
if ($idpost == $post->id) $this->setvalue($id, 'post', $idpost);
}
$this->unlock();
$this->fix = array();
}

public function postdeleted($idpost) {
if ($this->dbversion) {
$this->db->update('post = 0', "post = $idpost");
} else {
$changed = false;
foreach ($this->items as $id => $item) {
if ($idpost == $item['post']) {
$this->items[$id]['post'] = 0;
$changed = true;
}
}
if ($changed) $this->save();
}
}
  
  public function optimize() {
$this->CallSatellite('optimize');
  }
  
  public function beforefilter($post, &$content) {
    if (preg_match_all('/\[wiki:(.*?)\]/i', $content, $m, PREG_SET_ORDER)) {
$this->lock();
      foreach ($m as $item) {
$word = $item[1];
$id = $this->add($word, $post->id);
if ($post->id == 0) $this->fix[$id] = $post;
$content = str_replace($item[0], "<a name=\"wikiword-$id\">$word</a>", $content);
}
$this->unlock();
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