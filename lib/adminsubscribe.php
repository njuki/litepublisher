<?php

class TAdminSubscribe extends TAdminPage {

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->table = 'subscribe';
    $this->basename = 'subscribe';
  }
 
  public function Auth() { }
    public function getmenu() { return ''; }

  public function getcontent() {
    global $options;
    $html= THtmlResource::instance();
    $html->section = $this->basename;
    $lang = tlocal::instance();

    $comusers = tcomusers::instance();
    if (!($user = $comusers->GetItemFromCookie($_GET['userid']))) return $this->notfount();
$subscribers=  tsubscribers::instance();
$items = $subscribers->getitems($user['id']);
      if (count($items) == 0) return $html->nosubscribtions();
        $email = $user['email'];
        eval('$result .="'. $html->formhead . '\n";');
                foreach ($items as $postid) {
          $post = tpost::instance($postid);
          if ($post->status != 'published') continue;
          eval('$result .= "'. $html->formitem . '\n";');
        }
        eval('$result .= "'. $html->formfooter . '\n";');
return $this->FixCheckall($result);
  }
  
  public function ProcessForm() {
    $result = '';
    $comusers = tcomusers::instance();
    if (!($user = $comusers->GetItemFromCookie($_GET['userid']))) return '';
$subscribers = tsubscribers::instance();
      $subscribers->lock();
      foreach ($_POST as $name => $value) {
        if (substr($name, 0, 7) == 'postid-') {
          $subscribers->delete($value, $user['id']);
        }
      }
      $subscribers->unlock();

      $html = THtmlResource::instance();
      $html->section = $this->basename;
      $lang = tlocal::instance();
      eval('$result .= "'. $html->unsubscribed . '\n";');
    return $result;
  }
  
}//class

?>