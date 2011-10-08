<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tthemegenerator extends tevents_itemplate implements itemplate {
public $values;
public $selectors;
private $colors;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->cache = false;
    $this->basename=  'plugins' .DIRECTORY_SEPARATOR  . strtolower(get_class($this));
$this->addmap('values', array());
$this->addmap('selectors', array());
$this->colors = array();
  }

public function parseselectors() {
$this->selectors = array();
$s = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'res' . DIRECTORY_SEPARATOR   . 'scheme.tml');
$lines = explode("\n", str_replace(array("\r\n", "\r"), "\n", trim($s)));
foreach ($lines as $line) {
$line = trim($line);
if ($line == '') continue;
$css = explode('{', $line);
$sel = rtrim($css[0]);
$proplist = explode(';', trim($css[1], '{}; '));
$props = array();
foreach ($proplist as $v) {
$v =trim($v, '; ');
if ($v == '') continue;
$prop = explode(':', $v);
$propname = trim($prop[0]);
$propvalue =trim($prop[1]);
if (preg_match_all('/%%(\w*+)%%/', $propvalue, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
$this->selectors[] = array(
'name' => $item[1],
'sel' => $sel,
'propname' => $propname,
'value' => $propvalue
);
}
}
}
}
//dumpvar($this->selectors);
$this->save();
}

public function gethead() {
$pickerpath = litepublisher::$site->files . '/plugins/colorpicker/';
$result =   '<link type="text/css" href="' . $pickerpath . 'css/colorpicker.css" rel="stylesheet" />';

$template = ttemplate::i();
$template->ltoptions['colors'] = $this->selectors;
$result .= $template->getjavascript('/plugins/colorpicker/js/colorpicker.js');
$result .= $template->getjavascript(sprintf('/plugins/%s/themegenerator.min.js', basename(dirname(__file__))));
return $result;
}

public function gettitle() {
return $this->data['title'];
}

public function request($arg) {
//$this->parseselectors();
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
tlocal::usefile('themegenerator');
$lang = tlocal::i('themegenerator');

$tml = '<p>
      <input type="button" name="colorbutton-$name" id="colorbutton-$name" rel="$name" value="' . $lang->selectcolor . '" />
      #<input type="text" name="color_$name" id="text-color-$name" value="$value" size="16" />
      <label for="text-color-$name"><strong>$label</strong></label>
</p>';

$theme = $this->view->theme;
$args = new targs();
$a = new targs;
foreach ($this->colors as $name => $value) {
$args->name = $name;
$args->value = $value;
$args->label = $lang->$name;
$a->$name = $theme->parsearg($tml, $args);
}
$form = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR  . 'res' . DIRECTORY_SEPARATOR  . 'form.tml');
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
$css .= strtr(file_get_contents($res . 'scheme.tml'), $args->data);

$u = time();
$path = "themes/generator$u/";

    require_once(litepublisher::$paths->libinclude . 'zip.lib.php');
$zip = new zipfile();
$zip->addFile($colors, $path . 'colors.ini');
$zip->addFile($css, $path . 'style.css');

      $result = $zip->file();
}
  
}//class