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
$html = $this->html;
    $args = new targs();
    $tabs = new tuitabs();
$cm = tcommentmanager::i();
    $options = litepublisher::$options;
$lang = tlocal::admin();
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
');

      $form->obj = litepublisher::$classes->commentmanager;
      $form->add($form->sendnotification, $form->hidelink,  $form->redir, $form->nofollow);
      $form->addeditor(tsubscribers::i(), 'locklist');

return $html->adminform($tabs->get(), $args);
}
    
public function processform() {
$options->filtercommentstatus = isset($filtercommentstatus);
$options->commentsapproved = isset($commentsapproved);
$options->checkduplicate = isset($checkduplicate);
$options->defaultsubscribe = isset($defaultsubscribe);
$options->commentsdisabled  = isset($commentsdisabled);
$options->autocmtform  = isset($autocmtform);
$options->pingenabled  = isset($pingenabled);
$options->commentpages  = isset($commentpages);
$options->commentsperpage  = (int) trim($commentsperpage);
$options->comments_invert_order  = isset($comments_invert_order);

}
}//class