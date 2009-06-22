<?php

class TAdminOptions extends TAdminPage {
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'options';
 }
 
 public function Getcontent() {
  global $Options, $Template, $paths;
  $checked = "checked='checked'";
  $result = '';
  
  switch ($this->arg) {
   case null:
   $description = $this->ContentToForm($Options->description);
   $footer = $this->ContentToForm($Template->footer);
   $formname = 'descriptionform';
   break;
   
   case 'home':
   $home = &THomepage::Instance();
   $hideposts = $home->hideposts  ? 'checked' : '';
   $text = $this->ContentToForm($home->text);
   $formname = 'home';
   break;
   
   case 'mail':
   $subscribe = &TSubscribe ::Instance();
   $mailer = &TSMTPMailer ::Instance();
   $mailerchecked = $Options->mailer == 'smtp' ? $checked : '';
   $formname = 'mailform';
   break;
   
   case 'rss':
   $rss = &TRSS::Instance();
   $formname = 'rssform';
   break;
   
   case 'view':
   $ContentFilter = &TContentFilter::Instance();
   $automore = $ContentFilter->automore ? $checked: '';
   $submenuinwidget = $Template->submenuinwidget ? $checked : '';
   $formname = 'viewform';
   break;
   
   case 'comments':
   $status = $Options->DefaultCommentStatus  == 'approved' ? $checked: '';
   $commentsenabled = $Options->commentsenabled ? $checked: '';
   $pingenabled  = $Options->pingenabled  ? $checked: '';
   $CommentManager = &TCommentManager::Instance();
   $sendnotification = $CommentManager->SendNotification ? $checked : '';
   
   $authors = &TCommentUsers ::Instance();
   $hidelink = $authors->hidelink ? $checked : '';
   $redir = $authors->redir ? $checked : '';
   $nofollow = $authors->nofollow ? $checked : '';
   
   $formname = 'commentsform';
   break;
   
   case 'ping':
   $pinger = &TPinger::Instance();
   $pingenabled  = $pinger->enabled  ? $checked: '';
   $formname = 'pingform';
   break;
   
   case 'links':
   $linkgen = &TLinkGenerator::Instance();
   $formname = 'linksform';
   break;
   
   case 'openid':
   $openid = &TOpenid::Instance();
   $confirm = $openid->confirm ? $checked : '';
   $usebigmath = $openid->usebigmath ? $checked : '';
   $trusted = $this->ContentToForm(implode("\n", $openid->trusted));
   $formname = 'openidform';
   break;
   
   case 'cache':
   $cacheenabled = $Options->CacheEnabled ? $checked : '';
   $formname = 'cacheform';
   break;
   
   case 'lite':
   $archives = &TArchives::Instance();
   $litearchives = $archives->lite ? $checked : '';
   $categories= &TCategories::Instance();
   $litecategories = $categories->lite ? $checked : '';
   $tags = &TTags::Instance();
   $litetags = $tags->lite ? $checked : '';
   $formname = 'liteform';
   break;
   
   case 'robotstxt':
   $robotstxt = &TRobotstxt::Instance();
   $content = implode("\n", $robotstxt->items);
   $content = $this->ContentToForm($content);
   $formname = 'robotstxtform';
   break;
   
   case 'local':
   $timezones = $this->GetTimezones();
   $formname = 'localform';
   break;
   
   case '404':
   $err = &TNotFound404 ::Instance();
   $content = $this->ContentToForm($err->text);
   $formname = 'form404';
   break;
   
  }
  
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
$lang = &TLocal::Instance();
 eval('$result .= "'. $html->{$formname} . '\n";');
  $result = str_replace("'", '"', $result);
  return $result;
 }
 
 public function ProcessForm() {
  global $Options, $Urlmap, $paths;
  
  extract($_POST);
  
  switch ($this->arg) {
   case null:
   $Template = &TTemplate::Instance();
   $Options->Lock();
   if (!empty($url) && ($url != $Options->url))  {
    $Options->url = $url;
    $Options->rss = $url . '/rss/';
    $Options->rsscomments = $url .  '/comments/';
    $Options->pingurl = $url . '/rpc.xml';
    $Options->foaf = $url . '/foaf.xml';
   }
   if (!empty($name)) $Options->name = $name;
   if (!empty($description)) $Options->description = $description;
   if (!empty($keywords)) $Options->keywords = $keywords;
   $Options->Unlock();
   
   if (!empty($footer)) $Template->footer = $footer;
   $Urlmap->ClearCache();
   break;
   
   case 'home':
   $home = &THomepage::Instance();
   $home->Lock();
   $home->text = $text;
   $home->hideposts = isset($hideposts);
   $home->Unlock();
   break;
   
   case 'mail':
   $Options->Lock();
   if(!empty($email)) $Options->email = $email;
   if(!empty($fromemail)) $Options->fromemail = $fromemail;
   $Options->mailer = empty($mailer) ? '': 'smtp';
   $Options->Unlock();
   if (!empty($subscribeemail)) {
    $subscribe = &TSubscribe ::Instance();
    $subscribe->fromemail = $subscribeemail;
    $subscribe->Save();
   }
   
   $mailer = &TSMTPMailer ::Instance();
   $mailer->Lock();
   $mailer->host = $host;
   $mailer->login = $login;
   $mailer->password = $password;
   $mailer->port= (int) $port;
   $mailer->Unlock();
   break;
   
   case 'rss':
   $rss = &TRSS::Instance();
   $rss->SetFeedburnerLinks($feedburner, $feedburnercomments);
   break;
   
   case 'view':
   if (!empty($postsperpage)) $Options->postsperpage = (int) $postsperpage;
   $ContentFilter = &TContentFilter::Instance();
   $ContentFilter->automore = isset($automore);
   $ContentFilter->automorelength = (int) $automorelength;
   $ContentFilter->Save();
   $Template = &TTemplate::Instance();
   $Template->submenuinwidget = isset($submenuinwidget);
   break;
   
   case 'comments':
   $Options->Lock();
   $Options->DefaultCommentStatus  = isset($status) ? 'approved' : 'hold';
   $Options->commentsenabled = isset($commentsenabled);
   $Options->pingenabled  = isset($pingenabled );
   $Options->Unlock();
   
   $CommentManager = &TCommentManager::Instance();
   $CommentManager->SendNotification = isset($sendnotification);
   
   $authors = &TCommentUsers ::Instance();
   $authors->hidelink = isset($hidelink);
   $authors->redir = isset($redir);
   $authors->nofollow = isset($nofollow);
   $authors->Save();
   $Urlmap->ClearCache();
   break;
   
   case 'ping':
   $pinger = &TPinger::Instance();
   $pinger->Lock();
   $pinger->services = $content;
   $pinger->enabled = isset($pingenabled);
   $pinger->Unlock();
   break;
   
   case 'links':
   $linkgen = &TLinkGenerator::Instance();
   if (!empty($post)) $linkgen->post = $post;
   if (!empty($category)) $linkgen->category = $category;
   if (!empty($tag)) $linkgen->tag = $tag;
   $linkgen->Save();
   break;
   
   case 'openid':
   $openid = &TOpenid::Instance();
   $openid->confirm = isset($confirm);
   $openid->usebigmath = isset($usebigmath);
   $openid->trusted = explode("\n", trim($trusted));
   $openid->Save();
   break;
   
   case 'cache':
   $Options->Lock();
   $Options->CacheEnabled  = isset($cacheenabled);
   if (!empty($cacheexpired)) $Options->CacheExpired = (int) $cacheexpired;
   $Options->Unlock();
   break;
   
   case 'lite':
   $archives = &TArchives::Instance();
   $archives->lite = isset($litearchives);
   $categories= &TCategories::Instance();
   $categories->SetParams(isset($litecategories), $categories->sortname, $categories->showcount, $categories->maxcount);
   $tags = &TTags::Instance();
   $tags->SetParams(isset($litetags), $tags->sortname, $tags->showcount, $tags->maxcount);
   break;
   
   case 'robotstxt':
   $robotstxt = &TRobotstxt::Instance();
   $robotstxt->items = explode("\n", $content);
   $robotstxt->Save();
   break;
   
   case 'local':
   $Options->timezone = $timezone;
   $Options->language = $language;
   $archives = &TArchives::Instance();
   TUrlmap::unsub($archives);
   $archives->PostsChanged();
   break;
   
   case '404':
   $err = &TNotFound404 ::Instance();
   $err->text = $content;
   $err->Save();
   break;
   
  }
  
  return '';
 }
 
 private function GetTimezones() {
  global $Options;
  $zones = timezone_identifiers_list ();
  $result = "<select name='timezone' id='timezone'>\n";
  foreach ($zones as $zone) {
   $selected = $zone == $Options->timezone ? 'selected' : '';
   $result .= "<option value='$zone' $selected>$zone</option>\n";
  }
  $result .= "</select>";
  return $result;
 }
}//class
?>