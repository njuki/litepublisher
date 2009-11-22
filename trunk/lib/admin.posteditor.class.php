<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tposteditor extends tadminmenu {
  public $postid;
  
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
        foreach ($categories->getitems() as $item) {
$args->id = $item['id'];
      $args->checked = in_array($items['id'], $post->categories);
$args->url = $options->url . $item['url'];
$args->title = $item['title'];
      $result .= $html->category($args);
    }
$result = sprintf($html->categories(), $result);
    $result = str_replace("'", '"', $result);
    return $result;
  }

public function request($id) {
global $options;
if ($s = parent::request($id)) return $s;
    $this->postid = isset($_GET['postid']) ? (int) $_GET['postid'] : (isset($_POST['postid']) ? (int) $_POST['postid'] : 0);
$posts = tposts::instance();
if (!$posts->itemexists($this->postid)) return 404;
    $post = tpost::instance($this->postid);
if (($options->group == 'author') && ($options->user != $post->author)) return 404;
}
  
  public function getcontent() {
    global $options, $post;
    $result = '';
    $post = tpost::instance($this->postid);
$args = targs::instance();
    if ($post->id != 0) {
  $result .= $this->html->formhead("<a href='$options->url$post->url'>$post->title</a>", "$options->url/admin/posteditor/{$options->q}postid=$post->id", "$options->url/admin/posteditor/full/{$options->q}postid=$post->id");
    }
$args->categories = $this->getcategories($post);
    $args->raw = $post->rawcontent;
    $args->commentsenabled = $post->commentsenabled;
    $args->pingenabled = $post->pingenabled;
    $args->published = $post->status != 'draft' ? 'selected' : '';
    $args->draft = $post->status == 'draft' ? 'selected' : '';
    if (!isset($_GET['full'])) {
$result .= $this->html->form($args);
    } else {
      $args->date = $post->posted != 0 ?date('d-m-Y', $post->posted) : '';
      $args->time  = $post->posted != 0 ?date('H:i', $post->posted) : '';
      $args->content = $post->filtered;
      $args->excerpt = $post->excerpt;
      $args->rss = $post->rss;
      $result .= $this->html->fullform($args);
    }
    return $result;
  }
  
  public function processform() {
    global $options;
  
    $cats = array();
    $cat = 'category-';
    foreach ($_POST as $key => $value) {
      if ($cat == substr($key, 0, strlen($cat))) {
        $id = (int) substr($key, strlen($cat));
        $cats[] = $id;
      }
    }
    
    extract($_POST);
    if (empty($title))return $this->html->emptytitle;
    $post = tpost::instance($postid);
    $post->title = $title;
    $post->categories = $cats;
    $post->tagnames = $tags;
    $post->commentsenabled = isset($commentsenabled);
    $post->pingenabled = isset($pingenabled);
    $post->status = $status == 'draft' ? 'draft' : 'published';
    
    if (!isset($_GET['full'])) {
      $post->content = $raw;
    } else {
    $post->title2 = $title2;
      $post->url = $url;
      $post->description = $description;
      $post->rawcontent = $raw;
      $post->filtered = $content;
      $post->excerpt = $excerpt;
      $post->rss = $rss;
      $post->moretitle = $moretitle;
      if (($date != '')  && @sscanf($date, '%d-%d-%d', $d, $m, $y) && @sscanf($time, '%d:%d', $h, $min)) {
        $post->posted = mktime($h,$min,0, $m, $d, $y);
      }
    }
    
    $posts = tposts::instance();
    if ($postid == 0) {
      $posts->add($post);
    } else {
      $posts->edit($post);
    }
    
$s = $this->html->success;
    return sprintf($s,"<a href=\"$options->url$post->url\">$post->title</a>");
  }
  
}//class
?>