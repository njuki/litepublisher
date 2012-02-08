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

  public function  gethead() {
    return parent::gethead() . tuitabs::gethead();
  }
  
public function getiduser() {
    if (tusergroups::i()->hasright(litepublisher::$options->group, 'admin')) {
    $id = $this->idget();
} else {
$id = litepublisher::$options->user;
}

if (tusers::i()->itemexists($id)) return $id;
return false;
}  

  public function getcontent() {
    $result = '';
    $html = $this->html;
    $lang = tlocal::i('users');
    $args = targs::i();
    
if (!($id= $this->getiduser())) return $this->notfound();
$pages = tuserpages::i();
      $item = $users->getitem(litepublisher::$options->user);
      $args->add($item);
      $args->add($pages->getitem($id));
$args->formtitle = sprintf('<a href="$site.url%s">%s</a>', $item['url'], $item['name']);

    $tabs = new tuitabs();
$tabs->add($lang->title, '[text=name] [text=website]');
$tabs->add('SEO', '[text=url] [text=keywords] [text=description] [text=head]');
$tabs->add($lang->text, '[editor=rawcontent]');

      return $html->adminform($tabs->get(), $args);
}

  public function processform() {
      extract($_POST, EXTR_SKIP);
if (!($id= $this->getiduser())) return;
    $pages = tuserpages::i();

      $pages->edit($id, array(
      'name' => $name,
      'website' => $website,
      'rawcontent' => trim($rawcontent),
      'content' => tcontentfilter::i()->filter($rawcontent),
'idview' => (int) $idview,
'url' => $url,
'head' => $head,
'keywords => $keywords,
'description' => $description
      ));
      }

}//class      
