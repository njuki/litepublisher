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

       $id = $this->idget();
    $posts= tposts::instance();
    if (!$posts->itemexists($id)) return $this->notfound;
    $post = tpost::instance($id);

if (!$this->confirmed) {
$args = new targs()
$args->id = $id;
$args->adminurl = $options->url . $this->url . $options->q . 'id';
$args->action = $action;
$args->confirm = sprintf($this->lang->confirm, $this->lang->$action, "<a href='$post->link'>$post->title</a>");
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
$items = $this->db->idselect("status <> 'deleted' order by posted desc limit $from, $perpage");
} else {
$items = array_slice($this->items, $from, $perpage, true);
$items = array_reverse (array_keys($items));
}
$html = $this->html;
    $result .=sprintf($html->h2->count, $from, $from + count($items), $count);
    $result .= $html->listhead();
$args = new targs();
$args->adminurl = $options->url . $this->url . $options->q . 'id';
$args->editurl = $options->url . $this->url . 'editor/' . $options->q . 'id';
    foreach ($items  as $id ) {
      $post = tpost::instance($id);
      $args->status = $this->lang->{$post->status};
$result .= $html->itemlist($args);
    }
$result .= $html->listfooter();
    $result = str_replace("'", '"', $result);
    
    $tp = TTemplatePost::instance();
    $result .= $tp->PrintNaviPages('/admin/posts/', $urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
}//class
?>