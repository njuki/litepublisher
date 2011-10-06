<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tthemegenerator extends tevents_itemplate {
public $values;
private $colors;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->cache = false;
    $this->basename=  'plugins' .DIRECTORY_SEPARATOR  . strtolower(get_class($this));
$this->addmap('values', array());
$this->colors = array();
  }

public function getselectors() {
$result = array();
$s = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . res' . DIRECTORY_SEPARATOR   . 'scheme.tml');
$lines = explode("\n", str_replace(array("\r\n", "\r"), "\n", trim($s)));
$css = array();
foreach ($lines as $line) {
$i = strpos($line, '{');
$sel = trim(substr($line, 0, $i ));
$list = explode(';', trim(trim(substr($line, $i)), '{}; '));
$props = array();
foreach ($list as $v) {
$v =trim($v, '; ');
if ($v == '') continue;
$prop = explode(':', $v);
$props[trim($prop]0])] = trim($prop[1]);
}
$css[$sel] = $props;
}

foreach ($this->values as $name => $value) {
foreach ($css as $sel => $props) {
foreach ($props as $propname => $propvalue) {
if (strpos($propvalue, "%%$name%%")) {
$result[] = array(
'name' => $name,
'sel' => $sel,
'prop' => $prop
);
}
}
}
}

return json_encode($result);
}

public function gethead() {
$pickerpath = litepublisher::$site->files . '/plugins/colorpicker/';
$result =   '<link type="text/css" href="' $pickerpath . 'css/colorpicker.css" rel="stylesheet" />';

$template = ttemplate::i();
$result .= $template->getjavascript('/plugins/colorpicker/js/colorpicker.js');
$result .= $template->getjavascript(sprintf('/plugins/%s/themegenerator.min.js', basename(dirname(__file__))));
return $result;
}

public function gettitle() {
return $this->data['title'];
}

public function request($arg) {
tlocal::usefile('themegenerator');
$lang = tlocal::i('themegenerator');
$this->colors = $lang->ini['themecolors'];

    if (isset($_POST) && (count($_POST) > 0)) {
      if (get_magic_quotes_gpc()) {
        foreach ($_POST as $name => $value) {
          $_POST[$name] = stripslashes($_POST[$name]);
        }
      }

$this->processform();
    }
}

  public function getcont() {
$result = '';
$tml = '<p>
      <input type="button" name="colorbutton-$name" id="colorbutton-$name" rel="text-color-$name" value="' . $lng['selectcolor'] . '" />
      #<input type="text" name="color_$name" id="text-color-$name" value="$value" size="22" />
      <label for="text-color-$name"><strong>$label</strong></label>
</p>';

$theme = $this->view->theme;
tlocal::usefile('themegenerator');
$lang = tlocal::i('themegenerator');
$args = new targs();
$a = new targs;
foreach ($this->colors as $name => $value) {
$args->name = $name;
$args->value = $value;
$args->label = $lang->$name;
$a->$name = $theme->parsearg($tml, $args);
}
$result .= $theme->parsearg($form, $a);
return $theme->simple($result);
  }

public function setcolor($name, $value) {
if (isset($this->colors[$name])) {
$value = trim($value);
if (preg_match('/[0-9a-zA-Z]?*/', $value)) {
$this->colors[$name] = $value;
}
}
}

public function processform() {
switch ($_POST['formtype']) {
case 'colors':
foreach ($_POST as $name => $value) {
if (strbegin($name, 'color_')) {
$name = substr($name, strlen('color_'));
$this->setcolor($name, $value);
}
}
$this->sendfile();
break;

case 'uploadcolors':
        if (isset($_FILES['filename'])) {
        if (isset($_FILES['filename']['error']) && $_FILES['filename']['error'] > 0) {
$lang = tlocal::admin('uploaderrors');
          $result .= sprintf('<h4>%s</h4>', $lang->__get($_FILES['filename']['error']));
        } elseif (!is_uploaded_file($_FILES['filename']['tmp_name'])) {
$result .= sprintf('<h4>%s</h4>', $lng['attack']);
} else {
$colors = parse_ini_file($_FILES['filename']['tmp_name']);
foreach ($colors as $name => $value) {
$this->setcolor($name, $value);
}
}
}
break;
}
}

public function sendfile() {
$themedir = litepublisher::$paths->themes . 'generator' . DIRECTORY_SEPARATOR;
$css = file_get_contents($themedir . 'style.css');
$args = new targs();
$colors = "[themecolors]\n";
foreach ($this->colors as $name => $value) {
$colors .= "$name = \"$value\"\n";
$args->$name = $value;
}

$res = dirname(__file__) . DIRECTORY_SEPARATOR  . 'res' . DIRECTORY_SEPARATOR ;
$css .= strtr(file_get_contents($res . scheme.tml'), $args->data);

$path = "themes/generator$u/"

    require_once(litepublisher::$paths->libinclude . 'zip.lib.php');
$zip = new zipfile();
$zip->addFile($colors, $path . 'colors.ini');
$zip->addFile($css, $path . 'style.css');

      $result = $zip->file();
}
  
}//class