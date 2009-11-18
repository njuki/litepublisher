<?php

class tsitebars {
public $current;
public $count;
private $items;

public function __construct() {
$this->current = 0;
$template = ttemplate::instance();
$this->items = &$template->data['sitebars'];
$theme = ttheme::instance();
$this->count = &$theme->data['sitebarscount'];
}

public function save() {
$template = ttemplate::instance();
$template->save();
}

public function getcount($index) {
return count($this->items[$index]);
}

public function add($id, $index, $order) {
    if (($order < 0) || ($order > $this->getcount($index))) $order = $this->getcount($index);
    array_splice($this->items[$index], $order, 0, $id);
}

public function deletewidget($id) {
      for ($i = count($this->items) -1; $i >= 0; $i--) {
        $j = array_search($id, $this->items[$i]);
        if (is_int($j)) array_splice($this->items[$i], $j, 1);
      }
}

  public function getcontent($index) {
    $this->current = $index;
    $result = '';
$widgets = twidgets::instance();
    foreach ($this->items[$index] as $id) {
      $result .= $widgets->getcontent($id);
    }
    return $result;
  }

public function getcurrent() {
global $paths, $template;
$file = $paths['cache'] . "$template->tml.sitebar-$this->current.php";
if (file_exists($file)) return file_get_contents($file);
$result = $this->getcontent($this->current++);
file_put_contents($result);

//если закончились сайтбары, то остатки объеденить
    if ($this->count == $this->current) {
while ($this->current < count($this->items)) $result .= $this->getcurrent();
   }

return $result;
}

public function find($idwidget) {
foreach ($this->items as $i => $widgets) {
if (in_array($idwidget, $widgets)) return $i;
}
return false;
}

}//class
?>