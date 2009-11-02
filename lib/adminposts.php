<?php

class tadminposts extends tadminmenuitem {
  
  public static function instance() {
    return getinstance(__class__);
  }

 public function getcontent() {
if (isset($_GET['action']) && in_array($_GET['action'], 'array('delete', 'setdraft', 'publish')) {
$action = $_GET['action'];
} else {
return $this->getlist();
}

       $id = (int) $_GET['postid'];
    $posts= tposts::instance();
    if (!$posts->itemexists($id)) return $this->notfound;
    $post = tpost::instance($id);

if (!$this->confirmed) {
$args = new targs()
$args->id = $id;
$args->action = $action;
$lang = $this->lang;
$args->confirm = sprintf($lang->confirm, $lang->$action, "<a href='$post->link'>$post->title</a>");
return $this->html->confirmform($args);
}

$h2 = $this->html->h2; 
      switch ($_GET['action']) {
        case 'delete' :
        $posts->delete($id);
return $h2->confirmeddelete;
        
        case 'setdraft':
        $post->status = 'draft';
        $posts->edit($post);
return $h2->confirmedsetdraft;
        
        case 'publish':
        $post->status = 'published';
        $posts->edit($post);
return $h2->confirmedpublish;
      }

  }
  
  private function getlist() {
    global $options, $urlmap, $post;
    $result = '';
        $posts = tposts::instance();
$perpage = 20;
$count = $posts->count;
    $from = max(0, $count - $urlmap->page * $perpage);

if (dbversion) {
$items = $this->db->idselect("not status = 'deleted' order by posted desc limit $from, $perpage");
} else {
$items = array_slice(array_keys($this->items), $from, $perpage);
}

    $result .=sprintf($this->html->h2->count, $from, $from + count($items), $count);
    $result .= $this->html->listhead();
    $list = '';
$args = new targs();
    foreach ($items  as $id ) {
      $post = tpost::instance($id);
      $args->status = tlocal::$data['poststatus'][$post->status];
$list.= $this->html->itemlist($args);
    }
    $result .= $list;
$result .= $this->html->listfooter();
    $result = str_replace("'", '"', $result);
    
    $TemplatePost = TTemplatePost::instance();
    $result .= $TemplatePost ->PrintNaviPages('/admin/posts/', $urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
}//class
?>