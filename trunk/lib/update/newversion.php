<?php
class tdatafile  extends tdata {
public $dir;

  public static function instance() {
    return getinstance(__class__);
  }

public function load($name) {
    $filename = $this->dir . $name . '.php';
    if (file_exists($filename)) {
      return $this->loadfromstring(self::uncomment_php(file_get_contents($filename)));
    }
}

}//class

$data = tdatafile::instance();
$data->dir = litepublisher::$paths->home . 'data' . DIRECTORY_SEPARATOR . 'old' . DIRECTORY_SEPARATOR;

function migrateoptions() {
global $data;
$data->load('options');
$options = litepublisher::$options;
    $options->lock();
    $options->email = $data->email;
    $options->name = $data->name;
    $options->description  = $data->description;

    $options->timezone  = $data->timezone;
    $options->dateformat  = $data->dateformat;
    $options->keywords = $data->keywords;
   
    $options->mailer  = $data->mailer;
    $options->cache = $data->CacheEnabled;
    $options->expiredcache = $data->CacheExpired;
    $options->perpage = $data->postsperpage;
    $options->DefaultCommentStatus  = $data->DefaultCommentStatus ;
    $options->commentsdisabled  = $data->commentsdisabled ;
    $options->commentsenabled  = $data->commentsenabled ;
    $options->pingenabled  = $data->pingenabled ;
    $options->commentpages  = $data->commentpages ;
    $options->commentsperpage  = $data->commentsperpage ;

    $options->echoexception  = $data->echoexception ;
    
    $options->unlock();
}

?>