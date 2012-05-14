<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class Tadmincommentmanager extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    return parent::gethead() . tuitabs::gethead();
  }
  
  public function getcontent() {
    $result = '';
    $cm = tcommentmanager::i();
    $options = litepublisher::$options;
    $html = $this->gethtml('commentmanager');
    $lang = tlocal::admin('commentmanager');
    $args = new targs();
    $tabs = new tuitabs();
    $args->comstatus = tadminhtml::array2combo(array(
    'closed' => $lang->closed,
    'reg' => $lang->reg,
    'guest' => $lang->guest,
    'comuser' => $lang->comuser
    ), $options->comstatus);
    
    $args->filtercommentstatus = $options->filtercommentstatus;
    $args->commentsapproved = $options->commentsapproved;
    $args->checkduplicate = $options->checkduplicate;
    $args->commentsdisabled  = $options->commentsdisabled;
    $args->pingenabled  = $options->pingenabled;
    
    $tabs->add($lang->options, '
    [combo=comstatus]
    [checkbox=filtercommentstatus]
    [checkbox=commentsapproved]
    [checkbox=checkduplicate]
    [checkbox=commentsdisabled]
    [checkbox=pingenabled]
    ');
    
    $args->commentpages  = $options->commentpages;
    $args->commentsperpage  = $options->commentsperpage;
    $args->comments_invert_order  = $options->comments_invert_order;
    $args->hidelink =  $cm->hidelink;
    $args->redir = $cm->redir;
    $args->nofollow = $cm->nofollow;
    
    $tabs->add($lang->templates, '
    [checkbox=commentpages]
    [text=commentsperpage]
    [checkbox=comments_invert_order]
    [checkbox=hidelink]
    [checkbox=redir]
    [checkbox=nofollow]
    ');
    
    $args->canedit = $cm->canedit;
    $args->candelete = $cm->candelete;
    $args->confirmlogged = $cm->confirmlogged;
    $args->confirmguest = $cm->confirmguest ;
    $args->confirmcomuser = $cm->confirmcomuser;
    $args->confirmemail = $cm->confirmemail;
    
    $tabs->add($lang->perms, '
    [checkbox=canedit]
    [checkbox=candelete]
    [checkbox=confirmlogged]
    [checkbox=confirmguest]
    [checkbox=confirmcomuser]
    [checkbox=confirmemail]
    ');
    
    $args->sendnotification = $cm->sendnotification;
    $args->defaultsubscribe = $options->defaultsubscribe;
    $subscribe = tsubscribers::i();
    $args->locklist = $subscribe->locklist;
    $args->subscribe_enabled = $subscribe->enabled;
    
    $tabs->add($lang->subscribe, '
    [checkbox=sendnotification]
    [checkbox=defaultsubscribe]
    [checkbox=subscribe_enabled]
    [editor=locklist]
    ');
    
    $mesgtabs = new tuitabs();
    $tc = ttemplatecomments::i();
    foreach (array('logged', 'reqlogin', 'regaccount', 'guest', 'comuser', 'loadhold') as $name) {
      $args->$name = $tc->$name;
      $mesgtabs->add($lang->$name, "[editor=$name]");
    }
    $tabs->add($lang->mesgtabs, $mesgtabs->get());
    $args->formtitle = $lang->title;
    return $html->adminform($tabs->get(), $args);
  }
  
  public function processform() {
    extract($_POST, EXTR_SKIP);
    $options = litepublisher::$options;
    $cm = tcommentmanager::i();
    $cm->lock();
    
    $options->comstatus = $comstatus;
    $options->filtercommentstatus =isset($filtercommentstatus);
    $options->commentsapproved = isset($commentsapproved);
    $options->checkduplicate = isset($checkduplicate);
    $options->defaultsubscribe = isset($defaultsubscribe);
useroptions = tuseroptions::i();
$useroptions->defvalues['subscribe'] = $options->defaultsubscribe;
$useroptions->save();

    $options->commentsdisabled  = isset($commentsdisabled);
    $options->pingenabled  = isset($pingenabled);
    $options->commentpages  = isset($commentpages);
    $options->commentsperpage  = (int) trim($commentsperpage);
    $options->comments_invert_order  = isset($comments_invert_order);
    
    $cm->sendnotification = isset($sendnotification);
    $cm->hidelink =  isset($hidelink);
    $cm->redir = isset($redir);
    $cm->nofollow = isset($nofollow);
    
    $cm->unlock();
    
    $tc = ttemplatecomments::i();
    foreach (array('logged', 'reqlogin', 'regaccount', 'guest', 'comuser', 'loadhold') as $name) {
      $tc->$name = $_POST[$name];
    }
    $tc->save();
    
    $subscr = tsubscribers::i();
    $subscr->lock();
    $subscr->locklist = $locklist;
    $subscr->enabled = isset($subscribe_enabled);
    $subscr->unlock();
    
  }
}//class