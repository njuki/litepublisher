<?php

class ttheme extends TEventClass {
public $menu;
public $content;
public $navi;
public $widgets;
public $comments;

public static function instance() {
return getinstance(__class__);
}

protected function create() {
parent::create();
$template = ttemplate::instance();
$this->basename = 'themes' . DIRECTORY_SEPARATOR . "$template->theme-$template->tml";
$this->data['index'] = '';
$this->data['sitebarscount'] = 1;
$this->data['excerpt'] = '';
$this->data['post'] = '';
$this->data['commentform'] = '';

$this->addmap('navi', array());
$this->addmap('menu', array());
$this->addmap('content', array());
$this->addmap('widgets', array());
$this->addmap('comments', array());
}

public function load() {
global $paths;
    $filename = $paths['data'] . $this->getbasename() .'.php';
    if (file_exists($filename)) {
parent::load();
} else {
$template = ttemplate::instance();
$parser = tthemeparser::instance();
$parser->parse("$template->path$template->tml.tml", $this);
$this->save();
}
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