<?php

class ttheme extends TEventClass {
public $menu;
public $content;
public $widgets;
public $comments;

public static function instance() {
return getinstance(__class__);
}

  public function getbasename() {
$template = ttemplate::instance();
return 'theme' . DIRECTORY_SEPARATOR . $template->tml;
}

protected function create() {
parent::create();
$this->data['main'] = '';
$this->data['sitebarscount'] = 1;
$this->data['excerpt'] = '';
$this->data['post'] = '';
$this->data['commentform'] = '';


$this->addmap('menu', array());
$this->addmap('content', array());
$this->addmap('widgets', array());
$this->addmap('comments', array());
}

public function getwidget($name, $sitebar) {
if (isset($this->widgets[$sitebar][$name]) return $this->widgets[$sitebar][$name];
switch ($name) {
case 'postitem': return '<li><strong><a href=\"$post->link\" rel=\"bookmark\" title=\"Permalink to $post->title\">$post->title</a></strong><br />
    <small>$post->localdate</small></li>';

}
return '';
}
}//class
?>