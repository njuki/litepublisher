<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class Tadminoptions extends tadminmenu {
private $_adminoptionsform;

  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $options = litepublisher::$options;
    $template = ttemplate::instance();
    ttheme::$vars['template'] = $template;
    $result = '';
    $args = targs::instance();
    
    switch ($this->name) {
      case 'options':
      $args->fixedurl = litepublisher::$site->fixedurl;
      $args->description = litepublisher::$site->description;
      $args->textfooter = $template->footer;
      break;
      
      case 'home':
      $home = thomepage::instance();
      $args->hideposts = $home->hideposts;
      $args->image = $home->image;
      $menus = tmenus::instance();
$args->idhome = $menus->idhome;
      $args->homemenu =  $menus->home;
      break;
      
      case 'mail':
      ttheme::$vars['subscribers'] = tsubscribers::instance();
      ttheme::$vars['mailer'] = TSMTPMailer ::instance();
      $args->mailerchecked = $options->mailer == 'smtp';
      break;
      
      case 'rss':
      $rss = trss::instance();
      ttheme::$vars['rss'] = $rss;
      $args->content = $rss->template;
      break;
      
      case 'view':
      $filter = tcontentfilter::instance();
      $args->automore = $filter->automore;
      $args->automorelength = $filter->automorelength;
      $args->autolinks = $filter->autolinks;
      $args->commentautolinks = $filter->commentautolinks;
      $args->hovermenu = $template->hovermenu;
      $args->icondisabled = $options->icondisabled;
      
      $parser = tmediaparser::instance();
      $args->enablepreview = $parser->enablepreview;
      $args->ratio = $parser->ratio;
      $args->previewwidth = $parser->previewwidth;
      $args->previewheight = $parser->previewheight;
      break;
      
      case 'comments':
      $args->filtercommentstatus = $options->filtercommentstatus;
      $args->status = $options->DefaultCommentStatus  == 'approved';
      $args->commentsdisabled = $options->commentsdisabled;
      $args->commentsenabled = $options->commentsenabled;
      $args->pingenabled  = $options->pingenabled;
      $args->commentpages  = $options->commentpages;
      $args->checkduplicate = $options->checkduplicate;
      $args->defaultsubscribe  = $options->defaultsubscribe;
      $manager = litepublisher::$classes->commentmanager;
      $args->sendnotification = $manager->sendnotification;
      $args->hidelink = $manager->hidelink;
      $args->redir = $manager->redir;
      $args->nofollow = $manager->nofollow;
      
      $subscribers = tsubscribers::instance();
      $args->locklist = $subscribers->locklist;
      break;
      
      case 'ping':
      $pinger = tpinger::instance();
      $args->pingenabled  = $pinger->enabled;
      $args->content = $pinger->services;
      break;
      
      case 'links':
      $linkgen = tlinkgenerator::instance();
      ttheme::$vars['linkgen'] = $linkgen;
      $args->urlencode = $linkgen->urlencode;
      break;
      
      
      case 'cache':
      $args->cache = $options->cache;
      $args->ob_cache = $options->ob_cache;
      $args->compress = $options->compress;
      break;
      
      case 'lite':
      $args->litearchives = litepublisher::$classes->archives->lite;
      $args->litecategories = litepublisher::$classes->categories->lite;
      $args->litetags = litepublisher::$classes->tags->lite;
      break;
      
      case 'secure':
      $auth = tauthdigest::instance();
      $args->cookie = $options->cookieenabled;
      $args->usersenabled = $options->usersenabled;
      $args->reguser = $options->reguser;
      $args->parsepost = $options->parsepost;
      $args->xxxcheck = $auth->xxxcheck;
      $filter = tcontentfilter::instance();
      $args->phpcode = $filter->phpcode;
      break;
      
      case 'robots':
      $robots = trobotstxt::instance();
      $args->content = implode("\n", $robots->items);
      break;
      
      case 'local':
      $args->timezones = $this->gettimezones();
      break;
      
      case 'notfound404':
      $notfound  = tnotfound404::instance();
      $args->notify = $notfound->notify;
      $args->content = $notfound->text;
      $forbidden = tforbidden::instance();
      $args->forbiddentext = $forbidden->text;
      break;

case 'admin':
return $this->adminoptionsform->form;
    }
    
  $result  = $this->html->{$this->name}($args);
    return $this->html->fixquote($result);
  }
  
  public function processform() {
    extract($_POST, EXTR_SKIP);
    $options = litepublisher::$options;
    
    switch ($this->name) {
      case 'options':
      $template = ttemplate::instance();
$site = litepublisher::$site;
      $site->lock();
      $site->fixedurl = isset($fixedurl);
      if (!empty($url) && ($url != $site->url))  $site->seturl($url);
      if (!empty($name)) $site->name = $name;
      if (!empty($description)) $site->description = $description;
      if (!empty($keywords)) $site->keywords = $keywords;
      $site->unlock();
      
      if (!empty($textfooter)) $template->footer = $textfooter;
      litepublisher::$urlmap->clearcache();
      break;
      
      case 'home':
      $home = thomepage::instance();
      $home->lock();
      $home->image = $image;
      $home->hideposts = isset($hideposts);
      $home->unlock();
      
      $menus = tmenus::instance();
$menus->home = isset($homemenu);
$menus->save();
      break;
      
      case 'mail':
      $options->lock();
      if(!empty($email)) $options->email = $email;
      if(!empty($fromemail)) $options->fromemail = $fromemail;
      $options->mailer = empty($mailer) ? '': 'smtp';
      $options->unlock();
      if (!empty($subscribeemail)) {
        $subscribe = tsubscribers::instance();
        $subscribe->fromemail = $subscribeemail;
        $subscribe->save();
      }
      
      $mailer = TSMTPMailer ::instance();
      $mailer->lock();
      $mailer->host = $host;
      $mailer->login = $login;
      $mailer->password = $password;
      $mailer->port= (int) $port;
      $mailer->unlock();
      break;
      
      case 'rss':
      $rss = trss::instance();
      $rss->lock();
      $rss->SetFeedburnerLinks($feedburner, $feedburnercomments);
      $rss->template = $content;
      $rss->unlock();
      break;
      
      case 'view':
      $options->icondisabled = isset($icondisabled);
      if (!empty($perpage)) $options->perpage = (int) $perpage;
      $filter = tcontentfilter::instance();
      $filter->automore = isset($automore);
      $filter->automorelength = (int) $automorelength;
      $filter->autolinks = isset($autolinks);
      $filter->commentautolinks = isset($commentautolinks);
      $filter->save();
      $template = ttemplate::instance();
      $template->lock();
      $template->hovermenu = isset($hovermenu);
      $template->unlock();
      
      $parser = tmediaparser::instance();
      $parser->enablepreview = isset($enablepreview);
      $parser->ratio = isset($ratio);
      $parser->previewwidth = $previewwidth;
      $parser->previewheight = $previewheight;
      $parser->save();
      break;
      
      case 'comments':
      $options->lock();
      $options->filtercommentstatus = isset($filtercommentstatus);
      $options->DefaultCommentStatus  = isset($status) ? 'approved' : 'hold';
      $options->commentsdisabled = isset($commentsdisabled);
      $options->commentsenabled = isset($commentsenabled);
      $options->pingenabled  = isset($pingenabled );
      $options->commentpages = isset($commentpages);
      $options->commentsperpage = $commentsperpage;
      $options->checkduplicate = isset($checkduplicate);
      $options->defaultsubscribe = isset($defaultsubscribe);
      $options->unlock();
      
      $manager = litepublisher::$classes->commentmanager;
      $manager->sendnotification = isset($sendnotification);
      $manager->hidelink = isset($hidelink);
      $manager->redir = isset($redir);
      $manager->nofollow = isset($nofollow);
      $manager->save();
      
      $subscribtion = tsubscribers::instance();
      if ($locklist != $subscribtion->locklist) {
        $subscribtion->locklist = $locklist;
        $subscribtion->save();
      }
      litepublisher::$urlmap->clearcache();
      break;
      
      case 'ping':
      $pinger = tpinger::instance();
      $pinger->lock();
      $pinger->services = $content;
      $pinger->enabled = isset($pingenabled);
      $pinger->unlock();
      break;
      
      case 'links':
      $linkgen = tlinkgenerator::instance();
      $linkgen->urlencode = isset($urlencode);
      if (!empty($post)) $linkgen->post = $post;
      if (!empty($menu)) $linkgen->menu = $menu;
      if (!empty($category)) $linkgen->category = $category;
      if (!empty($tag)) $linkgen->tag = $tag;
      if (!empty($archive)) $linkgen->archive = $archive;
      $linkgen->save();
      break;
      
      
      case 'cache':
      if (isset($clearcache)) {
        ttheme::clearcache();
      } else {
        $options->lock();
        $options->cache = isset($cache );
        if (!empty($cacheexpired)) $options->expiredcache = (int) $cacheexpired;
        $options->ob_cache = isset($ob_cache);
        $options->compress = isset($compress);
        $options->unlock();
      }
      break;
      
      case 'lite':
      litepublisher::$classes->archives->lite = isset($litearchives);
      litepublisher::$classes->categories->lite = isset($litecategories);
      litepublisher::$classes->tags->lite = isset($litetags);
      break;
      
      case 'secure':
      if (isset($_POST['oldpassword'])) {
        $h2 = $this->html->h2;
        if ($oldpassword == '') return $h2->badpassword;
        if (($newpassword == '') || ($newpassword != $repassword))  return $h2->difpassword;
        if (!$options->auth($options->login, $oldpassword)) return $h2->badpassword;
        $options->SetPassword($newpassword);
        $auth = tauthdigest::instance();
        $auth->logout();
        return $h2->passwordchanged;
      } else {
        $options->cookieenabled = isset($cookie);
        $options->reguser = isset($reguser);
        $this->usersenabled = isset($usersenabled);
        $options->parsepost = isset($parsepost);
        $auth = tauthdigest::instance();
        $auth->xxxcheck = isset($xxxcheck);
        $auth->save();
        $filter = tcontentfilter::instance();
        $filter->phpcode = isset($phpcode);
        $filter->save();
      }
      break;
      
      case 'robots':
      $robots = trobotstxt::instance();
      $robots->items = explode("\n", $content);
      $robots->save();
      break;
      
      case 'local':
      $options->lock();
      $options->dateformat = $dateformat;
      if ($options->language != $language) {
        if (file_exists(litepublisher::$paths->languages . "$language.ini")) $options->language = $language;
      }
      if ($options->timezone != $timezone) {
        $options->timezone = $timezone;
        $archives = tarchives::instance();
        TUrlmap::unsub($archives);
        $archives->PostsChanged();
      }
      $options->unlock();
      litepublisher::$urlmap->clearcache();
      break;
      
      case 'notfound404':
      $notfound = tnotfound404 ::instance();
      $notfound->notify = isset($notify);
      $notfound->text = $content;
      $forbidden = tforbidden::instance();
      $forbidden->text = $forbiddentext;
      $forbidden->save();
      $notfound->save();
      break;

case 'admin':
return $this->adminoptionsform->processform();
          }
    
    return '';
  }
  
  private function gettimezones() {
    $zones = timezone_identifiers_list ();
    $result = "<select name='timezone' id='timezone'>\n";
    foreach ($zones as $zone) {
      $selected = $zone == litepublisher::$options->timezone ? 'selected' : '';
      $result .= "<option value='$zone' $selected>$zone</option>\n";
    }
    $result .= "</select>";
    return $result;
  }
  
  public function setusersenabled($value) {
    if (litepublisher::$options->usersenabled == $value) return;
    litepublisher::$options->usersenabled = $value;
    $menus = tadminmenus::instance();
    $menus->lock();
    if ($value) {
      $menus->createitem(0, 'users', 'admin', 'tadminusers');
    } else {
      $menus->deleteurl('/admin/users/');
    }
    $menus->unlock();
  }

public function getadminoptionsform() {
if (isset($this->_adminoptionsform)) return $this_adminoptionsform;
$form = new tautoform(tajaxposteditor ::instance(), 'options', 'adminoptions');
$form->add($form->ajaxvisual, $form->visual);
$form->obj = tadminmenus::instance();
$form->add($form->heads('editor'));
$this->_adminoptionsform = $form;
return $form;
}
  
}//class
?>