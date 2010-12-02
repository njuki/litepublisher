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

class tssh2filer extends tremotefiler {
protected $sftp;
protected $hostkey;
protected $public_key;
protected $private_key

public function connect() {
$this->handle = empty($this->key) ? 
@ssh2_connect($this->host, $this->port) :
 @ssh2_connect($this->host, $this->port, $this->hostkey);

if ($this->handle) {
$authresult = $this->public_key&& $this->private_key ?
@ssh2_auth_pubkey_file($this->handle, $this->login, $this->public_key, $this->private_key, $this->password) :
@ssh2_auth_password($this->handle, $this->login, $this->password);
if ($authresult) {
		$this->sftp = ssh2_sftp($this->handle);
return true;
}
}
return false;
}

public function run($cmd) {
		if ($h = ssh2_exec($this->handle, $cmd)){
			stream_set_blocking( $h, true );
			stream_set_timeout( $h, $this->timeout);
			$result = stream_get_contents( $h);
			fclose( $h);
				return $result;
		}
		return false;
	}

public function runbool($cmd) {
if ($result = $this->run($cmd)) return  trim($result) != '';
return false;

public function getfile($filename) {
		return file_get_contents('ssh2.sftp://' . $this->sftp . '/' . ltrim($filename, '/'));
	}

public function putfile($filename, $content) {
		return false !== file_put_contents('ssh2.sftp://' . $this->sftp . '/' . ltrim($filename, '/'), $content);
}

public function pwd() {
if ($result = $this->run('pwd')) return trailingslashit($result);
return false;
}

public function chdir($dir) {
		return $this->runbool('cd ' . $dir);
	}

protected function runcommand($cmd, $filename, $mode, $recursive) {
		if ( ! $this->exists($filename) ) return false;
if ($recursive && $this->is_dir($filename)) $cmd .= ' -R';
return $this->runbool(sprintf('%s %o %s', $cmd, $mode, escapeshellarg($filename));
	}

public function chgrp($filename, $group, $recursive ) {
return $this->runcommand('chgrp', $filename, $group, $recursive);
	}

public function chmod($file, $mode, $recursive ) {
		if ( ! $mode ) {
			if ( $this->is_file($file) )
				$mode = $this->chmod_file;
			elseif ( $this->is_dir($file) )
				$mode = $this->chmod_dir;
			else
				return false;
		}

return $this->runcommand('chmod', $filename, $mode, $recursive);
	}

public function  chown($filename, $owner, $recursive ) {
return $this->runcommand('chown ', $filename, $owner, $recursive);
	}


protected function get_name($filename, $name) {
$func = $name == 'owner' ? 'fileowner' : 'filegroup';
		if ($result = @$func('ssh2.sftp://' . $this->sftp . '/' . ltrim($filename, '/'))) {
$func = $name == 'owner' ? 'posix_getpwuid' : 'posix_getgrgid';
		if (function_exists($func) ){
		$a = $func($result);
		return $a['name'];
}
return $result;
}
return false;
}

public function owner($file) {
return $this->get_name($filename, 'owner');
	}

public function group($file) {
return $this->get_name($filename, 'group');
}

public function getchmod($file) {
		return substr(decoct(@fileperms( 'ssh2.sftp://' . $this->sftp . '/' . ltrim($file, '/') )),3);
	}

public function copy($src, $dst, $overwrite = false ) {
		if( ! $overwrite && $this->exists($dst) ) return false;
if ($s = $this->getfile($src)) return $this->putfile($dst, $s);
return false;
	}

public function move($source, $destination, $overwrite = false) {
		return @ssh2_sftp_rename($this->handle, $source, $destination);
	}

public function delete($file, $recursive = false) {
		if ( $this->is_file($file)  return ssh2_sftp_unlink($this->sftp, $file);
		if ( ! $recursive ) return ssh2_sftp_rmdir($this->sftp, $file);
		$filelist = $this->dirlist($file);
		if ( is_array($filelist) ) {
			foreach ( $filelist as $filename => $fileinfo) {
				$this->delete($file . '/' . $filename, $recursive);
			}
		}
		return ssh2_sftp_rmdir($this->sftp, $file);
	}

private function filefunc($filename, $func) {
		return $func('ssh2.sftp://' . $this->sftp . '/' . ltrim($filename, '/'));
}

public function exists($file) {
		return $this->filefunc($file, 'file_exists');
	}

public function is_file($file) {
		return $this->filefunc($file, 'is_file');
	}

public function is_dir($path) {		$path = ltrim($path, '/');
		return $this->filefunc($file, 'is_dir');
	}

public function is_readable($file) {
		return $this->filefunc($file, 'is_readable');
	}

public function is_writable($file) {
		return $this->filefunc($file, 'is_writable');
	}

public function atime($file) {
		return $this->filefunc($file, 'fileatime');
	}

public function mtime($file) {
		return $this->filefunc($file, 'filemtime');
	}

public function size($file) {
		return $this->filefunc($file, 'filesize');
	}

public function mkdir($path, $chmod = false, $chown = false, $chgrp = false) {
		$path = untrailingslashit($path);
		if ( ! $chmod ) $chmod = $this->chmod_dir;
		if ( ssh2_sftp_mkdir($this->sftp, $path, $chmod, true)) {
		if ( $chown ) $this->chown($path, $chown);
		if ( $chgrp ) $this->chgrp($path, $chgrp);
		return true;
}
return  false;
	}

public  function dirlist($path, $include_hidden = true, $recursive = false) {
		if ( $this->is_file($path) ) {
			$startfile = basename($path);
			$path = dirname($path);
		} else {
			$startfile = false;
		}

		if (!  $this->is_dir($path) )  return false;
		$result = array();
if ($dir = @dir('ssh2.sftp://' . $this->sftp .'/' . ltrim($path, '/') )) {
		while (false !== ($name = $dir->read()) ) {
if (($name == '.') || ($name == '..')) continue;
			if ( ! $include_hidden && '.' == $name[0] ) continue;
			if ( $startfile && $name != $startfile) continue;
$a = array();
$fullname = $path.'/'.$name);
			$a['perms'] 	= $this->gethchmod($fullname);
			$a['permsn']	= $this->getnumchmodfromh($a['perms']);
			$a['number'] 	= false;
			$a['owner']    	= $this->owner($fullname);
			$a['group']    	= $this->group($fullname);
			$a['size']    	= $this->size($fullname);
			$a['lastmodunix']= $this->mtime($fullname);
			$a['lastmod']   = date('M j',$a['lastmodunix']);
			$a['time']    	= date('h:i:s',$a['lastmodunix']);
if ($this->is_dir($fullname)) {
			$a['type']		= 'f';
}else {
			$a['type']		= 'd';
					$a['files'] = $recursive  ? $a['files'] = $this->dirlist($fullname, $include_hidden, $recursive) : array();
			}

			$result[ $name ] = $a;
		}
		$dir->close();
		unset($dir);
		return $result;
}
return false;
}

}//class

class tftpfiler extends tremotefiler {
}

class tsocketfiler extends tremotefiler {
}

}