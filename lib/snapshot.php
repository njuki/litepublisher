<?php

class tsnapshot extends TEventClass {

  public static function instance() {
    return getinstance(__class__);
  }

protected function create() {
parent::create();
$this->basename = 'snapshot';
}

public function getsnapshot($filename) {
if (!file_exists($filename)) return false;
$thisoptions = $this->options;
				$info = getimagesize($filename);
				switch ($info[2]) {
					case 1:
						$source = @imagecreatefromgif($filename);
						break;

					case 2:
						$source = @imagecreatefromjpeg($filename);
						break;

					case 3:
						$source = @imagecreatefrompng($filename);
						break;

					default:
						return false;
				}					

		$image_width = imagesx($tmp_image);
		$image_height = imagesy($tmp_image);
			if ($image_width > $this->Width && $image_height > $this->Height) {
//return original without changes
}
				$w_ratio = $this->Width / ($image_width * ($this->ResizeScale / 100));
		//width ratio.. ie: 0.1 = 80 / 800
				$h_ratio = $this->Height / ($image_height * ($this->ResizeScale / 100));
	//height ratio. ie: 0.075 = 60 / 800
				$maxwidth = $this->Width;	//maxwidth is the max width of the final snapshot
				$maxheight = $this->Height;	//maxheight is the max height of the final snapshot
				if ($w_ratio < $h_ratio) {
					$maxheight = ceil($image_height * $h_ratio);
					$maxwidth = ceil($image_width * $h_ratio);
				} else {
					$maxwidth = ceil($image_width * $w_ratio);
					$maxheight = ceil($image_height * $w_ratio);
				}

				$final_width = $maxwidth;
				$final_height = $maxheight;

				imagecopyresampled($tmp_image, $tmp_image,0,0,0,0, $final_width, $final_height, $image_width, $image_height);
				
				$image_width = $final_width;
				$image_height = $final_height;

		if ($image_width < $this->Width) {
			$this->Width = $image_width;
		}
		if ($image_height < $this->Height) {
			$this->Height = $image_height;
		}
		$new_photo = imagecreatetruecolor($this->Width,$this->Height);
		
		$this->ReturnedWidth = $this->Width;
		$this->ReturnedHeight = $this->Height;

		imagecopyresampled($new_photo, $tmp_image,0,0,$source_x,$source_y, $this->Width, $this->Height, $this->Width, $this->Height);
			imagejpeg($new_photo,$destfilename)
imagedestroy();
}

}//class

?>
