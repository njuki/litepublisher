<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmediaparser extends tevents {
  
  public   static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'mediaparser';
    $this->data['previewwidth'] = 120;
    $this->data['previewheight'] = 120;
    $this->data['audiosize'] = 128;
  }
  
  public function upload($filename, $content, $title, $overwrite ) {
    if ($title == '') $title = $filename;
    $linkgen = tlinkgenerator::instance();
    $filename = $linkgen->filterfilename($filename);
    if (preg_match('/\.(htm|html|php|phtml|php\d|htaccess)$/i', $filename)) $filename .= '.txt';
    $tempfilename = $this->doupload($filename, $content);
    return $this->addfile($filename, $tempfilename, $title, $overwrite);
  }
  
  public function uploadfile($filename, $tempfilename, $title, $overwrite ) {
    if ($title == '') $title = $filename;
    $linkgen = tlinkgenerator::instance();
    $filename = $linkgen->filterfilename($filename);
    if (preg_match('/\.(htm|html|php|phtml|php\d|htaccess)$/i', $filename)) $filename .= '.txt';
    $parts = pathinfo($filename);
    $newtemp = 'tmp.' . md5uniq() . '.' . $parts['filename'];
    $newtemp .= empty($parts['extension']) ? '' : '.' . $parts['extension'];
    if (!move_uploaded_file($tempfilename, litepublisher::$paths['files'] . $newtemp)) return $this->error("Error access to uploaded file");
    return $this->addfile($filename, $newtemp, $title, $overwrite);
  }
  
  public function uploadicon($filename, $content, $overwrite ) {
    $linkgen = tlinkgenerator::instance();
    $filename = $linkgen->filterfilename($filename);
    $tempfilename = $this->doupload($filename, $content, $overwrite);
    return $this->Addicon($filename, $tempfilename);
  }
  
  private function doupload($filename, &$content) {
    if (preg_match('/\.(htm|html|php|phtml|php\d|htaccess)$/i', $filename)) $filename .= '.txt';
    $parts = pathinfo($filename);
    $filename = 'tmp.' . md5uniq() . '.' . $parts['filename'] .(empty($parts['extension']) ? '' : '.' . $parts['extension']);
    if (@file_put_contents(litepublisher::$paths['files']. $filename, $content)) {
      @ chmod(litepublisher::$paths['files']. str_replace('/', DIRECTORY_SEPARATOR, $filename), 0666);
      return $filename;
    }
    return false;
  }
  
  private function getunique($dir, $filename) {
    if  (!@file_exists($dir . $filename)) return $filename;
    $parts = pathinfo($filename);
    $base = $parts['filename'];
    $ext = empty($parts['extension']) ? '' : ".$parts[extension]";
    for ($i = 2; $i < 10000; $i++) {
      $filename = "$base$i$ext";
      if  (!@file_exists($dir . $filename)) return $filename;
    }
    return $filename;
  }
  
  private function movetofolder($filename, $tempfilename, $media, $overwrite) {
    $dir = litepublisher::$paths['files'] . $media;
    if (!is_dir($dir)) {
      mkdir($dir, 0777);
      chmod($dir, 0777);
    }
    if ($media) $dir .= DIRECTORY_SEPARATOR;
    if (!$overwrite  )  $filename = $this->getunique($dir, $filename);
    if (!rename(litepublisher::$paths['files'] . $tempfilename, $dir . $filename)) return $this->error("Error rename file $tempfile to $dir$filename");
    return "$media/$filename";
  }
  /*
  public function add($filename, $tempfilename, $title) {
    return $this->addfile($filename, $tempfilename, $title, true);
  }
  */
  
  public function addfile($filename, $tempfilename, $title, $overwrite) {
    $info = $this->getinfo($tempfilename);
    $info['filename'] = $this->movetofolder($filename, $tempfilename, $info['media'], $overwrite);
    $item = $info + array(
    'filename' => $filename,
    'title' => $title,
    'description' => ''
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
      $preview['parent'] = $id;
      $idpreview = $files->additem($preview);
      
      $files->setvalue($id, 'preview', $idpreview);
    }
    $files->unlock();
    return $id;
  }
  
  public function addicon($filename) {
    $info = $this->getinfo($filename);
    if ($info['media'] != 'image') $this->error('Invalid icon file format '. $info['media']);
    $info['media'] = 'icon';
    $item = $info + array(
    'filename' => $filename,
    'title' => '',
    'description' => ''
    );
    
    $files = tfiles::instance();
    return $files->additem($item);
  }
  
  private function getdefaultvalues($filename) {
    return array(
    'parent' => 0,
    'preview' => 0,
    'media' => 'bin',
    'mime' => 'application/octet-stream',
    'filename' => $filename,
    'size' => 0,
    'icon' => 0,
    'bitrate' => 0,
    'framerate' => 0,
    'samplingrate' => '',
    'channels' => 0,
    'duration' => 0,
    'height' => 0,
    'width' => 0
    );
  }
  
  public function getinfo($filename) {
    $realfile = litepublisher::$paths['files']. str_replace('/', DIRECTORY_SEPARATOR, $filename);
    $result = $this->getdefaultvalues($filename);
    
    if ($info = getimagesize($realfile)) {
      $result['media'] = 'image';
      $result['mime'] = $info['mime'];
      $result['width'] = $info[0];
      $result['height'] = $info[1];
      return $result;
    }
    
    if (preg_match('/\.(mp3|wav)$/', $filename)) {
      $result['media'] = 'audio';
      $result['mime'] = preg_match('/\.mp3$/', $filename) ? 'audio/mpeg' : 'audio/x-wave';
      if ($info = $this->getaudioinfo($filename)) {
        $result['bitrate']  = $info['bitrate'];
        $result['samplingrate'] = $info['samplingrate'];
        $result['channels'] = $info['channels'];
        $result['duration'] = $info['duration'];
      }
      return $result;
    }
    
    
    return $result;
  }
  
  public function createpreview(array $info) {
    switch ($info['media']) {
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
    return true;
  }
  
  public function getsnapshot($filename) {
    $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
    $parts = pathinfo($filename);
    $destfilename = $parts['filename'] . '.preview.jpg';
    if (!empty($parts['dirname']) && ($parts['dirname'] != '.')) {
      $destfilename = $parts['dirname'] . DIRECTORY_SEPARATOR . $destfilename;
    }
    
    if (!$this->createsnapshot(litepublisher::$paths['files'] . $filename, litepublisher::$paths['files'] . $destfilename, $this->previewwidth, $this->previewheight)) return false;
    
    @chmod(litepublisher::$paths['files'] . $destfilename, 0666);
    $info = getimagesize(litepublisher::$paths['files']. $filename);
    $result = $this->getdefaultvalues(str_replace(DIRECTORY_SEPARATOR, '/', $destfilename));
    $result['media'] = 'image';
    $result['mime'] = $info['mime'];
    $result['width'] = $info[0];
    $result['height'] = $info[1];
    return $result;
  }
  
  public function createaudioclip($filename) {
    $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
    $parts = pathinfo($filename);
    $destfilename = sprintf('%s.preview.%s', $parts['filename'],$parts['extension']);
    if (!empty($parts['dirname'])) {
      $destfilename = $parts['dirname'] . DIRECTORY_SEPARATOR . $destfilename;
    }
    
    if ($fp = fopen(litepublisher::$paths['files'] . $filename, 'r')) {
      $content = fread($fp, 1024 * $this->audiosize);
      fclose($fp);
    }
    
    file_put_contents(litepublisher::$paths['files'] . $destfilename, $content);
    @chmod(litepublisher::$paths['files'] . $destfilename, 0666);
    $info = getimagesize(litepublisher::$paths['files']. $filename);
    $result = $this->getdefaultvalues(str_replace(DIRECTORY_SEPARATOR, '/', $destfilename));
    $result['media'] = 'audio';
    $result['mime'] = $info['mime'];
    return $result;
  }
  
  private function getaudioinfo($filename) {
    if (!class_exists('getID3')) return false;
    $realfile = litepublisher::$paths['files'] . str_replace('/', DIRECTORY_SEPARATOR, $filename);
    
    // Initialize getID3 engine
    $getID3 = new getID3;
    $getID3->option_md5_data        = true;
    $getID3->option_md5_data_source = true;
    $getID3->encoding               = 'UTF-8';
    
    $info = $getID3->analyze($realfile);
    if (isset($info['error'])) return false;
    
    $result = array (
    'bitrate'  => @$info['audio']['bitrate'],
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