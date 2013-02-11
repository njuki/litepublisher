<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminfiles extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $result = '';
    $files = tfiles::i();
    $html = $this->html;
    $lang = $this->lang;
                $args = new targs();
    if (!isset($_GET['action'])) {
      $args->adminurl = $this->url;
      $args->perm = litepublisher::$options->show_file_perm ?  tadminperms::getcombo(0, 'idperm') : '';
      $args->add(array(
      'title' => '',
      'description' => '',
      'keywords' => ''
      ));
      $result .= $html->uploadform($args);
    } else {
      $id = $this->idget();
      if (!$files->itemexists($id)) return $this->notfound;
      switch ($_GET['action']) {
        case 'delete':
        if ($this->confirmed) {
          if (('author' == litepublisher::$options->group) && ($r = tauthor_rights::i()->candeletefile($id))) return $r;
          $files->delete($id);
          $result .= $html->h2->deleted;
        } else {
          $item = $files->getitem($id);
          $args->add($item);
          $args->id = $id;
          $args->adminurl = $this->adminurl;
          $args->action = 'delete';
          $args->confirm = sprintf($this->lang->confirm, $item['filename']);
          return $html->confirmform($args);
        }
        break;
        
        case 'edit':
        $item = $files->getitem($id);
        $args->add($item);
        $args->title = tcontentfilter::unescape($item['title']);
        $args->description = tcontentfilter::unescape($item['description']);
        $args->keywords = tcontentfilter::unescape($item['keywords']);
        $args->formtitle = $this->lang->editfile;
        $result .= $html->adminform('[text=title] [text=description] [text=keywords]' .
        (litepublisher::$options->show_file_perm ?  tadminperms::getcombo($item['idperm'], 'idperm') : ''),
        $args);
        break;
      }
    }
    
    $perpage = 20;
    $type = $this->name == 'files' ? '' : $this->name;
      $sql = 'parent =0';
      $sql .= litepublisher::$options->user <= 1 ? '' : ' and author = ' . litepublisher::$options->user;
      $sql .= $type == '' ? " and media<> 'icon'" : " and media = '$type'";
      $count = $files->db->getcount($sql);
    $from = $this->getfrom($perpage, $count);
    $list = $files->select($sql, " order by posted desc limit $from, $perpage");
    if (!$list) $list = array();
    $result .= sprintf($html->h4->countfiles, $count, $from, $from + count($list));
    
    $args->adminurl = $this->adminurl;
        $result .= $html->buildtable($files->items, array(
            array('right', 'ID', '$id'),
                        array('right', $lang->filename, '<a href="$site.files/files/$filename">$filename</a>'),
                array('left', $lang->title, $type != 'icon' ? '$title' :
'<img src="$site.files/files/$filename" alt="$filename" />'),
    array('center', $lang->edit, "<a href=\"$this->adminurl=\$id&action=edit\">$lang->edit</a>"),
        array('center', $lang->thumbnail, '<a href="' . tadminhtml::getadminlink('/admin/files/thumbnail/', 'id='). "\$id\">$lang->thumbnail</a>"),
    array('center', $lang->delete, "<a href=\"$this->adminurl=\$id&action=delete\">$lang->delete</a>")
));

    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    $files = tfiles::i();
    if (empty($_GET['action'])) {
      $isauthor = 'author' == litepublisher::$options->group;
      if ($_POST['uploadmode'] == 'upload') {
        if (isset($_FILES['filename']['error']) && $_FILES['filename']['error'] > 0) {
          $error = tlocal::get('uploaderrors', $_FILES["filename"]["error"]);
          return "<h2>$error</h2>\n";
        }
        if (!is_uploaded_file($_FILES['filename']['tmp_name'])) return sprintf($this->html->h2->attack, $_FILES["filename"]["name"]);
        if ($isauthor && ($r = tauthor_rights::i()->canupload())) return $r;
        $overwrite  = isset($_POST['overwrite']);
        $parser = tmediaparser::i();
        $id = $parser->uploadfile($_FILES['filename']['name'], $_FILES['filename']['tmp_name'], $_POST['title'], $_POST['description'], $_POST['keywords'], $overwrite);
      } else {
        //downloadurl
        $content = http::get($_POST['downloadurl']);
        if ($content == false) return $this->html->h2->errordownloadurl;
        $filename = basename(trim($_POST['downloadurl'], '/'));
        if ($filename == '') $filename = 'noname.txt';
        if ($isauthor && ($r = tauthor_rights::i()->canupload())) return $r;
        $overwrite  = isset($_POST['overwrite']);
        $parser = tmediaparser::i();
        $id = $parser->upload($filename, $content, $_POST['title'], $_POST['description'], $_POST['keywords'], $overwrite);
      }
      
      if (isset($_POST['idperm'])) tprivatefiles::i()->setperm($id, (int) $_POST['idperm']);
      return $this->html->h4->success;
    } elseif ($_GET['action'] == 'edit') {
      $id = $this->idget();
      if (!$files->itemexists($id))  return $this->notfound;
      $files->edit($id, $_POST['title'], $_POST['description'], $_POST['keywords']);
      if (isset($_POST['idperm'])) tprivatefiles::i()->setperm($id, (int) $_POST['idperm']);
      return $this->html->h4->edited;
    }
    
    return '';
  }
  
}//class