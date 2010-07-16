<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tfriendswidget extends twidget {

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.friends';
$this->template = 'friends';
$this->data['title'] = tlocal::$data['default']['myfriends'];
    $this->data['maxcount'] =0;
    $this->data['redir'] = true;
    $this->data['redirlink'] = '/foaflink.htm';
}

  public function getcontent($id, $sitebar) {
$foaf = tfoaf::instance();
    $items = $foaf->getapproved($this->maxcount);
    if (count($items) == 0) return '';
    $result = '';
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('friends', $sitebar);
    $args = targs::instance();
    foreach ($items as $id) {
      $item = $foaf->getitem($id);
      $args->add($item);
      if ($this->redir && !strbegin($item['url'], litepublisher::$options->url)) {
        $args->url = litepublisher::$options->url . $this->redirlink . litepublisher::$options->q . "id=$id";
      }
      $result .=   $theme->parsearg($tml, $args);
    }

    return sprintf($theme->getwidgetitems('friends', $sitebar), $result);

  public function request($arg) {
      $id = empty($_GET['id']) ? 1 : (int) $_GET['id'];
$foaf = tfoaf::instance();
      if (!$foaf->itemexists($id)) return 404;
$item = $foaf->getitem($id);
      $this->cache = false;
    return sprintf('<?php @header(\'Location: %s\'); ?>', $item['url']);
    }

}
  
}//class

?>