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

    $users = TCommentUsers::instance();
    if (!($user = $users->GetItemFromCookie($_GET['userid']))) return $this->notfount();
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
    $users = TCommentUsers::instance();
    if (!($user = $users->GetItemFromCookie($_GET['userid']))) return '';
$subscribers = tsubscribers::instance();
if (dbversion) {
} else {
      $subscribers->lock();
      foreach ($_POST as $name => $value) {
        if (substr($name, 0, 7) == 'postid-') {
          $subscribers->unsubscribe($user['id'], $value);
        }
      }
      $subscribers->Unlock();
}

      $html = THtmlResource::instance();
      $html->section = $this->basename;
      $lang = tlocal::instance();
      eval('$result .= "'. $html->unsubscribed . '\n";');
    return $result;
  }
  
}//class

?>