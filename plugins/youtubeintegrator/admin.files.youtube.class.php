<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminyoutubefiles extends tadminmenu {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $result = '';
    $files = tfiles::instance();
    $html = $this->html;
    if (!isset($_GET['action'])) {
      $args = targs::instance();
      $args->adminurl = $this->url;
      $result .= $html->uploadform($args);
    } else {
      $id = $this->idget();
      if (!$files->itemexists($id)) return $this->notfound;
      switch ($_GET['action']) {
        case 'delete':
        if ($this->confirmed) {
          $files->delete($id);
          $result .= $html->h2->deleted;
        } else {
          $item = $files->getitem($id);
          $args = targs::instance();
          $args->add($item);
          $args->id = $id;
          $args->adminurl = $this->adminurl;
          $args->action = 'delete';
          $args->confirm = sprintf($this->lang->confirm, $item['filename']);
          return $html->confirmform($args);
        }
        break;
        
        case 'edit':
        $args = targs::instance();
        $args->add($files->getitem($id));
        $result .= $html->editform($args);
        break;
      }
    }
    
    $perpage = 20;
    $type = 'youtube';
    if (dbversion) {
      $sql = 'parent =0';
      $sql .= litepublisher::$options->user <= 1 ? '' : " and author = litepublisher::$options->user";
      $sql .= " and media = '$type'";
      $count = $files->db->getcount($sql);
    } else {
      $list= array();
      foreach ($files->items as $id => $item) {
        if ($item['parent'] != 0) continue;
        if (litepublisher::$options->user > 1 && litepublisher::$options->user != $item['author']) continue;
        if ($item['media'] != $type) continue;
        $list[] = $id;
      }
      $count = count($list);
    }
    
    $from = $this->getfrom($perpage, $count);
    if (dbversion) {
      $list = $files->select($sql, " order by posted desc limit $from, $perpage");
      if (!$list) $list = array();
    } else {
      $list = array_slice($list, $from, $perpage);
    }
    
    $result .= sprintf($html->h2->countfiles, $count, $from, $from + count($list));
    $result .= $html->tableheader();
    $args = targs::instance();
    $args->adminurl = $this->adminurl;
    foreach ($list as $id) {
      $item = $files->items[$id];
      $args->add($item);
      $args->id = $id;
      if ($type == 'icon') $args->title = sprintf('<img src="%1$s/files/%2$s" title="%2$s" />', litepublisher::$options->files, $item['filename']);
      $result .= $html->tableitem ($args);
    }
    
    $result .= $html->tablefooter;
    
    $theme = ttheme::instance();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    $files = tfiles::instance();
      $id = $this->idget();
      if (!$files->itemexists($id))  return $this->notfound;
      $files->edit($id, $_POST['title'], $_POST['description'], $_POST['keywords']);
      return $this->html->h2->edited;
    }
    
    return '';
  }
  
}//class
?>