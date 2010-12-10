<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tthemetree extends tadminmenu implements iwidgets {
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

public function getwidgetcontent($id, $sidebar) {
$result = '';
//root tags
$result .= $this->getsingle('title');
$result .= $this->gettree('menu');
$result .= $this->gettree('content');
$result .= $this->getsidebars();
return sprintf('<ul id="themetree">%s</ul>', $result);
}

public function getwidget($id, $sidebar) {
      $title = $this->gettitle($id);
      $content = $this->getwidgetcontent($id, $sidebar);
    $theme = ttheme::instance();
    return $theme->getwidget($title, $content, 'widget', $sidebar);
}

public function getwidgets(array &$items, $sidebar) {
if (($sitebar == 0) && isset($this->theme)) {
$id = twidgets::instance()->find($this);
array_insert($items, $id, 0);
}
}

public function getcontent() {
if (isset($this->theme)) {
return '<div id="themeeditor"></div>';
} else {
}
}

public function processform() {
$name = tadminhtml::getparam('name', '');
if (($name === '') || !ttheme::exists($name)) return '';
$this->theme = ttheme::getinstance($name);
$templates = &$theme->templates;
foreach ($_POST as $name => $value) {
$name = str_replace('_', '.', $name);
if (isset($templates[$name]) {
$templates[$name] = trim($value);
} elseif (strbegin($name, sidebar')) {
if (strbegin($name, 'sidebar.')) {$sidebar = 0;
} elseif (preg_match('/^sidebar(\d)\./', $name, $m)) {
$sidebar = (int) $m[1];
} else {
continue;
}
if (isset($templates[$sidebar][$name])) $templates[$sidebar][$name] = $value;
}
}

if (is_string($templates['menu.hover'])) $templates['menu.hover'] = trim($templates['menu.hover']) == 'true';

$theme->save();
tthemeparser::compress($theme);
return '';
}//class