<?php

class tadminyoutube {

  public static function instance() {
    return getinstance(__class__);
  }

  public function getcontent() {
tlocal::load(dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . litepublisher::$options->languages . '.youtube');
$lang = tlocal::instance('youtube');
    $plugin = tyoutube::instance();
    $args = targs::instance();
    $args->devkey = $plugin->devkey;
    $args->secret = $plugin->secret;
        $args->formtitle = $lang->optionstitle;
    $tml = '[text:devkey] [text:secret]';
    $html = THtmlResource::instance();
    $result = $html->adminform($tml, $args);
$result .= '<p><a href="' . litepublisher::$options->url . '/admin/youtube/getrequest.htm">' . $lang->getrequest . '</a></p>';
return $result;
  }
  
  public function processform() {
    extract($_POST, EXTR_SKIP);
    $plugin = tyoutube::instance();
    $plugin->devkey = $devkey;
$plugin->secret = $secret;
$plugin->save();
  }
  
}//class
?>