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
    $args->postid = $post->id;
    $args->antispam = base64_encode('superspamer' . strtotime ("+1 hour"));

$result .=  sprintf('<?php if (litepublisher::$options->ingroups(array(%s))) { ?>', implode(',', tcommentmanager::i()->idgroups));
$args->mesg = $this->logged;
      $result .= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
$result .= '<?php } else { ?>';

switch ($post->comments_status) {
case 'reg':
$mesg = $this->reqlogin;
if (litepublisher::$options->reguser) $mesg .= $this->regaccount;
$args->mesg = $this->fixmesg($mesg);
      $result .= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
break;

case 'guest':
$mesg = $this->guest;
if (litepublisher::$options->reguser) $mesg .= $this->regaccount;
$args->mesg = $this->fixmesg($mesg);
      $result .= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
break;

case 'comuser':
$mesg = $this->comuser;
if (litepublisher::$options->reguser) $mesg .= $this->regaccount;
$args->mesg = $this->fixmesg($mesg);

    $args->name = '';
    $args->email = '';
    $args->url = '';
    $args->subscribe = litepublisher::$options->defaultsubscribe;
    $args->content = '';
 
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
return str_replace('backurl=', 'backurl=' . urlencode(litepublisher::$urlmap->url), 
str_replace('&backurl=', '&amp;backurl=', $mesg));
}
  
} //class