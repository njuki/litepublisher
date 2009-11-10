<?php

class tstdwidgets extends TItems {

  public static function instance() {
    return getinstance(__class__);
  }

protected function create() {
parent::create();
$this->basename = 'stdwidgets';
$this->data['names'] = array('categories', 'archives', 'links', 'friends', 'tags', 'posts', 'meta');
}

public function add($name, $ajax) {
if (isset($this->items[$name])) return $this->error("widget  $name already exists");
$widgets = twidgets::instance();
$id = $widgets->add($this->class, 'echo', 0, -1);

$this->items[$name] = array(
'id' => $id,
'ajax' = $ajax,
'title' => $this->gettitle($name)
);
$this->save();
$this->updateajax();
return $id;
}

public function setajax($name, $ajax) {
if (isset($this->items[$name) && ($this->items[$name]['ajax'] != $ajax)) {
$this->items[$name]['ajax'] = $ajax;
$this->save();
$this->updateajax();
}
}

public function updateajax() {
$ajax = false;
foreach ($this->items as $name => $item) {
if ($item['ajax']) {
$ajax = true;
break;
}
}
$template = ttemplate::instance();
$template->addjavascript('ajax', file_get_contents($paths['libinclude']. 'ajax.js'));
}

public function delete($name) {
if (!isset($this->items[$name])) return;
$widgets = twidgets::instance();
$widgets->delete($this->items[$namre]['id']);
unset($this->items[$name]);
$this->save();
$this->updateajax();
}

public function widgetdeleted($id) {
if ($name = $this->getname($id)) {
unset($this->items[$name]);
$this->save();
$this->updateajax();
}
}

public function gettitle($name) {

}

public function getname($id) {
foreach ($this->items as $name => $item) {
if ($id == $item['id']) return $name;
}
return false;
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

public function getwidgetcontent($id) {
if ($name = $this->getname($id)) {
return $this->getcontent($name);
}
return '';
}

public function getcontent($name) {
global $paths, $classes;
if ($name == 'meta') return $this->meta;
$id = isset($this->items[$name]) ? $This->items[$name]['id'] : $name;
$file = $paths['cache'] . 'widget$id.php';
if (file_exists($file) return file_get_contents($file);

$instance = $classes->$name;
$result = $instance->getwidgetcontent($id);
file_put_contents($file, $result);
return $result;
}

protected function setmeta($s) {
if ($this->meta != $s) {
$this->data['meta'] = $s;
$this->save();
}
}

}//class
?>