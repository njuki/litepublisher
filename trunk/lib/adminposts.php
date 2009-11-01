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
    global $options, $urlmap;
    $result = '';
    
    $posts = tposts::instance();
$count = 20;
    $from = max(0, $posts->count - $urlmap->page * $count);
    $items = $posts->getrange($from, $count);
    eval('$s = "'. $html->count . '\n";');
    $result .=sprintf($s, $from, $from + count($items), count($posts->items));
    eval('$result .= "' . $html->listhead . '\n";');
    $list = '';
    foreach ($items  as $id => $item) {
      $post = tpost::instance($id);
      $status = TLocal::$data['poststatus'][$post->status];
      eval('$list="\n' . $html->itemlist . '" . $list;');
    }
    $result .= $list;
    eval('$result .= "'. $html->listfooter . '\n";');;
    $result = str_replace("'", '"', $result);
    
    $TemplatePost = TTemplatePost::instance();
    $result .= $TemplatePost ->PrintNaviPages('/admin/posts/', $urlmap->pagenumber, ceil(count($posts->items)/100));
    return $result;
  }
  
}//class
?>