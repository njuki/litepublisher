<?php

class TAdminLightbox {

public function Getcontent() {
global $Options;
$checked = "checked='checked'";
$plugin = &TLightbox::Instance();
$animate = $plugin->animate ? $checked : '';
$posts =$plugin->posts ? $checked : '';
$comments = $plugin->comments ? $checked : '';
$excerpt = $plugin->excerpt ? $checked : '';
$tml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . "lightbox$Options->language.tml");
eval('$result = "'. $tml . '\n";');
$result = str_replace("'", '"', $result);
return $result;
}

public function ProcessForm() {
extract($_POST);
$filter = &TContentFilter::Instance();
$filter->Lock();
$plugin = &TLightbox::Instance();
$plugin->Lock();
if (is_numeric($speed)) $plugin->speed = $speed;
if (is_numeric($border)) $plugin->border = $border;
if (is_float($opacity)) $plugin->opacity = $opacity;
$plugin->animate = isset($animate);
$plugin->posts = isset($posts);
$plugin->comments = isset($comments);
$plugin->excerpt = isset($excerpt);
$plugin->Unlock();		
$filter->Unlock();
return '';
}

}
?>