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
    $this->basename = 'files';
    $this->table = 'files';
    $this->addevents('changed', 'edited');
    $this->itemsposts = tfileitems ::instance();
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
    return sprintf('<img src="%s" alt="icon" />', $this->geturl($id));
  }
  
  public function additem(array $item) {
    $realfile = litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
    $item['author'] = litepublisher::$options->user;
    $item['posted'] = sqldate();
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
  
  public function edit($id, $title, $description, $keywords) {
    $item = $this->getitem($id);
    if (($item['title'] == $title) && ($item['description'] == $description) && ($item['keywords'] == $keywords)) return false;
    
    $item['title'] = $title;
    $item['description'] = $description;
    $item['keywords'] = $keywords;
    $this->items[$id] = $item;
    if ($this->dbversion) {
      $this->db->updateassoc($item);
    } else {
      $this->save();
    }
    $this->changed();
    $this->edited($id);
    return true;
  }
  
  public function delete($id) {
    if (!$this->itemexists($id)) return false;
    $list = $this->itemsposts->getposts($id);
    $this->itemsposts->deleteitem($id);
    $this->itemsposts->updateposts($list, 'files');
    $item = $this->getitem($id);
    @unlink(litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']));
    $this->lock();
    parent::delete($id);
    if ($item['preview'] > 0) $this->delete($item['preview']);
    $this->unlock();
    $this->changed();
    return true;
  }
  
  public function getlist(array $list, array $templates) {
    if (count($list) == 0) return '';
    $result = '';
    if ($this->dbversion) {
      $this->loaditems($list);
      $this->select(sprintf('parent in (%s)', implode(',', $list)), '');
    }
    
    //sort by media type
    $items = array();
    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;
      $item = $this->items[$id];
      $items[$item['media']][] = $id;
    }

$theme = ttheme::instance();    
    $args = targs::instance();
    $preview = new tarray2prop();
    ttheme::$vars['preview'] = $preview;
    foreach ($items as $type => $subitems) {
      foreach ($subitems as $id) {
        $item = $this->items[$id];
        $args->preview  = '';
        $args->add($item);
        $args->id = $id;
        if ($item['preview'] > 0) {
          $preview->array = $this->getitem($item['preview']);
          if ($preview->media === 'image') {
            $preview->id = $item['preview'];
            $args->preview = $theme->parsearg($templates['preview'], $args);
          } elseif($type == 'image') {
            $preview->array = $item;
            $preview->id = $id;
            $args->preview = $theme->parsearg($templates['preview'], $args);
          }
        }
        
        $tml = empty($templates[$type]) ? $templates['file'] : $templates[$type];
        $result .= $theme->parsearg($tml, $args);
      }
    }
    
    unset(ttheme::$vars['preview'], $preview);
    return str_replace('$items', $result, $theme->parse($templates[0]));
  }
  
  public function postedited($idpost) {
    $post = tpost::instance($idpost);
    $this->itemsposts->setitems($idpost, $post->files);
  }
  
}//class

class tfileitems extends titemsposts {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->basename = 'fileitems';
    $this->table = 'filesitemsposts';
  }
  
}

?>