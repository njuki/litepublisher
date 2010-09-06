<?php

class tadminyoutubeuploader {

  public static function instance() {
    return getinstance(__class__);
  }

  public function getcontent() {
    $plugin = tyoutubeuploader::instance();
$lang = $plugin->getlang();
    $args = targs::instance();
    $args->devkey = $plugin->devkey;
    $args->secret = $plugin->secret;
        $args->formtitle = $lang->optionstitle;
    $html = THtmlResource::instance();
$result = sprintf('<p>%s <a href="http://code.google.com/apis/accounts/docs/RegistrationForWebAppsAuto.html">http://code.google.com/apis/accounts/docs/RegistrationForWebAppsAuto.html</a></p>', $lang->registerapp);
    $result .= $html->adminform('[text:secret]', $args);
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