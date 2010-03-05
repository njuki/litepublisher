<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmintickets extends tadminmenu {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function gethtml($name = '') {
    $tickets = ttickets::instance();
    $tickets->checkhtml();
    return parent::gethtml($name);
  }
  
  public function getcontent() {
    $result = '';
    $posts = tposts::instance();
    $perpage = 20;
    if (dbversion) {
      $where = litepublisher::$options->group == 'ticket' ? ' and author = ' . litepublisher::$options->user : '';
      $tickets = ttickets::instance();
      $count = $tickets->getcount($where);
      $from = $this->getfrom($perpage, $count);
      if ($count > 0) {
        $items = $posts->select("status <> 'deleted' $where", " order by posted desc limit $from, $perpage");
        if (!$items) $items = array();
      }  else {
        $items = array();
      }
    } else {
      if (litepublisher::$options->user == 1) {
        $count = $posts->count;
        $from = $this->getfrom($perpage, $count);
        $items = array_slice($posts->items, $from, $perpage, true);
        $items = array_reverse (array_keys($items));
      } else {
        $items = array();
        foreach ($posts->items as $item) {
          if (isset($item['author']) && ($item['author'] == litepublisher::$options->user)) $items[] = $id;
        }
        
        $count = count($items);
        $from = $this->getfrom($perpage, $count);
        $items = array_slice($items, $from, $perpage);
        $items = array_reverse ($items);
      }
    }
    
    $html = $this->html;
    $result .= $html->checkallscript;
    $result .=sprintf($html->h2->count, $from, $from + count($items), $count);
    $result .= $html->listhead();
    $args = targs::instance();
    $args->adminurl = $this->adminurl;
    $args->editurl = litepublisher::$options->url . $this->url . 'editor/' . litepublisher::$options->q . 'id';
    $lang = tlocal::instance('tickets');
    foreach ($items  as $id ) {
      $post = tpost::instance($id);
      $ticket = $post->ticket;
      ttheme::$vars['post'] = $post;
      ttheme::$vars['ticket'] = $ticket;
    $args->status = $lang->{$post->status};
      $args->type = tlocal::$data['ticket'][$ticket->type];
      $args->prio = tlocal::$data['ticket'][$ticket->prio];
      $args->state = tlocal::$data['ticket'][$ticket->state];
      $result .= $html->itemlist($args);
    }
    $result .= $html->footer();
    if (litepublisher::$options->group != 'ticket') {
      $result  = "<form name='form' action='' method='post'>" . $result;
      $result .= $html->listfooter();
    }
    $result = $html->fixquote($result);
    
    $theme = ttheme::instance();
    $result .= $theme->getpages('/admin/posts/', litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    if (litepublisher::$options->group == 'ticket') return '';
    $posts = tposts::instance();
    $posts->lock();
    $status = isset($_POST['publish']) ? 'published' : (isset($_POST['setdraft']) ? 'draft' : 'delete');
    foreach ($_POST as $key => $id) {
      if (!is_numeric($id))  continue;
      $id = (int) $id;
      if ($status == 'delete') {
        $posts->delete($id);
      } else {
        $post = tpost::instance($id);
        $post->status = $status;
        $posts->edit($post);
      }
    }
    $posts->unlock();
  }
  
}//class
?>