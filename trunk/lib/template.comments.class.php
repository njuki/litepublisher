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
    $count = $this->getcount($post->commentscount);
    return "<a href=\"" . litepublisher::$options->url . "$post->lastcommenturl#comments\">$count</a>";
  }
  
  public function getcomments($idpost) {
    $result = '';
    $urlmap = turlmap::instance();
    $idpost = (int) $idpost;
    $post = tpost::instance($idpost);
    //    if (($post->commentscount == 0) && !$post->commentsenabled) return '';
    if ($post->haspages && ($post->commentpages < $urlmap->page)) return $this->getcommentslink($post);
    $lang = tlocal::instance('comment');    
    $comments = tcomments::instance($idpost);
    $list = $comments->getcontent();
    
    $theme = ttheme::instance();
    $tml = $theme->content->post->templatecomments->comments;
    $args = targs::instance();
    $args->count = $this->getcount($post->commentscount);
    $result .= $tml->count($args);
    $result .= $list;
    
    if (($urlmap->page == 1) && ($post->pingbackscount > 0))  {
      $pingbacks = tpingbacks::instance($post->id);
      $result .= $pingbacks->getcontent();
    }
    
    if (!litepublisher::$options->commentsdisabled && $post->commentsenabled) {
      $result .=  "<?php  echo tcommentform::printform($idpost); ?>\n";
    } else {
      $result .= $theme->parse($theme->content->post->templatecomments->closed);
    }
    return $result;
  }
  
} //class
?>