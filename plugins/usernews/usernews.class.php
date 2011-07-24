<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tusernews extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function create() {
    parent::create();
    $this->data['_changeposts'] = false;
    $this->data['_canupload'] = true;
    $this->data['_candeletefile'] = true;
    $this->data['autosubscribe'] = true;
$this->data['sourcetml'] = '<h4><a href="%1$s">%1$s</a></h4>';
  }
  
  public function getnorights() {
    $about = tplugins::getabout(tplugins::getname(__file__));
    return sprintf('<h4>%s</h4>', $about['norights']);
  }
  
  public function changeposts($action) {
    if (!$this->_changeposts) return $this->norights;
  }
  
  public function canupload() {
    if (!$this->_canupload) return $this->norights;
  }
  
  public function candeletefile() {
    if (!$this->_candeletefile) return $this->norights;
  }
  
  public function getposteditor($post, $args) {
    $args->sourceurl = isset($post->meta->sourceurl) ? $post->meta->sourceurl : '';
    $form = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'editor.htm');
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args->data['$lang.sourceurl'] = $about['sourceurl'];
    $ajaxeditor = tajaxposteditor ::instance();
    $args->raw = $ajaxeditor->geteditor('raw', $post->rawcontent, true);
    $html = tadminhtml::instance();
    $result = $post->id == 0 ? '' : $html->h2->formhead . $post->bookmark;
    $result .= $html->parsearg($form, $args);
    unset(ttheme::$vars['post']);
    return $html->fixquote($result);
  }
  
  public function editpost(tpost $post) {
    extract($_POST, EXTR_SKIP);
    $posts = tposts::instance();
    $html = tadminhtml::instance();
    // check spam
    if ($id == 0) {
      $status = 'published';
      $hold = $posts->db->getcount('status = \'draft\' and author = '. litepublisher::$options->user);
      $approved = $posts->db->getcount('status = \'published\' and author = '. litepublisher::$options->user);
      if ($approved < 3) {
        if ($hold - $approved >= 2) return $this->norights;
        $status = 'draft';
      }
    }
    
    $post->meta->sourceurl = $sourceurl;
    $post->title = $title;
    $post->categories = tposteditor::processcategories();
    $post->author = litepublisher::$options->user;
    if (isset($files))  {
      $files = trim($files);
      $post->files = $files == '' ? array() : explode(',', $files);
    }
    
    $post->content = $raw;
$post->filtered = sprintf($this->sourcetml,     $post->meta->sourceurl);
    if ($id == 0) {
      $post->status = $status;
      $id = $posts->add($post);
      $_GET['id'] = $id;
      $_POST['id'] = $id;

      if ($this->autosubscribe) {
        $pages = tuserpages::instance();
        $uitem = $pages->getitem(litepublisher::$options->user);
        $comusers = tcomusers::instance();
        $uid = $comusers->add($uitem['name'], $uitem['email'], $uitem['website'], '');
        $comusers->setvalue($uid, 'cookie', tusers::instance()->getvalue($uitem['id'], 'cookie'));
        $subscribers = tsubscribers::instance();
        $subscribers->add($id, $uid);
      }
    } else {
      $posts->edit($post);
    }
    
    return $html->h4->successedit;
  }
  
}//class