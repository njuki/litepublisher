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
$link = '<?php echo litepublisher::$site->getuserlink(); ?>';
$mesg = sprintf($this->logged, $link);
$mesg .= ' <a href="$site.url/admin/logout/">$lang.logout</a> ';
$args->mesg = $this->fixmesg($mesg);
      $result .= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
$result .= '<?php } else { ?>';

switch ($post->comments_status) {
case 'reg':
$this->noreg;
$args->mesg = $this->fixmesg($mesg);
      $result .= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
break;

case 'guest':
$args->mesg = $this->fixmesg($msg);
      $result .= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
break;

case 'comuser':
$args->mesg = $this->fixmesg($mesg);
      $result .= $theme->parsearg($theme->templates['content.post.templatecomments.form'], $args);
break;
}

$result .= '<?php } ?>';
    } else {
      $result .= $theme->parse($theme->templates['content.post.templatecomments.closed']);
    }
    return $result;
  }

public function fixmesg($mesg) {
return str_replace(array('&backurl=', '&amp;backurl='), 
'&amp;backurl=' . urlencode(litepublisher::$urlmap->url));
}
  
} //class