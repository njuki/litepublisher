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
$html = $this->gethtml('commentmanager');
$lang = tlocal::admin('commentmanager');
    $args = new targs();
    $tabs = new tuitabs();
$cm = tcommentmanager::i();
    $options = litepublisher::$options;

$args->comments_status = tadminhtml::array2combo(array(
'closed' => $lang->closed,
'reg' => $lang->reg,
'guest' => $lang->guest,
'comuser' => $lang->comuser
), $options->comments_status);

$args->filtercommentstatus = $options->filtercommentstatus;
$args->commentsapproved = $options->commentsapproved;
$args->checkduplicate = $options->checkduplicate;
$args->defaultsubscribe = $options->defaultsubscribe;
$args->commentsdisabled  = $options->commentsdisabled;
$args->autocmtform  = $options->autocmtform;
$args->pingenabled  = $options->pingenabled;
$args->commentpages  = $options->commentpages;
$args->commentsperpage  = $options->commentsperpage;
$args->comments_invert_order  = $options->comments_invert_order;

$args->sendnotification = $cm->sendnotification;
$args->hidelink =  $cm->hidelink;
$args->redir = $cm->redir;
$args->nofollow = $cm->nofollow;
$args->canedit = $cm->canedit;
$args->candelete = $cm->candelete;
$args->reqireconfirm = $cm->reqireconfirm;

$tabs->add($lang->options, 
'[combo=comments_status]
[checkbox=filtercommentstatus]
[checkbox=commentsapproved]
[checkbox=checkduplicate]
[checkbox=defaultsubscribe]
[checkbox=commentsdisabled]
[checkbox=autocmtform]
[checkbox=pingenabled]
[checkbox=commentpages]
[text=commentsperpage]
[checkbox=comments_invert_order]
[checkbox=sendnotification]
[checkbox=hidelink]
[checkbox=redir]
[checkbox=nofollow]
[checkbox=canedit]
[checkbox=candelete]
[checkbox=reqireconfirm]
');



$args->locklist = tsubscribers::i()->locklist;
      $tabs->add('E-Mail', '[editor=locklist]');

$args->formtitle = $lang->title;
return $html->adminform($tabs->get(), $args);
}
    
public function processform() {
        extract($_POST, EXTR_SKIP);
$options = litepublisher::$options;
$cm = tcommentmanager:i();
$cm->lock();

$options->comments_status = $comments_status;
$options->filtercommentstatus =isset($filtercommentstatus);
$options->commentsapproved = isset($commentsapproved);
$options->checkduplicate = isset($checkduplicate);
$options->defaultsubscribe = isset($defaultsubscribe);
$options->commentsdisabled  = isset($commentsdisabled);
$options->autocmtform  = isset($autocmtform);
$options->pingenabled  = isset($pingenabled);
$options->commentpages  = isset($commentpages);
$options->commentsperpage  = (int) trim($commentsperpage);
$options->comments_invert_order  = isset($comments_invert_order);

$cm->sendnotification = isset($sendnotification);
$cm->hidelink =  isset($hidelink);
$cm->redir = isset($redir);
$cm->nofollow = isset($nofollow);

$cm->unlock();

tsubscribers::i()->locklist = $locklist;
}
}//class