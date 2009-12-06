<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tadminoptions extends tadminmenu {
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    global $classes, $options, $template, $mailer, $subscribers, $home, $rss, $pinger, $linkgen;
    $result = '';
    $args = targs::instance();

    switch ($this->name) {
      case 'options':
      $args->description = $options->description;
      $args->footer = $template->footer;
      break;
      
      case 'home':
      $home = thomepage::instance();
      $args->hideposts = $home->hideposts;
      $args->text = $$home->text;
     break;
      
      case 'mail':
      $subscribers = tsubscribers::instance();
      $mailer = TSMTPMailer ::instance();
      $args->mailerchecked = $options->mailer == 'smtp';
      break;
      
      case 'rss':
      $rss = trss::instance();
$args->content = $rss->template;
      break;
      
      case 'view':
      $filter = tcontentfilter::instance();
      $args->automore = $filter->automore;
$args->phpcode = $filter->phpcode;
      $args->hovermenu = $template->hovermenu;
      break;
      
      case 'comments':
      $args->status = $options->DefaultCommentStatus  == 'approved';
      $args->commentsdisabled = $options->commentsdisabled;
      $args->commentsenabled = $options->commentsenabled;
      $args->pingenabled  = $options->pingenabled;
      $args->commentpages  = $options->commentpages;
      $manager = $classes->commentmanager;
      $args->sendnotification = $manager->sendnotification;
      
      $comusers= tcomusers ::instance();
      $args->hidelink = $comusers->hidelink;
      $args->redir = $comusers->redir;
      $args->nofollow = $comusers->nofollow;
      
      $subscribers = tsubscribers::instance();
      $args->locklist = $subscribers->locklist;
      break;
      
      case 'ping':
      $pinger = tpinger::instance();
      $args->pingenabled  = $pinger->enabled  ? $checked: '';
$args->content = $pinger->services;
      break;
      
      case 'links':
      $linkgen = tlinkgenerator::instance();
      break;
      
      case 'openid':
      $openid = topenid::instance();
      $args->confirm = $openid->confirm ? $checked : '';
      $args->usebigmath = $openid->usebigmath ? $checked : '';
      $args->trusted = implode("\n", $openid->trusted);
      break;
      
      case 'cache':
      $args->cache = $options->cache;
      break;
      
      case 'lite':
      $args->litearchives = $classes->archives->lite;
      $args->litecategories = $classes->categories->lite;
      $args->litetags = $classes->tags->lite;
      break;
      
      case 'secure':
      $auth = tauthdigest::instance();
      $args->cookie = $auth->cookieenabled;
      $args->xxxcheck = $auth->xxxcheck;
     break;
      
      case 'robots':
      $robots = trobots::instance();
      $args->content = implode("\n", $robots->items);
      break;
      
      case 'local':
      $args->timezones = $this->gettimezones();
      break;
      
      case 'notfound404':
      $err = tfnotfound404 ::instance();
      $args->content = $err->text;
      break;
      
    }
    
$result  = $this->html->__call($this->name, $args);
return str_replace("'", '"', $result);
  }
  
  public function processform() {
    global $options, $urlmap, $paths;
    
    extract($_POST);
    
    switch ($this->name) {
      case 'options':
      $template = ttemplate::instance();
      $options->lock();
      if (!empty($url) && ($url != $options->url))  $options->seturl($url);
      if (!empty($name)) $options->name = $name;
      if (!empty($description)) $options->description = $description;
      if (!empty($keywords)) $options->keywords = $keywords;
      $options->unlock();
      
      if (!empty($footer)) $template->footer = $footer;
      $urlmap->clearcache();
      break;
      
      case 'home':
      $home = thomepage::instance();
      $home->lock();
      $home->text = $text;
      $home->hideposts = isset($hideposts);
      $home->unlock();
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
      if (!empty($postsperpage)) $options->postsperpage = (int) $postsperpage;
      $filter = tcontentfilter::instance();
      $filter->automore = isset($automore);
      $filter->automorelength = (int) $automorelength;
$filter->phpcode = isset($phpcode);
      $filter->save();
      $template = ttemplate::instance();
      $template->hovermenu = isset($hovermenu);
      break;
      
      case 'comments':
      $options->lock();
      $options->DefaultCommentStatus  = isset($status) ? 'approved' : 'hold';
      $options->commentsdisabled = isset($commentsdisabled);
      $options->commentsenabled = isset($commentsenabled);
      $options->pingenabled  = isset($pingenabled );
      $options->commentpages = isset($commentpages);
      $options->commentsperpage = $commentsperpage;
      $options->unlock();
      
      $manager = $classes->commentmanager;
      $manager->sendnotification = isset($sendnotification);
      
      $comusers = tcomusers ::instance();
      $comusers->hidelink = isset($hidelink);
      $comusers->redir = isset($redir);
      $comusers->nofollow = isset($nofollow);
      $comusers->save();
      
      $subscribtion = tsubscribers::instance();
      if ($locklist != $subscribtion->locklist) {
        $subscribtion->locklist = $locklist;
        $subscribtion->save();
      }
      $urlmap->clearcache();
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
      if (!empty($post)) $linkgen->post = $post;
      if (!empty($category)) $linkgen->category = $category;
      if (!empty($tag)) $linkgen->tag = $tag;
      $linkgen->save();
      break;
      
      case 'openid':
      $openid = topenid::instance();
      $openid->confirm = isset($confirm);
      $openid->usebigmath = isset($usebigmath);
      $openid->trusted = explode("\n", trim($trusted));
      $openid->save();
      break;
      
      case 'cache':
      if (isset($clearcache)) {
        $urlmap->clearcache();
      } else {
        $options->lock();
        $options->cache = isset($cache );
        if (!empty($cacheexpired)) $options->expiredcache = (int) $cacheexpired;
        $options->unlock();
      }
      break;
      
      case 'lite':
$options->lock();
      $classes->archives->lite = isset($litearchives);
      $classes->categories->lite = isset($litecategories);
      $classes->tags->lite = isset($litetags);
$options->unlock();
      break;
      
      case 'secure':
      $auth = tauthigest::instance();
      $auth->cookieenabled = isset($cookie);
      $auth->xxxcheck = isset($xxxcheck);
      $auth->save();
      break;
      
      case 'robots':
      $robots = trobotstxt::instance();
      $robots->items = explode("\n", $content);
      $robots->save();
      break;
      
      case 'local':
      $options->lock();
      $options->dateformat = $dateformat;
      $options->language = $language;
      if ($options->timezone != $timezone) {
        $options->timezone = $timezone;
        $archives = tarchives::instance();
        TUrlmap::unsub($archives);
        $archives->PostsChanged();
      }
      $options->unlock();      
      $urlmap->clearcache();
      break;
      
      case 'notfound404':
      $err = tnotfound404 ::instance();
      $err->text = $content;
      $err->save();
      break;
      
    }
    
    return '';
  }
  
  private function gettimezones() {
    global $options;
    $zones = timezone_identifiers_list ();
    $result = "<select name='timezone' id='timezone'>\n";
    foreach ($zones as $zone) {
      $selected = $zone == $options->timezone ? 'selected' : '';
      $result .= "<option value='$zone' $selected>$zone</option>\n";
    }
    $result .= "</select>";
    return $result;
  }
}//class
?>