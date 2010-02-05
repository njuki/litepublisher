<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tadminsapeplugin {
private $widgets = array('ategories', 'TArchives', 'TLinksWidget', 'TFoaf', 'TPosts', 'TMetaWidget');

public function getcontent() {
$plugin = tsapeplugin::instance();
$std = tstdwidgets::instance();
$theme = ttheme::instance();
$checkbox = '<p><input type="checkbox" name="widget-$id" id="widget-$id" value="$id" $checked/>
<label for="widget-$id">$name</label></p>';

$checkboxes = '';
$args = targs::instance();
foreach ($std->items as $name => $item) {
if ($item['ajax']) continue;
$args->id = $item['id'];
$args->checked = in_array($item['id'], $plugin->widgets);
$args->name = $std->gettitle($name);
$checkboxes .= $theme->parsearg($checkbox, $args);
}

$args->checkboxes = $checkboxes;
$args->user = $plugin->user;
$args->count = $plugin->count;
$args->force = $plugin->force;
$tml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'sapeform.tml');
return $theme->parsearg($tml, $args);
}

public function processform() {
$plugin = tsapeplugin::instance();
$plugin->lock();
$plugin->widgets = array();
foreach ($_POST as $name => $value) {
if (strbegin($name, 'widget-')) $plugin->widgets[] = (int) $value;
}
extract($_POST);
$plugin->count = (int) $count;
$plugin->user = $user;
$plugin->force = isset($force);

$plugin->unlock();		
return '';
}

}//class
?>