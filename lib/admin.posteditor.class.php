<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tposteditor extends tadminmenu {
  public $idpost;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  private function getcategories(tpost $post) {
    global $options;
    $result = '';
    $categories = tcategories::instance();
    if (count($post->categories) == 0) $post->categories = array($categories->defaultid);
    $html = $this->html;
    $args = targs::instance();
    $categories->loadall();
    foreach ($categories->items  as $id => $item) {
      $args->add($item);
      $args->checked = in_array($item['id'], $post->categories);
      $result .= $html->category($args);
    }
    $result = sprintf($html->categories(), $result);
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function request($id) {
    global $options;
    if ($s = parent::request($id)) return $s;
    $this->basename = 'editor';
    $this->idpost = $this->idget();
    if ($this->idpost > 0) {
      $posts = tposts::instance();
      if (!$posts->itemexists($this->idpost)) return 404;
    }
    $post = tpost::instance($this->idpost);
    if (($options->group == 'author') && ($options->user != $post->author)) return 404;
  }
  
  public function gettitle() {
    if ($this->idpost == 0){
      return parent::gettitle();
    } else {
      return tlocal::$data[$this->name]['title'];
    }
  }
  
  private function getmode() {
    $mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : 'midle';
    if (!preg_match('/short|midle|full/', $mode)) $mode = 'midle';
    return $mode;
  }

public function shorteditor() {
$_REQUEST['mode'] = 'short';
    $this->basename = 'editor';
$this->idpost = 0;
return $this->getcontent();
}
  
  public function getcontent() {
    global $options, $post;
    $result = '';
    $html = $this->html;
    $post = tpost::instance($this->idpost);
    $mode = $this->getmode();
    $args = targs::instance();
    if ($post->id != 0) {
      $adminurl = $this->adminurl . "=$post->id&mode";
      $result .= sprintf($html->h2->formhead, "<a href='$post->link'>$post->title</a>",
      "$adminurl=short", "$adminurl=midle", "$adminurl=full");
    }
    $args->categories = $this->getcategories($post);
    $args->raw = $post->rawcontent;
    $args->commentsenabled = $post->commentsenabled;
    $args->pingenabled = $post->pingenabled;
    $args->published = $post->status != 'draft' ? 'selected' : '';
    $args->draft = $post->status == 'draft' ? 'selected' : '';
    
    if ($mode != 'short') {
      $args->date = $post->posted != 0 ?date('d-m-Y', $post->posted) : '';
      $args->time  = $post->posted != 0 ?date('H:i', $post->posted) : '';
      $args->content = $post->filtered;
      $args->excerpt = $post->excerpt;
      $args->rss = $post->rss;
    }
    
    $result .= $html->$mode($args);
    return $result;
  }
  
  public function processform() {
    global $options;
    $this->basename = 'editor';
    $html = $this->html;
    $cats = array();
    $cat = 'category-';
    foreach ($_POST as $key => $value) {
      if ($cat == substr($key, 0, strlen($cat))) {
        $id = (int) substr($key, strlen($cat));
        $cats[] = $id;
      }
    }
    
    extract($_POST);
    if (empty($title))return $html->h2->emptytitle;
    $post = tpost::instance((int)$id);
    $post->title = $title;
    $post->categories = $cats;
    $post->tagnames = $tags;
    switch ($this->getmode()) {
      case 'short':
      $post->content = $raw;
      break;
      
      case 'midle':
      if (($date != '')  && @sscanf($date, '%d-%d-%d', $d, $m, $y) && @sscanf($time, '%d:%d', $h, $min)) {
        $post->posted = mktime($h,$min,0, $m, $d, $y);
      }
      
      $post->content = $raw;
      $post->commentsenabled = isset($commentsenabled);
      $post->pingenabled = isset($pingenabled);
      $post->status = $status == 'draft' ? 'draft' : 'published';
      break;
      
      case 'full':
      if (($date != '')  && @sscanf($date, '%d-%d-%d', $d, $m, $y) && @sscanf($time, '%d:%d', $h, $min)) {
        $post->posted = mktime($h,$min,0, $m, $d, $y);
      }
      $post->commentsenabled = isset($commentsenabled);
      $post->pingenabled = isset($pingenabled);
      $post->status = $status == 'draft' ? 'draft' : 'published';
      $post->title2 = $title2;
      $post->url = $url;
      $post->description = $description;
      $post->rawcontent = $raw;
      $post->filtered = $content;
      $post->excerpt = $excerpt;
      $post->rss = $rss;
      $post->moretitle = $moretitle;
      break;
    }
    
    $posts = tposts::instance();
    if ($id == 0) {
      $_POST['id'] = $posts->add($post);
    } else {
      $posts->edit($post);
    }
    
    return sprintf($html->p->success,"<a href=\"$post->link\" title=\"$post->title\">$post->title</a>");
  }
  
}//class
?>