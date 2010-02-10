<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tfiles extends titems {
  public $itemsposts;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    
    $this->itemsposts = new titemsposts();
    $this->basename = 'files';
    $this->table = 'files';
    $this->addevents('changed', 'edited');
  }
  
  public function load() {
    if(!$this->dbversion) parent::load();
  }
  
  public function save() {
    if (!$this->dbversion) parent::save();
  }
  
  public function geturl($id) {
    $item = $this->getitem($id);
    return litepublisher::$options->files . '/files/' . $item['filename'];
  }
  
  public function getlink($id) {
    $item = $this->getitem($id);
    $icon = '';
    if (($item['icon'] != 0) && ($item['media'] != 'icon')) {
      $icon = $this->geticon($item['icon']);
    }
    return sprintf('<a href="%1$s" title="%2$s">%3$s</a>', litepublisher::$options->files. $item['filename'], $item['title'], $icon . $item['description']);
  }
  
  public function geticon($id) {
    return sprintf('<img src="%s" />', $this->geturl($id));
  }
  
  public function additem(array $item) {
    $realfile = litepublisher::$paths['files'] . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
    $item['author'] = litepublisher::$options->user;
    $item['posted'] = sqldate();
    $item['keywords'] = '';
    $item['md5'] = md5_file($realfile);
    $item['size'] = filesize($realfile);
    
    if (dbversion) {
      $id = $this->db->add($item);
      $this->items[$id] = $item;
      return $id;
    } else {
      $this->items[++$this->autoid] = $item;
      $this->save();
      $this->changed();
      $this->added($this->autoid);
      return $this->autoid;
    }
  }
  
  public function delete($id) {
    if (!$this->itemexists($id)) return false;
    $list = $this->itemsposts->getposts($id);
    $this->itemsposts->deleteitem($id);
    $this->itemsposts->updateposts($list, 'files');
    $item = $this->getitem($id);
    @unlink(litepublisher::$paths['files']. str_replace('/', DIRECTORY_SEPARATOR, $item['filename']));
    $this->lock();
    parent::delete($id);
    if ($item['preview'] > 0) $this->delete($item['preview']);
    $this->unlock();
    $this->changed();
    return true;
  }
  
  public function getlist(array $list) {
    if (count($list) == 0) return '';
    $result = '';
    if ($this->dbversion) {
      $this->loaditems($list);
      $this->select(sprintf('parent in (%s)', implode(',', $list)));
    }
    
    $theme = ttheme::instance();
    $tml = $theme->content->post->files;
    $args = targs::instance();
    $img = '<img src="litepublisher::$options.files/files/$filename" title="$filename" />';
    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;
      $item = $this->items[$id];
      $args->add($item);
      $type = $item['media'];
      $itemtml = empty($tml->array[$type]) ? $tml->file : $tml->array[$type];
      if ($item['preview'] == 0) {
        $args->preview = '';
      } elseif (!isset($this->items[$id])) {
        $args->preview = '';
      } else {
        $preview = $this->items[$item['preview']];
        $imgarg = new targs();
        $imgarg->add($preview);
        $args->preview =$theme->parsearg($img, $imgarg);
      }
      $result .= $theme->parsearg($itemtml , $args);
    }
    return sprintf($tml, $result);
  }
  
}//class

?>