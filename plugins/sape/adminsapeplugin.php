<?php

class TAdminSapePlugin {
private $widgets = array('TCategories', 'TArchives', 'TLinksWidget', 'TPosts', 'TCommentManager', 'TMetaWidget');

public function Getcontent() {
$plugin = &TSapePlugin::Instance();
$lang = &TLocal::$data['stdwidgetnames'];
$checkbox = '<p><input type=\'checkbox\' name=\'$name\' id=\'$name\' $checked/>
<label for=\'$name\'>$value</label></p>';

$checkboxes = '';
foreach ($this->widgets as $name) {
$value = $lang[$name];
$checked = in_array($name, $plugin->widgets) ? "checked='checked'" : '';
eval('$checkboxes .= "'. $checkbox . '\n";');
}

$force = $plugin->force ? "checked='checked'" : '';
$tml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'sapeform.tml');
eval('$result = "'. $tml . '\n";');
$result = str_replace("'", '"', $result);
return $result;
}

public function ProcessForm() {
$plugin = &TSapePlugin::Instance();
$plugin->Lock();
$plugin->widgets = array();
foreach ($_POST as $name => $value) {
if (in_array($name, $this->widgets)) $plugin->widgets[] = $name;
}
extract($_POST);
$plugin->count = $count;
$plugin->user = $user;
$plugin->force = isset($force);
$plugin->Unlock();		
return '';
}

}
?>