<?php

class tmediaparser extends TEventClass {

  static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'mediaprser';
  }

public function Add($filename) {
$result = array(  
'medium' => $this->getmedium($filename),
'mime' => 'application/octet-stream',
  'bitrate' => 0,
'framerateint' => 0,
'samplingrate' => '',
'channels' => 0,
'duration' => 0,
'height' => 0,
'width' => 0
);

$icons = ticons::instance();
$result['icon'] = $icon->getmedium($result['medium']);
switch ($result['medium']) {
case 'bin':
//$preview = $this->
break;

 case 'image':
				$info = getimagesize($filename);
$result['mime'] = $info['mime'];
$result['width'] = $info[0];
$result['height'] = $info[1];
$result['preview'] = $this->getsnapshot($filename);
break;

case 'audio':
$result['preview'] = $this->createaudioclip($filename);
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

return $result;
}

public function getmedium($filename) {
    $parts = pathinfo($filename);
$ext = $parts['extension'];
if (preg_match('/jpg|gif|bmp|png|/', $ext)) return 'image';
if (preg_match('/wav|mp3/', $ext)) return 'audio';
if (preg_match('//', $ext)) return 'video';
if (preg_match('//', $ext)) return 'video';
return 'bin';
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
    $parts = pathinfo($filename);
$preview = $parts['filename'] . '.preview.jpg';
if (!$this->createsnapshot($paths['files'] . $filename, $paths['files'] . $preview, $thisoptions->previewwidth, $thisoptions->previewheight)) return 0;

$files = tfiles::instance();
return $files->add($preview);
}

}//class
?>