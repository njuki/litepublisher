<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminfiles extends tadminmenu {
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    global $options, $urlmap;
    $result = '';
    $files = tfiles::instance();
    $html = $this->html;
    if (!isset($_GET['action'])) {
      $result .= $html->uploadform();
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
    if (dbversion) {
      $sql = $options->user <= 1 ? '' : "author = $options->user";
      $count = $files->db->getcount($sql);
    } elseif ($options->user == 0)  {
      $count = $files->count;
    } else {
      $list= array();
      foreach ($files->items as $id => $item) {
        if ($options->user == $item['author']) $list[] = $id;
      }
      $count = count($list);
    }
    
    $from = max(0, $count - $urlmap->page * $perpage);
    
    if (dbversion) {
      $items = $files->db->getitems($sql);
    } else {
      $list = array_slice($list, $from, $perpage);
      //$items = array_reverse (array_keys($items));
      $items = $files->getitems($list);
    }
    
    $result .= sprintf($html->h2->countfiles, $count, $from, $from + count($items));
    $result .= $html->tableheader();
    $args = targs::instance();
    $args->adminurl = $this->adminurl;
    foreach ($items as $item) {
      $args->add($item);
      $result .= $html->tableitem ($args);
    }
    
    $result .= $html->tablefooter;
    return str_replace("'", '"', $result);
  }
  
  public function processform() {
    global $options, $paths;
    $files = tfiles::instance();
    if (empty($_GET['action'])) {
      if (!is_uploaded_file($_FILES["filename"]["tmp_name"])) return sprintf($this->html->h2->attack, $_FILES["filename"]["name"]);
      
      $overwrite  = isset($_POST['overwrite']);
      $parser = tmediaparser::instance();
      $parser->uploadfile($_FILES["filename"]["name"], $_FILES["filename"]["tmp_name"], $_POST['title'], $overwrite);
      return $this->html->h2->success;
    } elseif ($_GET['action'] == 'edit') {
      $id = $this->idget();
      if (!$files->itemexists($id))  return $this->notfound;
      $files->edit($id, $_POST['title']);
      return $this->html->h2->edited;
    }
    
    return '';
  }
  
}//class
?>