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
$result .= $template->getjavascript('/plugins/' . basename(dirname(__file__)) . '/tree.js');
$name = tadminhtml::getparam('name', '');
if (($name != '') && ttheme::exists($name)) {
$this->theme = ttheme::getinstance($name);
$this->ini = tini2array::parsesection(file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . litepublisher::$options->language . '.theme.ini'));
$result .= $this->themetojavascript($name);
}
return $result;
}

public function themetojavascript($name) {
$result = '  <script type="text/javascript">
var theme = [];
';
$theme = ttheme::getinstance($name);
foreach ($theme->templates as $name => $value) {
if (is_string($value)) {
$value = tadminhtml::specchars($value);
$result .= "theme['$name'] = '$value';\n";
}
}
$value =$theme->templates['menu.hover'] ? 'true' : 'false';
$result .= "theme['menu.hover'] = '$value';\n";
foreach ($theme->templates['sidebars'] as $i => &$sidebar) {
$pre = $i == 0 ? 'sidebar' : "sidebar$";
foreach ($sidebar as $name => $value) {
if (!is_string($value)) continue;
$value = tadminhtml::specchars($value);
$result .= "theme['$pre.$name'] = '$value';\n";
}
}

$result .=   "\n</script>";
file_put_contents('theme.js', $result);
return $result;
}

private function getignore($name) {
return ($name == 'content') || ($name == 'content.post.templatecomments') || ($name == 'content.admin');
}
  
private function getitem($name) {
return sprintf('<li><a rel="%s" href="">%s</a>%s</li>', 
$this->getignore($name) ? 'ignore' : $name, $this->ini[$name], $this->getsubitems($name));
}

private function getsubitems($name) {
$items = $this->theme->reg("/$name.\w*+/");
if (count($items) == 0) return '';
$result = '';
foreach ($items as $name => $val) {
$result .= $this->getitem($name);
}
return sprintf('<ul>%s</ul>', $result);
}

private function getthemesidebars() {
$result = sprintf('<li><a rel="ignore" href="">%s</a><ul>', $this->ini['sidebars']);
$names = ttheme::getwidgetnames();
array_unshift($names, 'widget');
foreach ($this->theme->templates['sidebars'] as $i => &$widgets) {
$result = sprintf('<li><a rel="ignore" href="">%d</a><ul>', $i);
$pre = $i == 0 ? 'sidebar' : "sidebar$i";
foreach ($names as $name) {
if (isset($widgets[$name])) {
$subitems = sprintf('<li><a rel="%s" href="">%s</a></li>', "$pre.$name.subitems", $this->ini["sidebar.widget.subitems"]);
$item = sprintf('<li><a rel="%s" href="">%s</a><ul>%s</ul></li>', "$pre.$name.item", $this->ini['sidebar.widget.item'], $subitems);
$items = sprintf('<li><a rel="%s" href="">%s</a><ul>%s</ul></li>', "$pre.$name.items", $this->ini["sidebar.widget.items"], $item);
$result .= sprintf('<li><a rel="%s" href="">%s</a><ul>%s</ul></li>',  "$pre.$name", $this->ini["sidebar.$name"], $items);
}
}
$result .= '</ul></li>';
}
$result .= '</ul></li>';
return $result;
}

public function getwidgetcontent($id, $sidebar) {
$result = '';
//root tags
$result .= $this->getitem('title');
$result .= $this->getitem('menu');
$result .= $this->getitem('content');
$result .= $this->getthemesidebars();
return sprintf('<ul id="themetree">%s</ul>', $result);
}

public function getwidget($id, $sidebar) {
      $title = $this->gettitle();
      $content = $this->getwidgetcontent(0, $sidebar);
    $theme = ttheme::instance();
    return $theme->getwidget($title, $content, 'widget', $sidebar);
}

//iwidget
public function getwidgets(array &$items, $sidebar) { }

  public function getsidebar(&$content, $sidebar) {
if (($sidebar > 0) || !isset($this->theme)) return;
$content = $this->getwidget(0, 0) . $content;
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
if (isset($templates[$name])) {
$templates[$name] = trim($value);
} elseif (strbegin($name, 'sidebar')) {
if (strbegin($name, 'sidebar.')) {
$sidebar = 0;
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
}

}//class