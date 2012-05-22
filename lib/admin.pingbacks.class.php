<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpingbacks extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
   $result = '';
        $pingbacks = tpingbacks::i();
    $lang = $this->lang;
    $html = $this->html;
    
      if ($action = $this->action) {
        $id = $this->idget();
        if (!$pingbacks->itemexists($id)) return $this->notfound;
        switch($action) {
          case 'delete':
          if(!$this->confirmed) return $this->html->confirmdelete($id, $this->adminurl, $lang->confirmdelete );
          $pingbacks->delete($id);
          $result .= $html->h4->successmoderated;
          break;
          
          case 'hold':
          $pingbacks->setstatus($id, false);
          $result .= $html->h2->successmoderated;
          break;
          
          case 'approve':
          $pingbacks->setstatus($id, true);
          $result .= $html->h2->successmoderated;
          break;
          
          case 'edit':
          $result .= $this->editpingback($id);
          break;
        }
      }
      $result .= $this->getpingbackslist();
      return $result;
}

  private function getpingbackslist() {
    $result = '';
    $pingbacks = tpingbacks::i();
    $perpage = 20;
    $total = $pingbacks->getcount();
    $from = $this->getfrom($perpage, $total);
    $items = $pingbacks->db->getitems("status <> 'deleted' order by posted desc limit $from, $perpage");
    $html = $this->html;
    $result .= sprintf($html->h2->pingbackhead, $from, $from + count($items), $total);
    $result .= $html->pingbackheader();
    $args = targs::i();
    $args->adminurl = $this->adminurl;
    foreach ($items as $item) {
      $args->add($item);
      $args->idpost = $item['post'];
      unset($args->data['$post']);
      $args->website = sprintf('<a href="%1$s">%1$s</a>', $item['url']);
      $args->localstatus = tlocal::get('commentstatus', $item['status']);
      $args->date = tlocal::date(strtotime($item['posted']));
      $post = tpost::i($item['post']);
      ttheme::$vars['post'] = $post;
      $args->posttitle =$post->title;
      $args->postlink = $post->link;
      $result .=$html->pingbackitem($args);
    }
    $result .= $html->tablefooter();
    $result = $html->fixquote($result);
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($total/$perpage));
    return $result;
  }
  
  private function editpingback($id) {
    $pingbacks = tpingbacks::i();
    $args = targs::i();
    $args->add($pingbacks->getitem($id));
    return $this->html->pingbackedit($args);
  }

  public function processform() {
      $pingbacks = tpingbacks::i();
      if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
        extract($_POST, EXTR_SKIP);
        $pingbacks->edit($this->idget(), $title, $url);
      } else {
        $status = isset($_POST['approve']) ? 'approve' : (isset($_POST['hold']) ? 'hold' : 'delete');
        foreach ($_POST as $id => $value) {
          if (!is_numeric($id))  continue;
          $id = (int) $id;
          if ($status == 'delete') {
            $pingbacks->delete($id);
          } else {
            $pingbacks->setstatus($id, $status == 'approve');
          }
        }
      }

return $this->html->h4->successmoderated;
}

}//class