class thomepagesitebars extends titems {
public $current;
public $count;

public static function instance() {
return getinstance(__class__);
}

public function __construct() {
$this->current = 0;
$template = ttemplate::instance();
$this->items = &$template->data['sitebars'];
$theme = ttheme::instance();
$this->count = $theme->sitebarscount;
}

public function getcount($index) {
return count($this->items[$index]);
}

public function add($id, $index, $order) {
    if (($order < 0) || ($order > $this->getcount($index))) $order = $this->getcount($index);
    array_splice($this->items[$index], $order, 0, $id);
$this->save();
}

public function deletewidget($id) {
      for ($i = count($this->items) -1; $i >= 0; $i--) {
        $j = array_search($id, $this->items[$i]);
        if (is_int($j)) array_splice($this->items[$i], $j, 1);
      }
}

  private function getcontent($index) {
    $result = '';
$widgets = twidgets::instance();
    foreach ($this->items[$index] as $id) {
      $result .= $widgets->getcontent($id);
    }
    return $result;
  }

public function getcurrent() {
global $paths;
$template = ttemplate::instance();
$file = $paths['cache'] . "$template->tml.sitebar-$this->current.php";
if (file_exists($file)) {
$result = file_get_contents($file);
} else {
$result = $this->getcontent($this->current);
//если закончились сайтбары, то остатки объеденить
    if ($this->count == $this->current + 1) {
for ($i = $this->current + 1; $i < count($this->items); $i++) {
$result .= $this->getcontent($i);
}
}

file_put_contents($file, $result);
}
$template->onsitebar(&$result, $this->current++);
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