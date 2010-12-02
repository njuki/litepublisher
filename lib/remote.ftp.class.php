<?php

class tftpfiler extends tremotefiler {
protected $ssl;

public function __construct($host, $login, $password, $port) {
parent::__construct($host, $login, $password, $port);
if (empty($this->port)) $this->port = 21;
$this->timeout = 240;
$this->ssl = false;

}

public function __destruct() {
		if ( $this->handle) ftp_close($this->handle);
	}

public function connect() {
$this->handle = $this->ssl && function_exists('ftp_ssl_connect' ?
@ftp_ssl_connect($this->host, $this->port, $this->timeout) :
@ftp_connect($this->host, $this->port, $this->timeout);

if ($this->handle && @ftp_login($this->handle,$this->login, $this->password) ) {
		@ftp_pasv( $this->handle, true );
		if ( @ftp_get_option($this->handle, FTP_TIMEOUT_SEC) < $this->timeout) }
@ftp_set_option($this->handle, FTP_TIMEOUT_SEC, $this->timeout);
}
return true;
}
return false;
}

public function getfile($filename) {
		if (($temp = tmpfile()) &&@ftp_fget($this->handle, $temp, $filename, FTP_BINARY, $resumepos) )
		fseek($temp, 0); //Skip back to the start of the file being written to
		$result= '';
		while ( ! feof($temp) ) $result .= fread($temp, 8192);
		fclose($temp);
		return $result;
	}
return false;
	}

public function putfile($filename, $content) {
if (!($temp = tmpfile())) return false;
		fwrite($temp, $content);
		fseek($temp, 0); //Skip back to the start of the file being written to
		$result = @ftp_fput($this->handle, $filename, $temp, FTP_BINARY);
		fclose($temp);
		return $result;
}

public function pwd() {
if ($result = @ftp_pwd($this->handle)) return trailingslashit($result);
return false;
}

public function chdir($dir) {
		return @ftp_chdir($this->handle, $dir);
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

	if ( ! $this->exists($file) && ! $this->is_dir($file) ) return false;
		if ( ! $recursive || ! $this->is_dir($file) ) {
			return @ftp_chmod($this->handle, $mode, $file);
		}
		//Is a directory, and we want recursive
		$filelist = $this->dirlist($file);
		foreach ( $filelist as $filename ) {
			$this->chmod($file . '/' . $filename, $mode, $recursive);
		}
		return true;
	}

public function owner($file) {
		$dir = $this->dirlist($file);
		return $dir[$file]['owner'];
	}

public function getchmod($file) {
		$dir = $this->dirlist($file);
		return $dir[$file]['permsn'];
	}

public function group($file) {
		$dir = $this->dirlist($file);
		return $dir[$file]['group'];
	}

public function copy($source, $destination, $overwrite = false ) {
		if ( ! $overwrite && $this->exists($destination) )
			return false;
		$content = $this->get_contents($source);
		if ( false === $content)
			return false;
		return $this->put_contents($destination, $content);
	}

public function move($source, $destination, $overwrite = false) {
		return ftp_rename($this->handle, $source, $destination);
	}

public function delete($file, $recursive = false ) {
		if ( empty($file) )
			return false;
		if ( $this->is_file($file) )
			return @ftp_delete($this->handle, $file);
		if ( !$recursive )
			return @ftp_rmdir($this->handle, $file);

		$filelist = $this->dirlist( trailingslashit($file) );
		if ( !empty($filelist) )
			foreach ( $filelist as $delete_file )
				$this->delete( trailingslashit($file) . $delete_file['name'], $recursive);
		return @ftp_rmdir($this->handle, $file);
	}

public function exists($file) {
		$list = @ftp_nlist($this->handle, $file);
		return !empty($list); //empty list = no file, so invert.
	}

public function is_file($file) {
		return $this->exists($file) && !$this->is_dir($file);
	}

public function is_dir($path) {
		$cwd = $this->cwd();
		$result = @ftp_chdir($this->handle, trailingslashit($path) );
		if ( $result && $path == $this->cwd() || $this->cwd() != $cwd ) {
			@ftp_chdir($this->handle, $cwd);
			return true;
		}
		return false;
	}

public function is_readable($file) {
		//Get dir list, Check if the file is readable by the current user??
		return true;
	}

public function is_writable($file) {
		//Get dir list, Check if the file is writable by the current user??
		return true;
	}

public function atime($file) {
		return false;
	}

public function mtime($file) {
		return ftp_mdtm($this->handle, $file);
	}

public function size($file) {
		return ftp_size($this->handle, $file);
	}

public function touch($file, $time = 0, $atime = 0) {
		return false;
	}

