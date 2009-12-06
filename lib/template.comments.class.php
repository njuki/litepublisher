<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class ttemplatecomments extends tdata {

  public static function instance() {
    return getinstance(__class__);
  }

public function load() {}
public function save() {}  

  public function getcount($count) {
$l = &tlocal::$data['comment'];
    switch($count) {
      case 0: return $l[0];
      case 1: return $l[1];
      default: return sprintf($l[2], $count);
    }
  }
  
  public function getcommentslink(tpost $post) {
    global $options;
    $count = $this->getcount($post->commentscount);
    $url = $post->haspages ? rtrim($post->url, '/') . "/page/$post->countpages/" : $post->url;
    return "<a href=\"$options->url$url#comments\">$count</a>";
  }
  
  public function getcomments($idpost) {
    global $options;
    $result = '';
$urlmap = turlmap::instance();
$idpost = (int) $idpost;
$post = tpost::instance($idpost);
//    if (($post->commentscount == 0) && !$post->commentsenabled) return '';
    if ($post->haspages && ($post->commentpages < $urlmap->page)) return $this->getcommentslink($post);

$args = targs::instance();
$theme = ttheme::instance();
$tml = $theme->content->post->templatecomments->comments;
    $lang = tlocal::instance('comment');
    $args->count = $this->getcount($post->commentscount);
    $comments = tcomments::instance($idpost);
$result .= $comments->getcontent();

    if (count($items)  > 0) {
$result .= $theme->parsearg($tml->count, $args);
      $result .= $this->getlist($items, $idpost, '', $from);
    }

    if ($urlmap->page == 1)  {
$items = $comments->getpingbacks();
$result .= $this->getpingbacks($comments, $items);
}

    if (!$options->commentsdisabled && $post->commentsenabled) {
      $result .=  "<?php  echo tcommentform::printform($idpost); ?>\n";
    } else {
$result .= $theme->parse($theme->content->post->templatecomments->closed);
    }
    return $result;
  }

private function getpingbacks(tcomments $comments, array &$items) {
global $comment;
if (count($items) == 0) return '';
    $result = '';
    $comment = new TComment($comments);
    $lang = tlocal::instance('comment');
$theme = ttheme::instance();
$tml = $theme->content->post->templatecomments->pingbacks->pingback;
    foreach  ($items as $value) {
//трюк: в бд items это комменты целиком, а в файлах только id
if (dbversion)  {
      $comment->data = $value;
} else {
      $comment->id = $value;
}
$result .= $theme->parse($tml);
    }
    
    return sprintf($theme->content->post->templatecomments->pingbacks, $result);
}

  private function getlist(array &$items, $idpost, $hold, $from) {
    global $comment, $post;
    $result = '';
$post = tpost::instance($idpost);
$args = targs::instance();
$args->hold = $hold;
$args->from = $from;
    $comments = tcomments::instance($idpost);
    $comment = new TComment($comments);
    $lang = tlocal::instance('comment');
$theme = ttheme::instance();
$tml = $theme->content->post->templatecomments->comments->comment;
    $i = 1;
    foreach  ($items as $value) {
//трюк: в бд items это комменты целиком, а в файлах только id
if (dbversion)  {
      $comment->data = $value;
} else {
      $comment->id = $value;
}
      $args->class = (++$i % 2) == 0 ? $tml->class1 : $tml->class2;
$result .= $theme->parsearg($tml, $args);
    }
    
    return sprintf($theme->content->post->templatecomments->comments, $result, $from + 1);
  }
  
  public function gethold(array &$items, $idpost) {
    if (count($items) == 0) return '';
$theme = ttheme::instance();
    $lang = tlocal::instance('comment');
$hold = $theme->parse($thme->comments['hold']);
    return $this->getlist($items, $idpost, $hold, 0);
  }
  
} //class
?>