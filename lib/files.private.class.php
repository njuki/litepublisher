<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tprivatefiles extends tevents {
public $id;
public $item;

  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'files.private';
  }

public function __get($name) {
if (isset($this->item[$name])) return $this->item[$name];
return parent::__get($name);
}

public function error500() {
}
  
  public function request($id) {
$files = tfiles::i();
if (!$files->itemexists($id)) return 404;
$item = $files->getitem($id);
$filename = '/files/' . $item['filename'];
if ($item['idperm'] == 0) {
if ($filename == litepublisher::$urlmap->url) return $this->error500();
return turlmap::redir301($filename);
}

$this->id = $id;
$this->item = $item;

$perm = tperm::i($item['idperm']);
$result = $perm->getheader($this);
$result .= sprintf('<?php %s::sendfile(%s); ?>', get_class($this), var_export($item, true));
return $result;
}

public static function sendfile(array $item) {
    if (ob_get_level()) ob_end_clean ();
		if (isset($_SERVER['HTTP_IF_NONE_MATCH']))
if ($item['hash'] == $_SERVER['HTTP_IF_NONE_MATCH']) {
header('HTTP/1.1 304 Not Modified', true, 304);
exit();
}
}

  if (!isset($_SERVER['HTTP_RANGE'])) {
    header('HTTP/1.1 200 OK', true, 200);
self::send($item, 0, $item['size']);
} else {
    $range = $_SERVER['HTTP_RANGE'];
    $range = str_replace('bytes=', '', $range);
    $range = str_replace('-', '', $range);
    header('HTTP/1.1 206 Partial Content', true, 206);
self::send($item,
}
}

public function send(array $item, $from, $size) {
$filename = basename($item['filename']);
$realfile = litepublisher::$paths->files . '/files/private/' . $filename;

  header('Content-type: ' . $item['mime']);
  header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$size);
  header('Content-Length: ' . $item['size']);
  header('Accept-Ranges: bytes');
        header('Etag: ' . $item['hash']);
  header('Last-Modified: ' . date('r', strtotime($item['posted'])));

    $fh = fopen($realfile, 'rb');
    fseek($fh, $from);
$bufsize = 1024 * 16;
    while(!feof($fh)) {
        set_time_limit(1);
        echo fread($fh, $bufsize);
        flush();
        //ob_flush();
    }
    fclose($fh);

exit();
}

}//class