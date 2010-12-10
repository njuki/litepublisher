<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tthemetree extends tadminmenu {
private $ini;
private $theme;

  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

public function gethead() {
$result = parent::gethead();
$template = ttemplate::instance();
$result .= $template->getjavascript('/plugins/ . basename(dirname(__)) . '/tree.js');
$name = tadminhtml::getparam('name', '');
if (($name != '') && (ttheme::exists($name)) {
$this->theme = ttheme::getinstance($name);
$this->ini = tini2array::parsesection(file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . litepublisher::$options->language . '.theme.ini'));
$result .= $this->themetojavascript($name);
}
return $result;
}

public function themetojavascript($name) {
$result = '  <script type="text/javascript">
var theme = [];';
$theme = ttheme::getinstance($name);
foreach ($theme->templates as $name => $value) {
if (is_string($value)) {
$value = tadminhtml::specchars($value);
$result .= "theme['$name'] = '$value';\n";
}
}
$value =$theme->tempalates['menu.hover'] ? 'true' : 'false';
$result .= "ttheme['menu.hover'] = '$value';\n";
foreach ($theme->templates['sidebars'] as $i => &$sidebar) {
$pre = $i == 0 ? 'sidebar' : "sidebar$";
foreach ($sidebar as $name => $value) {
$value = tadminhtml::specchars($value);
$result .= "theme['$pre.$name'] = '$value';\n";
}
}

$result .=   "\n</script>";
return $result;
}
  
private function getitem($name) {
return sprintf('<li><a rel="%s" href="">%s</a>%s</li>', $name, $this->ini[$name], $this->getsubitems($name));
}

private function getsubitems($name) {
$items = $this->theme->reg("$name.\w*+");
if (count($items) == 0) return '';
$result = '';
foreach ($items as $name => $val) {
$result .= $this->getitem($name);
}
return sprintf('<ul>%s</ul>', $result);
}

public function getwidgetcontent() {
$result = '';
//root tags
$result .= $this->getsingle('title');
$result .= $this->gettree('menu');
$result .= $this->gettree('content');
$result .= $this->getsidebars();
return sprintf('<ul id="themetree">%s</ul>', $result);
}


public function getcontent() {
$name = tadminhtml::getparam('name', '');
if (ttheme::exists($name)) {
} else {
}
}

public function processform() {
}

}//class