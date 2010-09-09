class tmigratedata extends tdata {
public static $dir;

public function load($name) {
$this->data = array();
$filename = self::$dir . $name . '.php';
    if (file_exists($filename)) {
      return $this->loadfromstring(self::uncomment_php(file_get_contents($filename)));
    }

}

}//class