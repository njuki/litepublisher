<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttemplatecomments extends tevents {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'comments.templates';
}
  
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
    
    if (!litepublisher::$options->commentsdisabled && ($post->comments_status != 'closed')) {
$result .= '<?php if (litepublisher::$options->ingroup(\'author\')) { ?>';
$result .= $this->reg;
      $regform = $theme->parse($theme->templates['content.post.templatecomments.regform']);
      $result .= $regform;
$result .= '<?php } else { ?>';

switch ($post->comments_status) {
case 'reg':
$result .= $this->noreg;
break;

case 'guest':
$result .= $this->guest;
      $result .= $regform;
break;

case 'comuser':
$result .= $this->comuser;
        $result .=  tcommentform::i()->getform($post, $theme);
break;
}

$result .= '<?php } ?>';
    } else {
      $result .= $theme->parse($theme->templates['content.post.templatecomments.closed']);
    }
    return $result;
  }
  
} //class