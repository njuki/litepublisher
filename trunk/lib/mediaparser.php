<?php

class tmediaparser extends TEventClass {

  static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'mediaparser';
  }

  public function upload($filename, $content, $title, $overwrite = true) {
    if ($title == '') $title = $filename;
    $linkgen = tlinkgenerator::instance();
    $filename = $linkgen->filterfilename($filename);
    $filename = $this->doupload($filename, $content, $overwrite);
    return $this->Add($filename, $title);
  }

   public function getunique($filename) {
global $paths;
$filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
    $parts = pathinfo($filename);
$base = $parts['filename'];
if (!empty($parts['dirname'])) {
$subdir = $paths['files'] . $parts['dirname'];
if (!is_dir($subdir)) {
@mkdir($subdir, 0777);
@chmod($subdir, 0777);
}
$base = $parts['dirname'] . DIRECTORY_SEPARATOR . $base;
}
    $ext = empty($parts['extension']) ? '' : ".$parts[extension]";
    for ($i = 2; $i < 10000; $i++) {
      $filename = "$base$i$ext";
      if  (!@file_exists($paths['files'] . $filename)) return str_replace(DIRECTORY_SEPARATOR, '/', $filename);
    }
    return $filename;
  }
  
  private function doupload($filename, &$content, $overwrite) {
    global $paths;
    if (!$overwrite) $filename = $this->getunique($filename);
        if (@file_put_contents($paths['files']. str_replace('/', DIRECTORY_SEPARATOR, $filename), $content)) {
      @ chmod($paths['files']. str_replace('/', DIRECTORY_SEPARATOR, $filename), 0666);
      return $filename;
    } else {
      return false;
  }
  
public function Add($filename, $title) { 
$info = $this->getinfo($filename);
$item = $info + array(
'parent' => 0,
'preview' => 0,
'filename' => $filename,
'title' => $title,
'description' => $description
);

$files = tfiles::instance();
$files->lock();
$id = $files->additem($item);
if ($preview = $this->createpreview($info)) {
$preview = $preview + array(
'parent' => $id,
'preview' => 0,
'filename' => $filename,
'title' => $title,
'description' => ''
);
$idpreview = $files->additem($preview);
$files->setvalue($id, 'preview', $idpreview);
}
$files->unlock();
return $id;
}


private function getdefaultvalues($filename) {
return array(  
'parent' => 0,
'preview' => 0,
'medium' => 'bin',
'mime' => 'application/octet-stream',
'filename' => $filename,
'icon' => 0,
  'bitrate' => 0,
'framerate' => 0,
'samplingrate' => '',
'channels' => 0,
'duration' => 0,
'height' => 0,
'width' => 0,
'lang' => ''
);
}

public function getinfo($filename) {
global $paths;
$realfile = $paths['files'], str_replace('/', DIRECTORY_SEPARATOR, $filename;
$result = $this->getdefaultvalues($filename);
				if ($info = getimagesize($realfile)) {
$result['medium'] = 'image';
$result['mime'] = $info['mime'];
$result['width'] = $info[0];
$result['height'] = $info['1];
return $result;
}

if (preg_match('/\.(mp3|wav)$/', $filename) && ($info = $this->getaudioinfo($filename))) {
$result['medium'] = 'audio';
$result['mime'] = preg_match('/\.mp3$/', $filename) ? 'audio/mpeg' : 'audio/x-wave';

$result['bitrate']  = $info['bitrate'];
$result['samplingrate'] = $info['samplingrate'];
$result['channels'] = $info['channels'];
$result['duration'] = $info['duration'];
return $result;
}

}

public function createpreview(array $info) }
switch ($info['medium']) {
 case 'image':
return $this->getsnapshot($info['filename']);
break;

case 'audio':
return $this->createaudioclip($info['filename']);
break;

case 'video':
$result['preview'] = $this->getvideopreview($filename);
break;

case 'document':
break;

case 'executable':
break;

case 'text':
break;

case 'archive':
break;
}

return false;
}

private function createsnapshot($srcfilename, $destfilename, $x, $y) {
if (!file_exists($srcfilename)) return false;
				$info = getimagesize($srcfilename);
				switch ($info[2]) {
					case 1:
						$source = @imagecreatefromgif($srcfilename);
						break;

					case 2:
						$source = @imagecreatefromjpeg($srcfilename);
						break;

					case 3:
						$source = @imagecreatefrompng($srcfilename);
						break;

/*
4 IMAGETYPE_SWF 
5 IMAGETYPE_PSD 
6 IMAGETYPE_BMP 
7 IMAGETYPE_TIFF_II (intel byte order) 
8 IMAGETYPE_TIFF_MM (motorola byte order)  
9 IMAGETYPE_JPC 
10 IMAGETYPE_JP2 
11 IMAGETYPE_JPX 
12  IMAGETYPE_JB2 
13 IMAGETYPE_SWC 
14 IMAGETYPE_IFF 
*/
case 15:
$source = @imagecreatefromwbmp($srcfilename);
break;

case 16:
$source = @imagecreatefromxbm($srcfilename);
break;

					default:
						return false;
				}					

		$sourcex = imagesx($source);
		$sourcey = imagesy($source);
			if (($x >= $sourcex) && ($y >= $sourcey)) return false;
$ratio = $sourcex / $sourcey;
if ($x/$y > $ratio) {
   $x = $y *$ratio;
} else {
   $y = $x /$ratio;
}

$dest = imagecreatetruecolor($x, $y);
imagecopyresampled($dest, $source, 0, 0, 0, 0, $x, $y, $sourcex, $sourcey);
imagejpeg($dest, $destfilename, 100);
imagedestroy($dest);
imagedestroy($source);
}

public function getsnapshot($filename) {
global $paths;
$thisoptions = $this->options;
$filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
    $parts = pathinfo($filename);
$destfilename = $parts['filename'] . '.preview.jpg';
if (!empty($parts['dirname'])) {
$destfilename = $parts['dirname'] . DIRECTORY_SEPARATOR . $destfilename;
}

if (!$this->createsnapshot($paths['files'] . $filename, $paths['files'] . $pdestfilename, $thisoptions->previewwidth, $thisoptions->previewheight)) return false;

@chmod($paths['files'] . $destfilename, 0666);
$info = getimagesize($paths['files']. $filename);
$result = $this->getdefaultvalues(str_replace(DIRECTORY_SEPARATOR, '/', $destfilename));
$result['medium'] = 'image';
$result['mime'] = $info['mime'];
$result['width'] = $info[0];
$result['height'] = $info['1];
return $result;
}

public function createaudioclip($filename) {
global $paths;
$thisoptions = $this->options;
$filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
    $parts = pathinfo($filename);
$destfilename = $parts['filename'] . '.preview.' . $parts['extension'];
if (!empty($parts['dirname'])) {
$destfilename = $parts['dirname'] . DIRECTORY_SEPARATOR . $destfilename;
}

if ($fp = fopen($paths['files'] . $filename, 'r')) {
$content = fread($fp, 1024 * $this->options->audiosize);
fclose($fp);
}

file_put_contents($paths['files'] . $destfilename, $content);
@chmod($paths['files'] . $destfilename, 0666);
$info = getimagesize($paths['files']. $filename);
$result = $this->getdefaultvalues(str_replace(DIRECTORY_SEPARATOR, '/', $destfilename));
$result['medium'] = 'audio';
$result['mime'] = $info['mime'];
return $result;
}

private function getaudioinfo($filename) {
global $paths;
$realfile = $paths['files'] . str_replace('/', DIRECTORY_SEPARATOR, $filename);

		// Initialize getID3 engine
		$getID3 = new getID3;
		$getID3->option_md5_data        = true;
		$getID3->option_md5_data_source = true;
		$getID3->encoding               = 'UTF-8';

		$info = $getID3->analyze($realfile);
		if (isset($info['error'])) return false;

		$result = array (
'bitrate'  => @$info['audio']['bitrate']
'samplingrate'  => @$info['audio']['sample_rate'],
'channels'  => @$info['audio']['channels'],
'duration'  => @$info['playtime_seconds'],
);
		//$result['tags']            = @$info['tags'];
		//$result['comments']        = @$info['comments'];
return $result;
}

}//class
?>