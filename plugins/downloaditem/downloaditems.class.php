<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdownloaditems extends tposts {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->childtable = 'downloaditems';
  }
  
  public function createpoll() {
    tlocal::loadsection('admin', 'tickets', dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR);
    $lang = tlocal::instance('tickets');
    $items = explode(',', $lang->pollitems);
    $polls = tpolls::instance();
    return $polls->add('', 'opened', 'button', $items);
  }
  
  public function add(tpost $post) {
    //$post->poll = $this->createpoll();
    $post->updatefiltered();
return parent::add($post);
  }
  
  public function edit(tpost $post) {
    $post->updatefiltered();
    return parent::edit($post);
  }
  
  public function postsdeleted(array $items) {
    $deleted = implode(',', $items);
    $db = $this->getdb($this->childtable);
    $idpolls = $db->res2id($db->query("select poll from $db->prefix$this->childtable where (id in ($deleted)) and (poll  > 0)"));
    if (count ($idpolls) > 0) {
      $polls = tpolls::instance();
      foreach ($idpolls as $idpoll)       $pols->delete($idpoll);
    }
  }
  
  public function themeparsed($theme) { 
if (empty($theme->templates['custom']['downloadexcerpt'])) {
  $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    tlocal::loadsection('', 'downloaditem', $dir);
     ttheme::$vars['lang'] = tlocal::instance('downloaditem');
$theme->templates['custom']['downloadexcerpt'] = file_get_contents($dir . 'downloadexcerpt.tml');
$theme->templates['custom']['downloaditem'] = file_get_contents($dir . 'downloaditem.tml');
$theme->templates['custom']['siteform'] = $theme->parse(file_get_contents($dir . 'siteform.tml'));

//admin
$theme->templates['customadmin']['downloadexcerpt'] = array(
'type' => 'text',
'title' => 'Download excerpt'
);

$theme->templates['custom']['downloaditem'] = array(
'type' => 'text',
'title' => 'Download links'
);

$theme->templates['custom']['siteform'] = array(
'type' => 'text',
'title' => 'Upload site form'
);
}
}

}//class