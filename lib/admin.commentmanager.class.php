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
    $options = litepublisher::$options;
      $form = new tautoform(litepublisher::$options, 'options', 'commentform');
if (dbversion) {
$lang = tlocal::admin();
      $form->addprop(array(
'obj' => litepublisher::$options,
'propname' => 'comments_status',
'type' => 'combo',
'items' => array(
'closed' => $lang->closed,
'reg' => $lang->reg,
'guest' => $lang->guest,
'comuser' => $lang->comuser
)
));
} else {
      $form->add($form->commentsenabled);
}

      $form->add($form->filtercommentstatus, $form->commentsapproved, $form->checkduplicate, $form->defaultsubscribe, $form->commentsdisabled, $form->autocmtform, $form->pingenabled,
      $form->commentpages, $form->commentsperpage, $form->comments_invert_order);
      $form->obj = litepublisher::$classes->commentmanager;
      $form->add($form->sendnotification, $form->hidelink,  $form->redir, $form->nofollow);
      $form->addeditor(tsubscribers::i(), 'locklist');

return $html->adminform($tabs->get(), $args);
}
    
public function processform() {
}
}//class