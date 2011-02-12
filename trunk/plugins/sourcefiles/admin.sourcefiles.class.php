<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminsourcefiles  {

  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
$plugin = tsourcefiles::instance();
    $html = tadminhtml::instance();
    $args = targs::instance();
$args->root = $plugin->root;
$args->formtitle = 'Source files option';
$args->data['$lang.root'] = 'Path to source files';
$result = $html->adminform('[text=root]', $args);

$result .= '<form name="rereadform" action="" method="post" >
 <p><input type="submit" name="reread" value="Reread"/></p>
 <p><input type="submit" name="download" value="Download and refresh"/></p>
</form>';

return $result;
  }
  
  public function processform() {
    $plugin = tsourcefiles::instance();
    if (isset($_POST['download'])) {
$version = litepublisher::$options->version;
    if (!($s = http::get("http://litepublisher.googlecode.com/files/litepublisher.$version.tar.gz")) &&
    !($s = http::get("http://litepublisher.com/download/litepublisher.$version.tar.gz") )) {
      return  "Error download"
    }

      tbackuper::include_tar();
      $tar = new tar();
      $tar->loadfromstring($s);
      if (!is_array($tar->files)) {
        unset($>tar);
        return 'Invalid file archive';
      }
      tfiler::delete($plugin->root, true, false);
      foreach ($tar->files as $item) {
$filename = $plugin->froot . $item['name'];
file_put_contents($filename, $item['file']);
@chmod($filename,0644);
      }

unset($tar);
$plugin->reread();
    } elseif (isset($_POST['reread'])) {
$plugin->reread();
} else {
$plugin->root = $_POSTT['root'];
$plugin->save();
}
}

}//class

