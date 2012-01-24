<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpasswordpage extends tevents_itemplate implements itemplate {
public $perm;
private $formresult;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'passwordpage';
$this->formresult = '';
  }

  private function checkspam($s) {
    if  (!($s = @base64_decode($s))) return false;
    $sign = 'megaspamer';
    if (!strbegin($s, $sign)) return false;
    $timekey = (int) substr($s, strlen($sign));
    return time() < $timekey;
  }
  
  public function request($arg) {
$this->cache = false;    
    if (!isset($_POST) || (count($_POST) == 0)) return;
    if (get_magic_quotes_gpc()) {
      foreach ($_POST as $name => $value) {
        $_POST[$name] = stripslashes($_POST[$name]);
      }
    }

    $antispam = isset($_POST['antispam']) ? $_POST['antispam'] : '';
    if (!$this->checkspam($antispam))          return 403;
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
if ($password == '') return;
if (!isset($this->perm)) {
$idperm = isset($_GET['idperm']) ? (int) $_GET['idperm'] : 0;
$perms = tperms::i();
if (!$perms->itemexists($idperm)) return 403;
$this->perm = tperm::i($idperm);
}

$backurl = isset($_GET['backurl']) ? $_GET['backurl'] : '';

if ($this->perm->checkpassword($password)) {
if ($backurl != '') turlmap::redir301($backurl);
} else {
$this->formresult = tlocal::i()->invalidpassword;
}
}

public function gettitle() {
return tlocal::i()->reqpassword;
}
  
  public function getcont() {
$result = $this->formresult;    
    $view = tview::getview($this);
    $theme = $view->theme;

$args = new targs();
    $args->antispam = base64_encode('megaspamer' . strtotime ("+1 hour"));
    
    $result .= $theme->parsearg($theme->templates['content.post.passwordform'], $args);



								<form action="$site.url/send-post-password.php" method="post" id="postpassword">
<p>$lang.postpassword</p>
									<p><input type="password" name="password" id="password" value="" size="22" />
									<label for="password">$lang.password</label></p>

<p>
									<input type="hidden" name="idpost" value="$context.id" />
									<input type="hidden" name="antispam" value="$antispam" />

									<input name="submitbutton" type="submit" id="submitbutton" value="$lang.send" /></p>
								</form>



return $result;
}
  
}//class  
}//class