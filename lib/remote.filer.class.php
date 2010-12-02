<?php

class tremotefiler {
protected $host;
protected $login;
protected $password;
protected $port;
protected $handle;
protected $timeout;
public $chmod_file;
public $chmod_dir;

public function __construct($host, $login, $password, $port) {
$this->host = $host;
$this->login = $login;
$this->password = $password;
$this->port = $port;
$this->handle= null;
$this->timeout = 10;
$this->chmod_file = 0666;
$this->chmod_dir = 0777;
}

public static function getprefered() {
if (extension_loaded('ssh2') && function_exists('stream_get_contents') ) return 'ssh2';
if (extension_loaded('ftp')) return 'ftp';
if (extension_loaded('sockets') || function_exists('fsockopen')) return 'socket';
return false;
}

}//class

