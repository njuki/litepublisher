<?php

class tfiles extends TItems {
public $itemsposts;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->itemsposts = new titemsposts();
    $this->basename = 'files';
$this->table = 'files';
    $this->addevents('Changed', 'Edited');
  }

public function load() {
if(!dbversion) parent::load();
}

public function save() {
if (!dbversion) parent::save();
}
  
  public function geturl($id) {
$item = $this->getitem($id);
    return '/files/' . $item['filename'];
  }
  
  public function getlink($id) {
    global $options;
$item = $this->getitem($id);
$icon = '';
if ($item['icon'] != 0) {
$icons = ticons::instance();
$icon = sprintf('<img src="%s" alt="%s" />', $icons->geturl($item['icon']), $item['title']);
}
    return sprintf('<a href="%1$s" title="%2$s">%3$s</a>', $options->files. $item['filename'], $item['title'], $icon . $item['description']);
  }
  
public function additem(array $item) {
global $options, $paths;
$realfile = $paths['files'] . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
$item = $item + array(
'author' => $options->user,
'posted' => time(),
'keywords' => ''
'md5' => md5_file($realfile),
'size' => filesize($realfile),
               'lang' => ''
);

if (dbversion) {
$id = $this->db->add($item);
$this->items[$id] = $item;
return $id;
 } else {
    $this->items[++$this->autoid] = $item;
    $this->save();
    $this->changed();
    $this->added($this->autoid);
    return $this->autoid;
}
  }

  public function delete($id) {
    global $paths;
    if (!$this->itemexists($id)) return false;
$item = $this->getitem($id);
    @unlink($paths['files']. str_replace('/', DIRECTORY_SEPARATOR, $item['filename']));
$this->lock();
parent::delete($id);
if ($item['preview'] > 0) $this->delete($item['preview']);
$this->unlock();
    $this->changed();
    return true;
  }
  
}//class

?>