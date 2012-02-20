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

public function setperm($id, $idperm) {
$files = tfiles::i();
$item = $files->getitem($id);
dumpvar($item);
if ($idperm == $item['idperm']) return;
$files->setvalue($id, 'idperm', $idperm);
$filename = basename($item['filename']);
$path = litepublisher::$paths->files;
if ($idperm) {
rename($path . $item['filename'], $path . 'private/' . $filename);
litepublisher::$urlmap->add('/files/' . $item['filename'], get_class($this), $id);
} else {
litepublisher::$urlmap->delete('/files/' . $item['filename']);
rename($path . 'private/' . $filename, $path . $item['filename']);
}

if ($item['preview'] > 0) $this->setperm($item['preview'], $idperm);
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
//die(htmlspecialchars($result));
return $result;
}

public static function sendfile(array $item) {
    if (ob_get_level()) ob_end_clean ();
		if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
if ($item['hash'] == $_SERVER['HTTP_IF_NONE_MATCH']) {
header('HTTP/1.1 304 Not Modified', true, 304);
exit();
}
}

  if (!isset($_SERVER['HTTP_RANGE'])) {
    header('HTTP/1.1 200 OK', true, 200);
self::send($item, 0, $item['size'] - 1);
} else {
    list($unit, $ranges) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            list($range, $extra) = explode(',', $ranges, 2);
list($from, $end) = explode('-', $range, 2);

    $end= empty($end) ? $item['size'] - 1 : min(abs(intval($end)),$item['size'] - 1);
    $from = empty($from) || ($end < abs(intval($from))) ? 0 : max(abs(intval($from)),0);

    header('HTTP/1.1 206 Partial Content', true, 206);
        header("Content-Range: bytes $from-$end/" .$item['size']);
self::send($item, $from, $end);
}
}

private static function send(array $item, $from, $end) {
$filename = basename($item['filename']);
$realfile = litepublisher::$paths->files . 'private' . DIRECTORY_SEPARATOR. $filename;

  header('Content-type: ' . $item['mime']);
  header('Content-Disposition: attachment; filename=' . $filename);
  header('Content-Length: ' . $end - $from + 1);
  header('Accept-Ranges: bytes');
        header('Etag: ' . $item['hash']);
  header('Last-Modified: ' . date('r', strtotime($item['posted'])));

if ($fh = fopen($realfile, 'rb')) {
    fseek($fh, $from);
$bufsize = 1024 * 16;
    while(!feof($fh) && ($from <= $end)) {
        set_time_limit(1);
$s = fread($fh, min($bufsize, $end - $from));
$from += strlen($s);
        echo $s;
        flush();
        //ob_flush();
    }
    fclose($fh);
}

exit();
}

}//class