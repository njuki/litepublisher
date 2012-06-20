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
  
  public function gethead() {
    $result = parent::gethead();
    $template = ttemplate::i();
  $result .= $template->getready('$("#tabs, #contenttabs").tabs({ cache: true });');
    return $result;
  }
  
  public function gettitle() {
    if ($this->idpost == 0){
      return parent::gettitle();
    } else {
      return tlocal::admin('forums')->editor;
    }
  }
  
  public function camrequest() {
    if ($s = parent::canrequest()) return $s;
    $this->basename = 'forums';
    if ($this->idpost > 0) {
      $forum = tforum::i($this->idpost);
      if ((litepublisher::$options->group == 'forum') && (litepublisher::$options->user != $forum->author)) return 403;
    }
  }
  
  public function gethtml($name = '') {
    $lang = tlocal::admin('forums');
    $lang->ini['forums'] = $lang->ini['forum'] + $lang->ini['forums'];
    return parent::gethtml($name);
  }
  
  protected function getlogoutlink() {
    return $this->gethtml('login')->logout();
  }
  
  public function getcontent() {
    $result = $this->logoutlink;
    $this->basename = 'forums';
    $forum = tforum::i($this->idpost);
    ttheme::$vars['forum'] = $forum;
    $args = targs::i();
    $args->id = $this->idpost;
    $args->title = tcontentfilter::unescape($forum->title);
    $args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$forum->id&get");
    $ajaxeditor = tajaxposteditor ::i();
    $args->raw = $ajaxeditor->geteditor('raw', $forum->rawcontent, true);
    
    $html = $this->html;
    $lang = tlocal::admin('forums');
    
    $args->code = $html->getinput('editor', 'code', tadminhtml::specchars($forum->code), $lang->codetext);
    
    $args->fixed = $forum->state == 'fixed';
    
    $forums = tforums::i();
    $args->catcombo = tposteditor::getcombocategories($forums->cats, count($forum->categories) ? $forum->categories[0] : $forums->cats[0]);
    
    $states =array();
    foreach (array('fixed', 'opened', 'wontfix', 'invalid', 'duplicate', 'reassign') as $state) {
      $states[$state] = $lang->$state;
    }
    $args->statecombo= $html->array2combo($states, $forum->state);
    
    $prio = array();
    foreach (array('trivial', 'minor', 'major', 'critical', 'blocker') as $p) {
      $prio[$p] = $lang->$p;
    }
    $args->priocombo = $html->array2combo($prio, $forum->prio);
    
    if ($forum->id > 0) $result .= $html->headeditor ();
    $result .= $html->form($args);
    $result = $html->fixquote($result);
    return $result;
  }
  
  public function processform() {
    /* dumpvar($_POST);
    return;
    */
    extract($_POST, EXTR_SKIP);
    $forums = tforums::i();
    $this->basename = 'forums';
    $html = $this->html;
    
    // check spam
    if ($id == 0) {
      $newstatus = 'published';
      if (litepublisher::$options->group == 'forum') {
        $hold = $forums->db->getcount('status = \'draft\' and author = '. litepublisher::$options->user);
        $approved = $forums->db->getcount('status = \'published\' and author = '. litepublisher::$options->user);
        if ($approved < 3) {
          if ($hold - $approved >= 2) return $html->h4->noapproved;
          $newstatus = 'draft';
        }
      }
    }
    if (empty($title)) {
      $lang =tlocal::i('editor');
      return $html->h4->emptytitle;
    }
    $forum = tforum::i((int)$id);
    $forum->title = $title;
    $forum->categories = array((int) $combocat);
    if (isset($tags)) $forum->tagnames = $tags;
    if ($forum->author == 0) $forum->author = litepublisher::$options->user;
    if (isset($files))  {
      $files = trim($files);
      $forum->files = $files == '' ? array() : explode(',', $files);
    }
    
    $forum->content = tcontentfilter::quote(htmlspecialchars($raw));
    $forum->code = $code;
    $forum->prio = $prio;
    $forum->set_state($state);
    $forum->version = $version;
    $forum->os = $os;
    //if (litepublisher::$options->group != 'forum') $forum->state = $state;
    if ($id == 0) {
      $forum->status = $newstatus;
      $forum->categories = array((int) $combocat);
      $forum->closed = time();
      $id = $forums->add($forum);
      $_GET['id'] = $id;
      $_POST['id'] = $id;
      $this->idpost = $id;
    } else {
      $forums->edit($forum);
    }
    
    return $html->h4->successedit;
  }
  
}//class