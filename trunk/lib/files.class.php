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
    $this->addevents('Changed', 'Edited');
  }
  
  public function load() {
    if(!dbversion) parent::load();
  }
  
  public function save() {
    if (!dbversion) parent::save();
  }
  
  public function geturl($id) {
    global $options;
    $item = $this->getitem($id);
    return $options->files . '/files/' . $item['filename'];
  }
  
  public function getlink($id) {
    global $options;
    $item = $this->getitem($id);
    $icon = '';
    if (($item['icon'] != 0) && ($item['media'] != 'icon')) {
      $icon = $this->geticon($item['icon']);
    }
    return sprintf('<a href="%1$s" title="%2$s">%3$s</a>', $options->files. $item['filename'], $item['title'], $icon . $item['description']);
  }
  
  public function geticon($id, $title) {
    return sprintf('<img src="%s" />', $this->geturl($id));
  }
  
  public function additem(array $item) {
    global $options, $paths;
    $realfile = $paths['files'] . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
    $item = $item + array(
    'author' => $options->user,
    'posted' => sqldate(),
    'keywords' => '',
    'md5' => md5_file($realfile),
    'size' => filesize($realfile)
    );
    
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
    global $paths;
    if (!$this->itemexists($id)) return false;
    $list = $this->itemsposts->getposts($id);
    $this->itemsposts->deleteitem($id);
    $this->itemsposts->updateposts($list, 'files');
    $item = $this->getitem($id);
    @unlink($paths['files']. str_replace('/', DIRECTORY_SEPARATOR, $item['filename']));
    $this->lock();
    parent::delete($id);
    if ($item['preview'] > 0) $this->delete($item['preview']);
    $this->unlock();
    $this->changed();
    return true;
  }
  
  public function getitems(array $list) {
    if (dbversion) {
      return $this->db->getlist($list);
    } else {
      $result = array();
      foreach ($list as $id) {
        $item = $this->items[$id];
        $item['id'] = $id;
        $result[] = $item;
      }
      return $result;
    }
  }
  
  private function getpreviewitems(array $list) {
    if (dbversion) {
      $res = $this->db->select(sprintf('parent in (%s)', implode(',', $list)));
      return $res->fetchAll(PDO::FETCH_ASSOC);
    } else {
      $result = array();
      foreach ($list as $id) {
        $item = $this->items[$id];
        if($item['preview'] == 0) continue;
        $id = $item['preview'];
        $item = $this->items[$id];
        $item['id'] = $id;
        $result[] = $item;
      }
      return $result;
    }
  }
  
  public function getpreviews(array $list) {
    $items = $this->getpreviewitems($list);
    if (count($items) == 0) return '';
    $result = '';
    $theme = ttheme::instance();
    $tml = $theme->content->excerpts->excerpt->previews->preview;
    $args = targs::instance();
    foreach ($items as $item) {
      $args->add($item);
      $result .= $theme->parsearg($tml, $args);
    }
    return sprintf($theme->content->excerpts->excerpt->previews, $result);
  }
  
  public function getlist(array $list, $screenshots) {
    $items = $this->getitems($list);
    if (count($items) == 0) return '';
    $result = '';
    $theme = ttheme::instance();
    $tml = $themes->content->post->files;
    $args = targs::instance();
    foreach ($items as $item) {
      $args->add($item);
      $tmlitem= empty($tml->array[$item['media']]) ? $tml->file : $tml->array[$item['media']];$theme->files['file'];
      $result .= $theme->parsearg($tmlitem, $args);
    }
    return sprintf($tml, $result);
  }
  
}//class

?>