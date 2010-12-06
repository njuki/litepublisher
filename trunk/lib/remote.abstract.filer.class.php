<?php

class tremotefiler {
protected $host;
protected $login;
protected $password;
public $port;
protected $handle;
protected $timeout;
public $chmod_file;
public $chmod_dir;

public function __construct() {
$this->port = 21;
$this->handle= null;
$this->timeout = 30;
$this->chmod_file = 0644;
$this->chmod_dir = 0755 ;
}

public function connect($host, $login, $password) {
if (empty($host) || empty($login) || empty($password)) return false;
$this->host = $host;
$this->login = $login;
$this->password = $password;
}

protected function getmode($mode) {
if ($mode) return $mode;
			if ( $this->is_file($file) )  return $this->chmod_file;
if ( $this->is_dir($file) ) return $this->chmod_dir;
				return false;
}

public static function getownername($owner) {
		if ($owner&& function_exists('posix_getpwuid') ) {
		$a = posix_getpwuid($owner);
		return $a['name'];
}
return  $owner;
}

protected function getgroupname($group) {
		if ($group && function_exists('posix_getgrgid') ) {
		$a = posix_getgrgid($group);
		return $a['name'];
	}
return $group;
	}

public function copy($src, $dst, $overwrite = false ) {
		if( ! $overwrite && $this->exists($dst) ) return false;
if (false === ($s = $this->getfile($src))) return false;
 return $this->putfile($dst, $s);
}

public function move($source, $destination, $overwrite = false) {
		if ( $this->copy($source, $destination, $overwrite) && $this->exists($destination) ) {
			$this->delete($source);
			return true;
}
			return false;
	}

public function mkdir($path, $chmod = false, $chown = false, $chgrp = false) {
		if ( ! $chmod ) $chmod = $this->chmod_dir;
		$this->chmod($path, $chmod);
		if ( $chown ) $this->chown($path, $chown);
		if ( $chgrp ) $this->chgrp($path, $chgrp);
		return true;
	}

protected function getfileinfo($filename) {
$result = array();
			$result['mode'] 	= $this->getchmod($filename);
			$result['owner']    	= $this->owner($filename);
			$result['group']    	= $this->group($filename);
			$result['size']    	= $this->size($filename);
			$result['time']= $this->mtime($filename);
			$result['type']		= $this->is_dir($filename) ? 'd' : 'f';
return $result;
}

public function each($dir, $func, $args) {
$dir = rtrim($dir, '/');
if ($list = $this->getdir($dir)) {
$call = array($this, $func);
if (!is_array($args)) {
$args = isset($args) ? array(0 => $args) : array();
}
  array_unshift($args, 0);
foreach ($list as $name => $item) {
$args[0] = $dir . '/' . $name;
call_user_func_array($call, $args);
}
}
}

}//