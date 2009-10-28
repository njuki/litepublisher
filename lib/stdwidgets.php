<?php

class tstdwidgets extends TItems {

  public static function instance() {
    return getinstance(__class__);
  }

protected function create() {
parent::create();
$this->basename = 'stdwidgets';
}


public function add($name) {
}

public function delete($name) {
}

public function widgetdeleted($id) {
}

public function getwidget(id, $sitebar) {

}

public function request($arg) {
global $paths;
switch ($arg) {
case 'categories':
$file = $paths['cache'] . 'categories.php';
if (file_exists($file)) {
$result =file_get_contents($file);
} else {
$cats = tcategories::instance();
$result = $cats->getwidgetcontent(1);
file_put_contents($file, $result);
}
break;

case 'tags':
$file = $paths['cache'] . 'tags.php';
if (file_exists($file)) {
$result = file_get_contents($file);
} else {
$tags = ttags::instance();
$result = $tags->getwidgetcontent(1);
file_put_contents($file, $result);
}
break;

case 'meta':
$custom = tcustomwidget::instance();

break;
}

return $result;
}

}//class
?>