<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
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
    $this->addevents('changed', 'edited', 'ongetfilelist');
    $this->itemsposts = tfileitems ::instance();
  }
  
  public function geturl($id) {
    $item = $this->getitem($id);
    return litepublisher::$site->files . '/files/' . $item['filename'];
  }
  
  public function getlink($id) {
    $item = $this->getitem($id);
    $icon = '';
    if (($item['icon'] != 0) && ($item['media'] != 'icon')) {
      $icon = $this->geticon($item['icon']);
    }
    return sprintf('<a href="%1$s" title="%2$s">%3$s</a>', litepublisher::$site->files. $item['filename'], $item['title'], $icon . $item['description']);
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
    return $this->insert($item);
  }
  
  public function insert(array $item) {
    if (dbversion) {
      $id = $this->db->add($item);
    } else {
      $id = ++$this->autoid;
    }
    $this->items[$id] = $item;
    if (!$this->dbversion) $this->save();
    $this->changed();
    $this->added($id);
    return $id;
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
  
  public function exists($filename) {
    return $this->IndexOf('filename', $filename);
  }
  
  public function getfilelist(array $list, $excerpt) {
    if ($result = $this->ongetfilelist($list, $excerpt)) return $result;
    $theme = ttheme::instance();
    return $this->getlist($list, $excerpt ?
    $theme->gettag('content.excerpts.excerpt.filelist') :
    $theme->gettag('content.post.filelist'));
  }
  
  public function getlist(array $list,  $templates) {
    if (count($list) == 0) return '';
    $result = '';
    if ($this->dbversion) {
      $this->loaditems($list);
      $this->select(sprintf('parent in (%s)', implode(',', $list)), '');
    }
    
    //sort by media type
    $items = array();
    $types = array(
    'file' => $templates->file,
    'files' => $templates->files,
    'preview' => $templates->preview
    );

    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;
      $item = $this->items[$id];
      $type = $item['media'];
      $items[$type][] = $id;
      if (!isset($types[$type])) {
if (isset($templates->$type)) {
$types[$type] = $templates->$type;
$type .= 's';
$types[$type] = $templates->$type;
} else {
$types[$type] = $type['file'];
$types[$type . 's'] = $type['files'];
}
}
    }
    
    $theme = ttheme::instance();
    $args = targs::instance();
    $url = litepublisher::$site->files . '/files/';
    $preview = new tarray2prop();
    ttheme::$vars['preview'] = $preview;
    foreach ($items as $type => $subitems) {
$sublist = '';
      foreach ($subitems as $id) {
        $item = $this->items[$id];
        $args->preview  = '';
        $args->add($item);
        $args->link = $url . $item['filename'];
        $args->id = $id;
        if ($item['preview'] > 0) {
          $preview->array = $this->getitem($item['preview']);
          if ($preview->media === 'image') {
            $preview->id = $item['preview'];
            $preview->link = $url . $preview->filename;
            $args->preview = $theme->parsearg($types['preview'], $args);
          } elseif($type == 'image') {
            $preview->array = $item;
            $preview->id = $id;
            $preview->link = $url . $preview->filename;
            $args->preview = $theme->parsearg($types['preview'], $args);
          }
        }
        
        $sublist .= $theme->parsearg($types[$type], $args);
      }
$sublist = str_replace('$' . $type, $sublist, $types[$type . 's']);
$result .= $sublist;
    }
    
    unset(ttheme::$vars['preview'], $preview);
    return str_replace('$files', $result, $theme->parse((string) $templates));
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