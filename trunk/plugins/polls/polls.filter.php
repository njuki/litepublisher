<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpollsfilter extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['garbage'] = true;
    $this->data['defadd'] = false;
  }
  
  private function extractitems($s) {
    $result = array();
    $lines = explode("\n", $s);
    foreach ($lines as $name) {
      $name = trim($name);
      if (($name == '')  || ($name[0] == '[')) continue;
      $result[] = $name;
    }
    return $result;
  }
  
  private function extractvalues($s) {
    $result = array();
    $lines = explode("\n", $s);
    foreach ($lines as $line) {
      $line = trim($line);
      if (($line == '')  || ($line[0] == '[')) continue;
      if ($i = strpos($line, '=')) {
        $name = trim(substr($line, 0, $i));
        $value = trim(substr($line, $i + 1));
        if (($name != '') && ($value != '')) $result[$name] = $value;
      }
    }
    return $result;
  }
  
  public function beforefilter($post, &$content) {
    $content = str_replace(array("\r\n", "\r"), "\n", $content);
    $i = 0;
    while (is_int($i = strpos($content, '[poll]', $i))) {
      $j = strpos($content, '[/poll]', $i);
      if ($j == false) {
        // simple form and need to find empty string
        $j = strpos($content, "\n\n", $i);
        $s = substr($content, $i, $j - $i);
        $items = $this->extractitems($s);
        $id = $this->add('', '', '', $items);
      } else {
        // has poll id?
        $j += strlen("[/poll]");
        $s = substr($content, $i, $j - $i);
        // extract items section
        $k = strpos($s, '[items]');
        $l = strpos($s, '[/items]');
        $items = $this->extractitems(substr($s, $k, $l));
        $s = substr_replace($s, '', $k, $l - $k);
        $values = $this->extractvalues($s);
        $title = isset($values['title']) ? $values['title'] : '';
        $status = isset($values['status']) ? $values['status'] : '';
        $type = isset($values['type']) ? $values['type'] : '';
        $id = isset($values['id']) ? $this->db->findid("hash = " . dbquote($values['id'])) : false;
        if (!$id) {
          $id = $this->add($title, $status, $type, $items);
        } else {
          if (!$this->edit($id, $title, $status, $type, $items)){
            $i = min($j, strlen($content));
            continue;
          }
        }
      }
      //common for both cases
      $item = $this->getitem($id);
      $stritems = implode("\n", $items);
    $replace = "[poll]\nid={$item['hash']}\n";
$replace .= "status={$item['status']}\ntype={$item['type']}\ntitle={$item['title']}\n";
      $replace .= "[items]\n$stritems\n[/items]\n[/poll]";
      
      $src = substr($content, $i, $j - $i);
      $content = substr_replace($content, $replace, $i, $j - $i);
      $post->rawcontent = str_replace($src, $replace, $post->rawcontent);
      $i = min($j, strlen($content));
    }
  }
  
  public function filter(&$content) {
    //replace poll templates to html
    $content = str_replace(array("\r\n", "\r"), "\n", $content);
    $i = 0;
    while (is_int($i = strpos($content, '[poll]', $i))) {
      $j = strpos($content, '[/poll]', $i);
      $j += strlen("[/poll]");
      $s = substr($content, $i, $j - $i);
      // extract items
      $k = strpos($s, '[items]');
      $l = strpos($s, '[/items]');
      $s = substr_replace($s, '', $k, $l - $k);
      $values = $this->extractvalues($s);
      $id = isset($values['id']) ? $this->db->findid("hash = " . dbquote($values['id'])) : false;
      if ($id) {
        $replace = $this->gethtml($id, false);
        $content = substr_replace($content, $replace, $i, $j - $i);
      }
      $i = min($j, strlen($content));
    }
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
  
}//class