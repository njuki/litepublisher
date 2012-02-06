<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminuserpages extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    $result = parent::gethead();
    $template = ttemplate::i();
  $result .= $template->getready('$("#tabs").tabs({ cache: true });');
    return $result;
  }
  
  public function getcontent() {
    $result = '';
    $users = tusers::i();
$pages = tuserpages::i();
   
    $html = $this->html;
    $lang = tlocal::i('users');
    $args = targs::i();
    
    if (!$groups->hasright(litepublisher::$options->group, 'admin')) {
      $item = $users->getitem(litepublisher::$options->user);
      $args->add($item);
      $args->add($pages->getitem(litepublisher::$options->user));
      return $html->userform($args);

    
    $id = $this->idget();
    if ($users->itemexists($id)) {
      $item = $users->getitem($id);
      $args->add($item);
      $args->add($pages->getitem($id));

}

  public function processform() {
    $users = tusers::i();
    $pages = tuserpages::i();

    
    if (!$groups->hasright(litepublisher::$options->group, 'admin')) {
      extract($_POST, EXTR_SKIP);
      $pages->edit(litepublisher::$options->user, array(
      'name' => $name,
      'website' => $website,
      'rawcontent' => trim($rawcontent),
      'content' => tcontentfilter::i()->filter($rawcontent),
      ));
      
}

}//class      