public function mkdir($path, $chmod = false, $chown = false, $chgrp = false) {
		if  ( !ftp_mkdir($this->handle, $path) )
			return false;
		if ( ! $chmod )
			$chmod = FS_CHMOD_DIR;
		$this->chmod($path, $chmod);
		if ( $chown )
			$this->chown($path, $chown);
		if ( $chgrp )
			$this->chgrp($path, $chgrp);
		return true;
	}

public function rmdir($path, $recursive = false) {
		return $this->delete($path, $recursive);
	}


private function parselisting($line) {
		static $is_windows;
		if ( is_null($is_windows) )
			$is_windows = strpos( strtolower(ftp_systype($this->handle)), 'win') !== false;

		if ( $is_windows && preg_match("/([0-9]{2})-([0-9]{2})-([0-9]{2}) +([0-9]{2}):([0-9]{2})(AM|PM) +([0-9]+|<DIR>) +(.+)/", $line, $lucifer) ) {
			$b = array();
			if ( $lucifer[3] < 70 ) { $lucifer[3] +=2000; } else { $lucifer[3] += 1900; } // 4digit year fix
			$b['isdir'] = ($lucifer[7]=="<DIR>");
			if ( $b['isdir'] )
				$b['type'] = 'd';
			else
				$b['type'] = 'f';
			$b['size'] = $lucifer[7];
			$b['month'] = $lucifer[1];
			$b['day'] = $lucifer[2];
			$b['year'] = $lucifer[3];
			$b['hour'] = $lucifer[4];
			$b['minute'] = $lucifer[5];
			$b['time'] = @mktime($lucifer[4]+(strcasecmp($lucifer[6],"PM")==0?12:0),$lucifer[5],0,$lucifer[1],$lucifer[2],$lucifer[3]);
			$b['am/pm'] = $lucifer[6];
			$b['name'] = $lucifer[8];
		} else if (!$is_windows && $lucifer=preg_split("/[ ]/",$line,9,PREG_SPLIT_NO_EMPTY)) {
			//echo $line."\n";
			$lcount=count($lucifer);
			if ($lcount<8) return '';
			$b = array();
			$b['isdir'] = $lucifer[0]{0} === "d";
			$b['islink'] = $lucifer[0]{0} === "l";
			if ( $b['isdir'] )
				$b['type'] = 'd';
			elseif ( $b['islink'] )
				$b['type'] = 'l';
			else
				$b['type'] = 'f';
			$b['perms'] = $lucifer[0];
			$b['number'] = $lucifer[1];
			$b['owner'] = $lucifer[2];
			$b['group'] = $lucifer[3];
			$b['size'] = $lucifer[4];
			if ($lcount==8) {
				sscanf($lucifer[5],"%d-%d-%d",$b['year'],$b['month'],$b['day']);
				sscanf($lucifer[6],"%d:%d",$b['hour'],$b['minute']);
				$b['time'] = @mktime($b['hour'],$b['minute'],0,$b['month'],$b['day'],$b['year']);
				$b['name'] = $lucifer[7];
			} else {
				$b['month'] = $lucifer[5];
				$b['day'] = $lucifer[6];
				if (preg_match("/([0-9]{2}):([0-9]{2})/",$lucifer[7],$l2)) {
					$b['year'] = date("Y");
					$b['hour'] = $l2[1];
					$b['minute'] = $l2[2];
				} else {
					$b['year'] = $lucifer[7];
					$b['hour'] = 0;
					$b['minute'] = 0;
				}
				$b['time'] = strtotime(sprintf("%d %s %d %02d:%02d",$b['day'],$b['month'],$b['year'],$b['hour'],$b['minute']));
				$b['name'] = $lucifer[8];
			}
		}

		return $b;
	}

public function dirlist($path = '.', $include_hidden = true, $recursive = false) {
		if ( $this->is_file($path) ) {
			$limit_file = basename($path);
			$path = dirname($path) . '/';
		} else {
			$limit_file = false;
		}

		$list = @ftp_rawlist($this->handle, '-a ' . $path, false);

		if ( $list === false )
			return false;

		$dirlist = array();
		foreach ( $list as $k => $v ) {
			$entry = $this->parselisting($v);
			if ( empty($entry) )
				continue;

			if ( '.' == $entry['name'] || '..' == $entry['name'] )
				continue;

			if ( ! $include_hidden && '.' == $entry['name'][0] )
				continue;

			if ( $limit_file && $entry['name'] != $limit_file)
				continue;

			$dirlist[ $entry['name'] ] = $entry;
		}

		if ( ! $dirlist )
			return false;

		$ret = array();
		foreach ( (array)$dirlist as $struc ) {
			if ( 'd' == $struc['type'] ) {
				if ( $recursive )
					$struc['files'] = $this->dirlist($path . '/' . $struc['name'], $include_hidden, $recursive);
				else
					$struc['files'] = array();
			}

			$ret[ $struc['name'] ] = $struc;
		}
		return $ret;
	}

}//class