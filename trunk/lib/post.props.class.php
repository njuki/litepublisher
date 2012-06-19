class tpostprops extends tdata {
public $dataname;
public $defvalues;

protected function create() {
parent::create();
$this->dataname = 'postprops';
$this->table = 'posts';
}

public function get(tpost $post, $name, &$value) {
if (!isset($post->data[$this->dataname])) $this->request_item($post);
$data = &$post->data[$this->dataname];
    if (method_exists($this, $get = 'get' . $name)) {
$value = $this->$get($data);
return true;
}

if (array_key_exists($name, $data)) {
$value = $data[$name];
return true;
}

return false;
}

public function set(tpost $post, $name, $value) {
if (!isset($post->data[$this->dataname])) $this->request_item($post);
$data = &$post->data[$this->dataname];
    if (method_exists($this, $set = 'set' . $name))  {
$this->$set($data, $value);
return true;
}

if (array_key_exists($name, $data)) {
$value = $data[$name];
return true;
}

return false;
}

public function request_item(tpost $post) {
if ($post->id == 0) {
$post->data[$this->dataname] = $this->defvalues;
} else {
//query items for loaded posts
$items = array();
foreach (tpost::$instances['post'] as $id => $post) {
if (!isset($post->data[$this->dataname])) $items[] = $id;
}
$list = implode(',', $items);
$db = litepublisher::$db;
if ($res = $db->query("select * from $db->prefix$this->table where id in($list)")) {
      while ($r = mysql_fetch_assoc($res)) {
$post = tpost::i((int) $r['id']);
$post->data[$this->dataname] = $r;
}
}
}
}

public function save(tpost $post) {
$this->db->updateassoc($post->data[$this->dataname]);
}

}//class