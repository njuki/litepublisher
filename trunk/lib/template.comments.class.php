<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttemplatecomments extends tdata {
  
  public static function i() {
    return getinstance(__class__);
  }
  
public function load() {}
public function save() {}
  
  public function getcomments($idpost) {
    $result = '';
    $urlmap = turlmap::i();
    $idpost = (int) $idpost;
    $post = tpost::i($idpost);
    
    //if ($post->haspages && ($post->commentpages < $urlmap->page)) return $post->getcommentslink();
    
    $lang = tlocal::i('comment');
    $comments = tcomments::i($idpost);
    $list = $comments->getcontent();
    
    $theme = $post->theme;
    $args = targs::i();
    $args->count = $post->cmtcount;
    $result .= $theme->parsearg($theme->templates['content.post.templatecomments.comments.count'], $args);
    $result .= $list;
    
    if (($urlmap->page == 1) && ($post->pingbackscount > 0))  {
      $pingbacks = tpingbacks::i($post->id);
      $result .= $pingbacks->getcontent();
    }
    
    if (!litepublisher::$options->commentsdisabled && $post->commentsenabled) {
      if (litepublisher::$options->autocmtform) {
        $result .=  "<?php  echo tcommentform::printform($idpost, '$theme->name'); ?>\n";
      } else {
        $lang = tlocal::i('comment');
        $args->name = '';
        $args->email = '';
        $args->url = '';
        $args->subscribe = litepublisher::$options->defaultsubscribe;
        $args->content = '';
        $args->postid = $idpost;
        $args->antispam = base64_encode('superspamer' . strtotime ("+1 hour"));
        
        $result .= $theme->parsearg($theme->templates['content.post.templatecomments.form'], $args);
      }
    } else {
      $result .= $theme->parse($theme->templates['content.post.templatecomments.closed']);
    }
    return $result;
  }
  
} //class