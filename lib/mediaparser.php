<?php

class tmediaparser extends TEventClass {


    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'mediaprser';
  }
  
public function Add($filename) {
$result = array();
$result['mediatype'] = $this->getmediatype($filename);
$icons = ticons::instance();
$result['icon'] = $icon->getmedia($result['mediatype']);
switch ($result['mediatype']) {
case 'bin':
//$preview = $this->
break;

 case 'image':
$result[preview'] = $this->getsnapshot($filename);
break;

case 'audio':
$result['preview'] = $this->createaudioclip($filename);
break;

case 'video':
$result['preview'] = $this->getvideopreview($filename);
break;
}

return $result;
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

$images = timages::instance();
return $images->add($preview);
}

}//class
?>