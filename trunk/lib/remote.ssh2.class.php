<?php

class tssh2filer extends tremotefiler {
protected $sftp;
protected $hostkey;
protected $public_key;
protected $private_key;

public function __construct($host, $login, $password, $port) {
parent::__construct($host, $login, $password, $port);
if (empty($this->port)) $this->port = 22;
$this->ssl = false;
$this->hostkey = false;
}

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

private function run($cmd) {
		if ($h = ssh2_exec($this->handle, $cmd)){
			stream_set_blocking( $h, true );
			stream_set_timeout( $h, $this->timeout);
			$result = stream_get_contents( $h);
			fclose( $h);
				return $result;
		}
		return false;
	}

private function runbool($cmd) {
if ($result = $this->run($cmd)) return  trim($result) != '';
return false;
}

private function getfilename($file) {
return 'ssh2.sftp://' . $this->sftp . '/' . ltrim($filename, '/');
}

public function getfile($filename) {
		return file_get_contents($this->getfilename($filename));
	}

public function putfile($filename, $content) {
		return file_put_contents($this->getfilename($filename), $content) !== false;
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
return $this->runbool(sprintf('%s %o %s', $cmd, $mode, escapeshellarg($filename)));
	}

public function chgrp($filename, $group, $recursive ) {
return $this->runcommand('chgrp', $filename, $group, $recursive);
	}

public function chmod($file, $mode, $recursive ) {
if (!$mode && !($mode = $this->getmode($mode))) return false;
return $this->runcommand('chmod', $filename, $mode, $recursive);
	}

public function  chown($filename, $owner, $recursive ) {
return $this->runcommand('chown ', $filename, $owner, $recursive);
	}

public function owner($file) {
return $this->getownername(@fileowner($this->
$file));
	}

public function group($file) {
return $this->getgroupname(@filegroup($file));
}

public function getchmod($file) {
		return substr(decoct(@fileperms($this->getfilename($file) )),3);
	}

public function move($source, $destination, $overwrite = false) {
		return @ssh2_sftp_rename($this->handle, $source, $destination);
	}

public function delete($file, $recursive = false) {
		if ( $this->is_file($file))  return ssh2_sftp_unlink($this->sftp, $file);
		if ( ! $recursive ) return ssh2_sftp_rmdir($this->sftp, $file);
		$filelist = $this->dirlist($file);
		if ( is_array($filelist) ) {
			foreach ( $filelist as $filename => $fileinfo) {
				$this->delete($file . '/' . $filename, $recursive);
			}
		}
		return ssh2_sftp_rmdir($this->sftp, $file);
	}

public function exists($file) {
		return file_exists($this->getfilename($file));
	}

public function is_file($file) {
		return is_file($this->getfilename($file));
	}

public function is_dir($path) {		$path = ltrim($path, '/');
		return is_dir($this->getfilename($file));
	}

public function is_readable($file) {
		return is_readable($this->getfilename($file));
	}

public function is_writable($file) {
		return is_writable($this->getfilename($file));
	}

public function atime($file) {
		return fileatime($this->getfilename($file));
	}

public function mtime($file) {
		return filemtime($this->getfilename($file));
	}

public function size($file) {
		return filesize($this->getfilename($file));
	}

public function mkdir($path, $chmod = false, $chown = false, $chgrp = false) {
		$path = untrailingslashit($path);
		if ( ! $chmod ) $chmod = $this->chmod_dir;
		if ( !ssh2_sftp_mkdir($this->sftp, $path, $chmod, true)) return false;
		if ( $chown ) $this->chown($path, $chown);
		if ( $chgrp ) $this->chgrp($path, $chgrp);
		return true;
	}

public  function dirlist($path, $include_hidden = true, $recursive = false) {
		if ( $this->is_file($path) ) {
			$base = basename($path);
			$path = dirname($path);
		} else {
			$base = false;
		}

		if (!  $this->is_dir($path) )  return false;
		$result = array();
if ($dir = @dir($this->getfilename($path))) {
		while (false !== ($name = $dir->read()) ) {
if (($name == '.') || ($name == '..')) continue;
			if ( ! $include_hidden && '.' == $name[0] ) continue;
			if ( $base && $name != $base) continue;
$fullname = $path.'/'.$name;
$a = $this->getfileinfo($fullname);
$a['name'] = $name;
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

