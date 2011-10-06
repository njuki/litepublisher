<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tthemegenerator extends tevents_itemplate {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->cache = false;
    $this->basename=  'plugins' .DIRECTORY_SEPARATOR  . strtolower(get_class($this));
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
$scheme = parse_ini_file(dirname(__file__) .DIRECTORY_SEPARATOR   . 'scheme.ini', true);
$lng = $scheme[litepublisher::$options->language];
        if (isset($_FILES['filename'])) {
        if (isset($_FILES['filename']['error']) && $_FILES['filename']['error'] > 0) {
$lang = tlocal::admin('uploaderrors');
          $result .= sprintf('<h4>%s</h4>', $lang->__get($_FILES['filename']['error']));
        } elseif (!is_uploaded_file($_FILES['filename']['tmp_name'])) {
$result .= sprintf('<h4>%s</h4>', $lng['attack']);
} else {
$colors = parse_ini_file($_FILES['filename']['tmp_name']);
}
}

if (!isset($colors)) $colors =  $scheme['colors'];

$tml = '<p>
      <input type="text" name="color_$name" id="text-color-$name" value="$value" size="22" />
      <label for="text-color-$name"><strong>$label</strong></label>
      <input type="button" name="colorbutton-$name" id="colorbutton-$name" rel="text-color-$name" value="' . $lng['selectcolor'] . '" />
</p>';

$args = new targs();
$a = new targs;
foreach ($scheme['colors'] as $name => $value) {
$args->name = $name;
$args->value = $value;
$args->label = $lng[$name];
$a->$name = $theme->parsearg($tml, $args);
}
$result .= $theme->parsearg($form, $a);
return $theme->simple($result);
  }

public function processform() {
}
  
}//class