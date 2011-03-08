<?php

class tdebugproxy {
public static $trace;
public static $total;
public static $stat;
public $obj;

public function __construct($obj) {
$this->obj = $obj;
//echo get_class($obj), "<br>";
}

public function __get($name) {
$m = microtime(true);
$r = $this->obj->$name;
$this->addstat(" get $name", microtime(true) - $m);
return $r;
}

public function __set($name, $value) {
$m = microtime(true);
$this->obj->$name = $value;
$this->addstat(" set $name", microtime(true) - $m);
}

public function __call($name, $args) {
$m = microtime(true);
$r = call_user_func_array(array($this->obj, $name), $args);
$this->addstat(" call $name", microtime(true) - $m);
return $r;
}

public function addstat($s, $time) {
$name = get_class($this->obj) . $s;
echo "$name<br>";
self::$trace[] = array($name, $time);
if (isset(self::$total[$name])) {
self::$total[$name] += $time;
} else {
self::$total[$name] = $time;
}
}

public static function showperformance() {
arsort(self::$total);
foreach (self::$total as $k => $v) {
$v= round($v, 5);
echo "$k $v\n";
}
}

}