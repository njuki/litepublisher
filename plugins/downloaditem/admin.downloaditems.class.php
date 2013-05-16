<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmindownloaditems extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethtml($name = '') {
    $lang = tlocal::admin();
    $lang->ini['downloaditems'] = $lang->ini['downloaditem'] + $lang->ini['downloaditems'];
    return parent::gethtml($name);
  }
  
  protected function getlogoutlink() {
    return $this->gethtml('login')->logout();
  }
  
  public function getcontent() {
    $result = '';
    //$result = $this->logoutlink;
    $html = $this->html;
    $args = targs::i();
    $args->adminurl = $this->adminurl;
    $args->editurl = tadminhtml::getadminlink('/admin/downloaditems/editor/', 'id');
    $lang = tlocal::admin('downloaditems');
    
    $downloaditems = tdownloaditems::i();
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

    $result .=$html->getitemscount($from, $from + count($items), $count);    
    ttheme::$vars['dlitem_status'] = new dlitem_status();
    $result .= $html->tableposts($items, array(
    array('right', $lang->downloads, '$post.downloads'),
    array('left', $lang->posttitle, '$post.bookmark'),
    array('left', $lang->status, '$ticket_status.status'),
    array('left', $lang->tags, '$post.tagnames'),
    array('center', $lang->edit, '<a href="' . tadminhtml::getadminlink('/admin/downloaditems/editor/', 'id') . '=$post.id">' . $lang->edit . '</a>'),
    ));
    
    unset(ttheme::$vars['dlitem_status']);

    $result .= $html->footer();    $result = $html->fixquote($result);
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    $downloaditems = tdownloaditems::i();
    if ($this->name == 'addurl') {
      $url = trim($_POST['url']);
      if ($url == '') return '';
      if ($downloaditem = taboutparser::parse($url)) {
        $id = $downloaditems->add($downloaditem);
        litepublisher::$urlmap->redir(tadminhtml::getadminlink('/admin/downloaditems/editor/', "id=$id"));
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
        $downloaditem = tdownloaditem::i($id);
        $downloaditem->status = $status;
        $downloaditems->edit($downloaditem);
      }
    }
  }
  
}//class

class dlitem_status {
  
  public function __get($name) {
    $dl = ttheme::$vars['post'];
    $lang = tlocal::i();
    switch ($name) {
      case 'status':
    return $lang->{$dl->status};
      
      case 'type':
    return tlocal::get('downloaditem', $dl->type);
    }
    return '';
  }
  
}//class