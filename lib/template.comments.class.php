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
    
    if (!litepublisher::$options->commentsdisabled && ($post->comstatus != 'closed')) {
      $args->postid = $post->id;
      $args->antispam = base64_encode('superspamer' . strtotime ("+1 hour"));
      
      $cm = tcommentmanager::i();
      $result .=  sprintf('<?php if (litepublisher::$options->ingroups(array(%s))) {', implode(',', $cm->idgroups));
        //add hold list
        $result .= 'if ($ismoder = litepublisher::$options->ingroup(\'moderator\')) { ?>';
          $args->comment = '';
          $result .= $theme->parsearg($theme->templates['content.post.templatecomments.holdcomments'], $args);
          $result .= $this->loadhold;
        $result .= '<?php } ?>';
        
        $args->mesg = $this->logged;
        $result .= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
        $template = ttemplate::i();
        $result .= sprintf('<script type="text/javascript">
        ltoptions.theme.comments = $.extend(true, ltoptions.theme.comments, %s);
        ltoptions.theme.comments.ismoder = <?php echo ($ismoder ? \'true\' : \'false\'); ?>;
        </script>', json_encode(array(
        'canedit' => $cm->canedit,
        'candelete' => $cm->candelete,
'confirmcomment' => ($post->idperm == 0) && $cm->confirmlogged,
'comuser' => false
        )));

        $result .= $template->getjavascript($template->jsmerger_comments);
        $result .= $template->getjavascript($template->jsmerger_moderate);
        $result .= $template->getjavascript('/js/litepublisher/moderate.js');
      $result .= '<?php } else { ?>';
        
        switch ($post->comstatus) {
          case 'reg':
          $mesg = $this->reqlogin;
          if (litepublisher::$options->reguser) $mesg .= $this->regaccount;
          $args->mesg = $this->fixmesg($mesg, $theme);
          $result .= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
          break;
          
          case 'guest':
$result .= $this->getjs(($post->idperm == 0) && $cm->confirmguest, false);
          $mesg = $this->guest;
          if (litepublisher::$options->reguser) $mesg .= $this->regaccount;
          $args->mesg = $this->fixmesg($mesg, $theme);
          $result .= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
          break;
          
          case 'comuser':
$result .= $this->getjs(($post->idperm == 0) && $cm->confirmcomuser, true);
          $mesg = $this->comuser;
          if (litepublisher::$options->reguser) $mesg .= $this->regaccount;
          $args->mesg = $this->fixmesg($mesg, $theme);
          
          foreach (array('name', 'email', 'url') as $field) {
            $args->$field = "<?php echo (isset(\$_COOKIE['comuser_$field']) ? \$_COOKIE['comuser_$field'] : ''); ?>";
          }
          
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
  
  public function fixmesg($mesg, $theme) {
    return $theme->parse(str_replace('backurl=', 'backurl=' . urlencode(litepublisher::$urlmap->url),
    str_replace('&backurl=', '&amp;backurl=', $mesg)));
  }

public function getjs($confirmcomment, $comuser) {
        $result = sprintf('<script type="text/javascript">ltoptions.theme.comments = $.extend(true, ltoptions.theme.comments, %s);</script>',
 json_encode(array(
'confirmcomment' => $confirmcomment,
'comuser' => $comuser
        )));

$template = ttemplate::I();
        $result .= $template->getjavascript($template->jsmerger_comments);
        //$result .= $template->getjavascript('/js/litepublisher/confirmcomment.js');
return  $result;
}
  
} //class