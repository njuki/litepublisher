<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tforumeditor extends tposteditor {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gettitle() {
    if ($this->idpost == 0){
      return parent::gettitle();
    } else {
      return tlocal::admin('posts')->editor;
    }
  }
  
  protected function getlogoutlink() {
    return $this->gethtml('login')->logout();
  }
  
  public function getcontent() {
    $result = $this->logoutlink;
    $this->basename = 'forum';
$posts = tposts::i();
    $html = $this->html;
    $lang = tlocal::admin('forum');
   
    if ($this->idpost == 0) {
$forum = tforum::i();
if ($forum->moderate && !litepublisher::$options->ingroup('editor')) {
// if too many drafts then reject
        $hold = $posts->db->getcount('status = \'draft\' and author = '. litepublisher::$options->user);
if ($hold >= 3) return $html->manydrafts;
}
}

    $post = tpost::i($this->idpost);
    ttheme::$vars['post'] = $post;
    $args = new targs();
    $args->id = $this->idpost;
    $args->title = tcontentfilter::unescape($post->title);
    $args->raw = $post->rawcontent;
    
    $args->catcombo = tposteditor::getcombocategories($posts->cats, count($post->categories) ? $post->categories[0] : $posts->cats[0]);
    
    if ($post->id > 0) $result .= $html->headeditor ();
    $result .= $html->editor($args);
    $result = $html->fixquote($result);
    return $result;
  }
  
  public function processform() {
    //  return dumpvar($_POST);
    extract($_POST, EXTR_SKIP);
    $posts = tposts::i();
    $this->basename = 'posts';
    $html = $this->html;
    
    if ($id == 0) {
$forum = tforum::i();
if (!$forum->moderate || litepublisher::$options->ingroup('editor')) {
$status = 'published';
} else {
$status = 'draft';
// if too many drafts then reject
        $hold = $posts->db->getcount('status = \'draft\' and author = '. litepublisher::$options->user);
if ($hold >= 3) return $html->manydrafts;
    }

    if (empty($title)) {
      $lang =tlocal::i('editor');
      return $html->h4->emptytitle;
    }
}

    $post = tpost::i((int)$id);
    $post->title = $title;
    $post->categories = array((int) $combocat);

    if ($post->author == 0) $post->author = litepublisher::$options->user;

    if (isset($files))  {
      $files = trim($files);
      $post->files = $files == '' ? array() : explode(',', $files);
    }
    
    $post->content = tcontentfilter::remove_scripts($raw);

    if ($id == 0) {
      $post->status = $newstatus;
$post->comstatus = $forum->comstatus;
$post->idview = $forum->idview;
$post->idperm = $forum->idperm;

    $post->url = tlinkgenerator::i()->addurl($post, 'forum');

      $id = $posts->add($post);
      $_GET['id'] = $id;
      $_POST['id'] = $id;
      $this->idpost = $id;
    } else {
      $posts->edit($post);
    }
    
    return $html->h4->successedit;
  }
  
}//class