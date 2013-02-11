<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tfiles extends titems {
  public $itemsposts;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->basename = 'files';
    $this->table = 'files';
    $this->addevents('changed', 'edited', 'ongetfilelist', 'onlist');
    $this->itemsposts = tfileitems ::i();
    $this->data['videoplayer'] = '/js/litepublisher/icons/videoplayer.jpg';
  }
  
  public function preload(array $items) {
    $items = array_diff($items, array_keys($this->items));
    if (count($items) > 0) {
      $this->select(sprintf('(id in (%1$s)) or (parent in (%1$s))',
      implode(',', $items)), '');
    }
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
    return sprintf('<a href="%1$s/files/%2$s" title="%3$s">%4$s</a>', litepublisher::$site->files,
    $item['filename'], $item['title'], $icon . $item['description']);
  }
  
  public function geticon($id) {
    return sprintf('<img src="%s" alt="icon" />', $this->geturl($id));
  }
  
  public function gethash($filename) {
    return trim(base64_encode(md5_file($filename, true)), '=');
  }
  
  public function additem(array $item) {
    $realfile = litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
    $item['author'] = litepublisher::$options->user;
    $item['posted'] = sqldate();
    $item['hash'] = $this->gethash($realfile);
    $item['size'] = filesize($realfile);
    
    //fix empty props
    foreach (array('mime', 'title', 'description', 'keywords') as $prop) {
      if (!isset($item[$prop])) $item[$prop] = '';
    }
    return $this->insert($item);
  }
  
  public function insert(array $item) {
    $item = $this->escape($item);
    $id = $this->db->add($item);
    $this->items[$id] = $item;
    $this->changed();
    $this->added($id);
    return $id;
  }
  
  public function escape(array $item) {
    foreach (array('title', 'description', 'keywords') as $name) {
      $item[$name] = tcontentfilter::escape(tcontentfilter::unescape($item[$name]));
    }
    return $item;
  }
  
  public function edit($id, $title, $description, $keywords) {
    $item = $this->getitem($id);
    if (($item['title'] == $title) && ($item['description'] == $description) && ($item['keywords'] == $keywords)) return false;
    
    $item['title'] = $title;
    $item['description'] = $description;
    $item['keywords'] = $keywords;
    $item = $this->escape($item);
    $this->items[$id] = $item;
    $this->db->updateassoc($item);
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
    if ($item['idperm'] == 0) {
      @unlink(litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']));
    } else {
      @unlink(litepublisher::$paths->files . 'private' . DIRECTORY_SEPARATOR . basename($item['filename']));
      litepublisher::$urlmap->delete('/files/' . $item['filename']);
    }
    
    parent::delete($id);
    if ($item['preview'] > 0) $this->delete($item['preview']);
    
    $this->getdb('imghashes')->delete("id = $id");
    $this->changed();
    return true;
  }
  
  public function setcontent($id, $content) {
    if (!$this->itemexists($id)) return false;
    $item = $this->getitem($id);
    $realfile = litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
    if (file_put_contents($realfile, $content)) {
      $item['hash'] = $this->gethash($realfile);
      $item['size'] = filesize($realfile);
      $this->items[$id] = $item;
      if ($this->dbversion) {
        $item['id'] = $id;
        $this->db->updateassoc($item);
      } else {
        $this->save();
      }
    }
  }
  
  public function exists($filename) {
    return $this->IndexOf('filename', $filename);
  }
  
  public function getfilelist(array $list, $excerpt) {
    if ($result = $this->ongetfilelist($list, $excerpt)) return $result;
    $theme = ttheme::i();
    return $this->getlist($list, $excerpt ?
    $theme->gettag('content.excerpts.excerpt.filelist') :
    $theme->gettag('content.post.filelist'));
  }
  
  public function getlist(array $list,  $templates) {
    if (count($list) == 0) return '';
    $this->onlist($list);
    $result = '';
    $this->preload($list);
    
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
      if (isset($types[$type])) {
        $items[$type][] = $id;
      } elseif (isset($templates->$type)) {
        $items[$type][] = $id;
        $types[$type] = $templates->$type;
        $type .= 's';
        $types[$type] = $templates->$type;
      } else {
        $items['file'][] = $id;
      }
    }
    $theme = ttheme::i();
    $args = new targs();
    $url = litepublisher::$site->files . '/files/';
    $preview = new tarray2prop();
    ttheme::$vars['preview'] = $preview;
    $index = 0;
    foreach ($items as $type => $subitems) {
      $sublist = '';
      foreach ($subitems as $typeindex => $id) {
        $item = $this->items[$id];
        $args->add($item);
        $args->link = $url . $item['filename'];
        $args->id = $id;
        $args->typeindex = $typeindex;
        $args->index = $index++;
                $args->preview  = '';
                                $preview->array = array(); 
                                
        if ($item['preview'] > 0) {
          $preview->array = $this->getitem($item['preview']);
        } elseif($type == 'image') {
          $preview->array = $item;
          $preview->id = $id;
                } elseif($type == 'video') {
            $preview->link = litepublisher::$site->url . $this->videoplayer;
            $args->preview = $theme->parsearg($types['preview'], $args);
                }
        
if (count($preview->array)) {
            $preview->link = $url . $preview->filename;
            $args->preview = $theme->parsearg($types['preview'], $args);
            }

 unset($item['title'], $item['keywords'], $item['description']);
 $args->json = str_replace('"', '&quot;', json_encode($item));       
        $sublist .= $theme->parsearg($types[$type], $args);
      }
      $sublist = str_replace('$' . $type, $sublist, $types[$type . 's']);
      $result .= $sublist;
    }
    
    unset(ttheme::$vars['preview'], $preview);
    return str_replace('$files', $result, $theme->parse((string) $templates));
  }
  
  public function postedited($idpost) {
    $post = tpost::i($idpost);
    $this->itemsposts->setitems($idpost, $post->files);
  }
  
}//class

class tfileitems extends titemsposts {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->basename = 'fileitems';
    $this->table = 'filesitemsposts';
  }
  
}