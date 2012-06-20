class tbasepostprops extends tdata {
public $dataname;
public $defvalues;
public $arrayprops;
public $intarray;
public $intprops;
public $boolprops;
public $allprops;
public $types;

protected function create() {
parent::create();
$this->dataname = 'postprops';
$this->table = 'posts';
$this->defvalues = array();
}

public function update_all_props() {
$this->allprops =array_keys($this->defvalues);
$methods = get_class_methods($this);
foreach ($methods as $name) {
if ((strlen($name) > 3) && (strbegin($name, 'get') || strbegin($name, 'set'))) {
if (!in_array($name, $this->allprops)) $this->allprops[] = $name;
}
}

$this->types = array();
foreach ($this->allprops as $name) {
if (in_array($name, $this->intprops)) {
$type = 'int';
} elseif (in_array($name, $this->boolprops)) {
$type = 'bool;
if (in_array($name, $this->arrayprops)) {
$type = 'array';
if (in_array($name, $this->arrayprops)) {
$type = 'array';
if (in_array($name, $this->intarray)) {
$type = 'intarray';
} else {
$type = 'string';
}

$this->types[$name] = $type;
}
}

public function get(tpost $post, $name, &$value) {
if (!in_array($name, $this->allprops)) return false;

if (!isset($post->propdata[$this->dataname])) $this->load_item($post);
$data = &$post->propdata[$this->dataname];
    if (method_exists($this, $get = 'get' . $name)) {
$value = $this->$get($data);
return true;
}

switch($this->types[$name]) {
case 'int':
$value = (int) $data[$name];
break;

case 'bool':
$value = $data[$name] == '1';
break;

case 'array':
case 'intarray':
if (!isset($post->syncdata[$this->dataname])) $post->syncdata[$this->dataname] = array();
$syncdata = &$post->syncdata[$this->dataname];
if (isset($syncdata([$name])) {
$value = syncdata[$name];
} else {
$value = array();
$isint = $this->types[$name] == 'intarray';
foreach (explode(',', $data[$name]) as $v) {
if ($v = trim($v)) {
$value[] = $iisint ? (int) $v : $v;
}
}

$syncdata[$name] = $value;
}
break;

default:
$value = $data[$name];
}

return true;
}

public function set(tpost $post, $name, $value) {
if (!in_array($name, $this->allprops)) return false;
if (!isset($post->propdata[$this->dataname])) $this->load_item($post);
$data = &$post->propdata[$this->dataname];
    if (method_exists($this, $set = 'set' . $name))  {
$this->$set($data, $value);
return true;
}

switch ($this->types[$name]) {
case 'int':
$data[$name] = (int) $value;
break;

case 'bool':
$data[$name] = $value ? '1' : '0';
break;

case array':
case 'intarray':
if (!isset($post->syncdata[$this->dataname])) $post->syncdata[$this->dataname] = array();
$post->syncdata[$this->dataname][$name] = $value;
$data[$name] = implode(',', $value);
break;

default:
$data[$name] = $value;
}

return true;
}

public function load_item(tpost $post) {
if ($post->id == 0) {
$post->propdata[$this->dataname] = $this->defvalues;
} else {
//query items for loaded posts
$items = array();
foreach (tpost::$instances['post'] as $id => $post) {
if (!isset($post->propdata[$this->dataname])) $items[] = $id;
}
$list = implode(',', $items);
$db = litepublisher::$db;
if ($res = $db->query("select * from $db->prefix$this->table where id in($list)")) {
      while ($r = mysql_fetch_assoc($res)) {
$p = tpost::i((int) $r['id']);
$p->propdata[$this->dataname] = $r;
}
}

if (!isset($post->propdata[$this->dataname])) $this->error(sprintf('The "%d" post not found in"%s" table", $post->id, $db->prefix . $this->table));
}
}

public function add(tpost $post) {
$this->db->insert_a($post->propdata[$this->dataname]);
}

public function save(tpost $post) {
$this->db->updateassoc($post->propdata[$this->dataname]);
}

}//class