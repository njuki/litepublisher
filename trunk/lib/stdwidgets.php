<?php

class tstdwidgets extends TItems {
public $names;
private $ajaxincluded;

  public static function instance() {
    return getinstance(__class__);
  }

protected function create() {
parent::create();
$this->basename = 'stdwidgets';
$this->ajaxincluded = false;
$this->names = array(
'categories' => 'tcategories',
 'tags' => 'ttags',
 'archives' => 'tarchives',
 'links' => 'tlinks',
'posts' => 'tposts',
 'meta' => __class__, 
 'friends' => 'tfoaf'
);
}

public function add($name, $ajax) {
if (isset($this->items[$name])) return $this->eerror("widget  $name already exists");
$widgets = twidgets::instance();
$id = $widgets->add($this->class, 'echo', 0, -1);

$this->items[$name] = array(
'id' => $id,
'ajax' = true,
'options' => array()
);
$this->save();
}

public function delete($name) {
if (!isset($this->items[$name])) return;
$widgets = twidgets::instance();
$widgets->delete($this->items[$namre]['id']);
unset($this->items[$name]);
$this->save();
}

public function widgetdeleted($id) {
if ($name = $this->getname($id)) {
unset($this->items[$name]);
$this->save();
}
}

public function getname($id) {
foreach ($this->items as $name => $item) {
if ($id == $item['id']) return $name;
}
return false;
}

public function getoptions($name) {
return new tarray2prop($this->items[$name]['options']);
}

public function request($arg) {
if (!isset($this->items[$name])) return 404;
$result = "<?php 
    @header('Content-Type: text/html; charset=utf-8');
    @ header('Last-Modified: ' . date('r'));
    @header('X-Pingback: $options->url/rpc.xml');
    ?>";

$result .= $this->getcontent($name);
return $result;
}

public function getwidget(id, $sitebar) {
global $options;
if (!($name = $this->getname($id))) return '';
$result = '';
$title = $this->items[$name]['title'];
if ($this->items[$name]['ajax']) {
$title = "<a onclick=\"loadcontent('widget$name', '$options->url/stdwidget/$name/')\">$title</a>";
$content = '';
if (!$this->ajaxincluded) $result = file_get_contents($paths['libinclude'] . 'ajax.txt');
$this->ajaxincluded = true;
} else {
$content = $this->getcontent($name);
}

$theme = ttheme::instance();
$result .= $theme->getwidget($title, $content, $name, $sitebar);
return $result;
}

public function getcontent($name) {
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