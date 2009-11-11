<?php

class tsnapshot extends TEventClass {

  public static function instance() {
    return getinstance(__class__);
  }

protected function create() {
parent::create();
$this->basename = 'snapshot';
}

public function createsnapshot($srcfilename, $destfilename, $x, $y) {
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

			if ($x >= $sourcx) && ($y >= $sourcey)) return false;

$ratio = $sourcx / $sourcey;
if ($x/$y > $ratio) {
   $x = $y *$ratio;
} else {
   $y = $x /$ratio;
}

$dest = imagecreatetruecolor($x, $y);
imagecopyresampled($dest, $source, 0, 0, 0, 0, $x, $y, $sourcx, $sourcy);
imagejpeg($dest, $destfilename, 100);
imagedestroy($dest);
imagedestroy($source);
}

}//class

?>
