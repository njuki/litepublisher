<?php

class tadminposts extends tadminmenuitem {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    global $options;
    if (!isset($_GET['action'])) return $this->getlist();
       $id = (int) $_GET['postid'];
    $posts= tposts::instance();
    if (!$posts->itemexists($id)) return $this->notfound;
    $post = tpost::instance($id);
    
    $result ='';
if ($this->confirmed) {
      switch ($_GET['action']) {
        case 'delete' :
        $posts->delete($id);
        break;
        
        case 'setdraft':
        $post->status = 'draft';
        $posts->edit($post);
        break;
        
        case 'publish':
        $post->status = 'published';
        $posts->edit($post);
        break;
      }

$s = $this->html->h2->confirmed;
      $result .= sprintf($s, tlocal::$data['poststatus'][$_GET['action']], "<a href='$options->url$post->url'>$post->title</a>");
    } else {

    $confirm = sprintf($lang->confirm, $lang->{$_GET['action']}, "<a href='$options->url$post->url'>$post->title</a>");
      eval('$result .= "'. $html->confirmform . '\n";');
    }
    return $result;
  }
  
  private function getlist() {
    global $options, $urlmap;
    $result = '';
    
    $posts = tposts::instance();
    $from = max(0, count($posts->items) - $urlmap->pagenumber * 100);
    $items = array_slice($posts->items, $from, 100, true);
    eval('$s = "'. $html->count . '\n";');
    $result .=sprintf($s, $from, $from + count($items), count($posts->items));
    eval('$result .= "' . $html->listhead . '\n";');
    $list = '';
    foreach ($items  as $id => $item) {
      $post = &TPost::instance($id);
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