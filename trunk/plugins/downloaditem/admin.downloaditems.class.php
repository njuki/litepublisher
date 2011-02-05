<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmindownloaditems extends tadminmenu {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethtml($name = '') {
    $html = tadminhtml::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    $html->addini('downloaditems', $dir . 'html.ini');
    tlocal::loadsection('', 'downloaditem', $dir);
    tlocal::loadsection('admin', 'downloaditems', $dir);
    tlocal::$data['downloaditems'] = tlocal::$data['downloaditem'] + tlocal::$data['downloaditems'];
    return parent::gethtml($name);
  }
  
  protected function getlogoutlink() {
    return $this->gethtml('login')->logout();
  }
  
  public function getcontent() {
$result = '';
    //$result = $this->logoutlink;
    $html = $this->html;
    $args = targs::instance();
    $args->adminurl = $this->adminurl;
    $args->editurl = tadminhtml::getadminlink('/admin/downloaditems/editor/', 'id');
    $lang = tlocal::instance('downloaditems');

    $downloaditems = tdownloaditems::instance();
    $perpage = 20;
    $where = litepublisher::$options->group == 'downloaditem' ? ' and author = ' . litepublisher::$options->user : '';
    
    switch ($this->name) {
case 'addurl':
$args->formtitle = $lang->addurl;
$args->url = tadminhtml::getparam('url', '');
return $html->adminform('[text=url]', $args);

      case 'theme':
      $where .= " and type = 'theme' ";
      break;
      
      case 'plugin':
      $where .= " and type = 'plugin' ";
      break;
    }
    
    $count = $downloaditems->getchildscount($where);
    $from = $this->getfrom($perpage, $count);
    if ($count > 0) {
      $items = $downloaditems->select("status <> 'deleted' $where", " order by posted desc limit $from, $perpage");
      if (!$items) $items = array();
    }  else {
      $items = array();
    }
    
$tablebody = '';
    foreach ($items  as $id ) {
      $downloaditem = tdownloaditem::instance($id);
      ttheme::$vars['downloaditem'] = $downloaditem;
    $args->status = $lang->{$downloaditem->status};
      $args->type = tlocal::$data['downloaditem'][$downloaditem->type];
      $tablebody .= $html->itemlist($args);
    }

    $result .=sprintf($html->h2->count, $from, $from + count($items), $count);
$result .= $html->gettable($html->listhead(), $tablebody);
    $result .= $html->footer();
    $result = $html->fixquote($result);
    
    $theme = ttheme::instance();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    $downloaditems = tdownloaditems::instance();
if ($this->name == 'addurl') {
$url = trim($_POST['url']);
if ($url == '') return '';
if ($downloaditem = taboutparser::parse($url)) {
$id = $downloaditems->add($downloaditem);
turlmap::redir(tadminhtml::getadminlink('/admin/downloaditems/editor/', "id=$id"));
}
return '';
}

    $status = isset($_POST['publish']) ? 'published' :
    (isset($_POST['setdraft']) ? 'draft' :'delete');

    foreach ($_POST as $key => $id) {
      if (!is_numeric($id))  continue;
      $id = (int) $id;
      if ($status == 'delete') {
        $downloaditems->delete($id);
      } else {
        $downloaditem = tdownloaditem::instance($id);
          $downloaditem->status = $status;
        $downloaditems->edit($downloaditem);
}
      }
  }
  
}//class