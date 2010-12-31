<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminfiles extends tadminmenu {
  
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
    $type = $this->name == 'files' ? '' : $this->name;
    if (dbversion) {
      $sql = 'parent =0';
      $sql .= litepublisher::$options->user <= 1 ? '' : ' and author = ' . litepublisher::$options->user;
      $sql .= $type == '' ? " and media<> 'icon'" : " and media = '$type'";
      $count = $files->db->getcount($sql);
    } else {
      $list= array();
      $user = litepublisher::$options->user;
      foreach ($files->items as $id => $item) {
        if ($item['parent'] != 0) continue;
        if ($user > 1 && $user != $item['author']) continue;
        if (($type != '') && ($item['media'] != $type)) continue;
        if (($type == '') && ($item['media'] == 'icon')) continue;
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
    //if ($type != 'icon') $result .= $files->getlist($list);
    $result .= $html->tableheader();
    $args = targs::instance();
    $args->adminurl = $this->adminurl;
    foreach ($list as $id) {
      $item = $files->items[$id];
      $args->add($item);
      $args->id = $id;
      if ($type == 'icon') $args->title = sprintf('<img src="%1$s/files/%2$s" title="%2$s" />', litepublisher::$site->files, $item['filename']);
      $result .= $html->tableitem ($args);
    }
    
    $result .= $html->tablefooter;
    
    $theme = ttheme::instance();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    $files = tfiles::instance();
    if (empty($_GET['action'])) {
      
      if ($_POST['uploadmode'] == 'upload') {
        if (isset($_FILES["filename"]["error"]) && $_FILES["filename"]["error"] > 0) {
          $error = tlocal::$data['uploaderrors'][$_FILES["filename"]["error"]];
          return "<h2>$error</h2>\n";
        }
        if (!is_uploaded_file($_FILES["filename"]["tmp_name"])) return sprintf($this->html->h2->attack, $_FILES["filename"]["name"]);
        
        $overwrite  = isset($_POST['overwrite']);
        $parser = tmediaparser::instance();
        $parser->uploadfile($_FILES["filename"]["name"], $_FILES["filename"]["tmp_name"], $_POST['title'], $_POST['description'], $_POST['keywords'], $overwrite);
      } else {
        //downloadurl
        $content = http::get($_POST['downloadurl']);
        if ($content == false) return $this->html->h2->errordownloadurl;
        $filename = basename(trim($_POST['downloadurl'], '/'));
        if ($filename == '') $filename = 'noname.txt';
        $overwrite  = isset($_POST['overwrite']);
        $parser = tmediaparser::instance();
        $parser->upload($filename, $content, $_POST['title'], $_POST['description'], $_POST['keywords'], $overwrite);
      }
      return $this->html->h2->success;
    } elseif ($_GET['action'] == 'edit') {
      $id = $this->idget();
      if (!$files->itemexists($id))  return $this->notfound;
      $files->edit($id, $_POST['title'], $_POST['description'], $_POST['keywords']);
      return $this->html->h2->edited;
    }
    
    return '';
  }
  
}//class
?>