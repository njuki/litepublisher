class tpostprops {
public $dataname;

public function __construct() {
$this->dataname = 'postprops';
}

public function get(tpost $post, $name, &$value) {
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

}//class