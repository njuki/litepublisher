<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class Tadminoptions extends tadminmenu {
  private $_form;
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getautoform($name) {
    if (isset($this->_form)) return $this->_form;
    switch ($name) {
      case 'options':
      $form = new tautoform(litepublisher::$site, 'options', 'blogdescription');
      $form->add($form->fixedurl, $form->url, $form->name, $form->description, $form->keywords);
      $form->obj = ttemplate::instance();
      $form->add($form->footer('editor'));
      break;
      
      case 'rss':
      $form = new tautoform(trss::instance(), 'options', 'rssoptions');
      $form->add($form->feedburner, $form->feedburnercomments, $form->template('editor'));
      break;
      
      case 'comments':
      $form = new tautoform(litepublisher::$options, 'options', 'commentform');
      $form->add($form->filtercommentstatus, $form->commentsapproved, $form->checkduplicate, $form->defaultsubscribe, $form->commentsdisabled, $form->autocmtform, $form->commentsenabled, $form->pingenabled,
      $form->commentpages, $form->commentsperpage);
      $form->obj = litepublisher::$classes->commentmanager;
      $form->add($form->sendnotification, $form->hidelink,  $form->redir, $form->nofollow);
      $form->addeditor(tsubscribers::instance(), 'locklist');
      break;
      
      case 'ping':
      $form = new tautoform(tpinger::instance(), 'options', 'optionsping');
      $form->add($form->enabled, $form->services('editor'));
      break;
      
      case 'robots':
      $form = new tautoform(trobotstxt::instance(), 'options', 'editrobot');
      $form->add($form->text('editor'));
      break;
      
      case 'notfound404':
      $form = new tautoform(tnotfound404::instance(), 'options', 'edit404');
      $form->add($form->notify, $form->text('editor'));
      break;
      
      
      default:
      return false;
    }
    $this->_form = $form;
    return $form;
  }
  
  public function gethead() {
    $result = parent::gethead();
    switch ($this->name) {
      case 'home':
      $template = ttemplate::instance();
    $result .= $template->getready('$("#tabs").tabs({ cache: true });');
      break;
    }
    return $result;
  }
  
  public function getcontent() {
    if ($form = $this->getautoform($this->name)) return $form->getform();
    $options = litepublisher::$options;
    $template = ttemplate::instance();
    ttheme::$vars['template'] = $template;
    $result = '';
    $args = targs::instance();
    
    switch ($this->name) {
      case 'home':
      $home = thomepage::instance();
      $args->hideposts = $home->hideposts;
      $args->invertorder = $home->invertorder;
      $args->image = $home->image;
      $args->idhome =  $home->id;
      $menus = tmenus::instance();
      $args->homemenu =  $menus->home;
      
      $args->includecats = tposteditor::getcategories($home->includecats);
      $args->excludecats = str_replace('category-', 'exclude_category-',
      tposteditor::getcategories($home->excludecats));
      $args->formtitle = '';
      break;
      
      case 'mail':
      ttheme::$vars['subscribers'] = tsubscribers::instance();
      ttheme::$vars['mailer'] = TSMTPMailer ::instance();
      $args->mailerchecked = $options->mailer == 'smtp';
      break;
      
      case 'view':
      $filter = tcontentfilter::instance();
      $args->usefilter = $filter->usefilter;
      $args->automore = $filter->automore;
      $args->automorelength = $filter->automorelength;
      $args->autolinks = $filter->autolinks;
      $args->commentautolinks = $filter->commentautolinks;
      $args->hovermenu = $template->hovermenu;
      $args->icondisabled = $options->icondisabled;
      
      $themeparser = tthemeparser::instance();
      $args->replacelang = $themeparser->replacelang;
      
      $parser = tmediaparser::instance();
      $args->enablepreview = $parser->enablepreview;
      $args->ratio = $parser->ratio;
      $args->previewwidth = $parser->previewwidth;
      $args->previewheight = $parser->previewheight;
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
      
      case 'catstags':
      case 'lite': //old version suports
      $args->litearch= litepublisher::$classes->archives->lite;
      $cats = litepublisher::$classes->categories;
      $args->litecats= $cats->lite;
      $args->parentcats = $cats->includeparents;
      $args->childcats = $cats->includechilds;
      $tags = litepublisher::$classes->tags;
      $args->litetags = $tags->lite;
      $args->parenttags = $tags->includeparents;
      $args->childtags = $tags->includechilds;
      $lang = tlocal::instance('options');
      $args->formtitle = $lang->catstags;
      $html = $this->html;
      return $html->adminform('[checkbox=litearch]
      [checkbox=litecats] [checkbox=parentcats] [checkbox=childcats]
      [checkbox=litetags] [checkbox=parenttags] [checkbox=childtags]', $args) .
      $html->p->notecatstags;
      
      case 'secure':
      $auth = tauthdigest::instance();
      $args->cookie = $options->cookieenabled;
      $args->usersenabled = $options->usersenabled;
      $args->reguser = $options->reguser;
      $args->parsepost = $options->parsepost;
      $args->xxxcheck = $auth->xxxcheck;
      $filter = tcontentfilter::instance();
      $args->phpcode = $filter->phpcode;
      $backuper = tbackuper::instance();
      $args->filertype = tadminhtml::array2combo(array(
      'auto' => 'auto',
      'file' => 'file',
      'ftp' => 'ftp',
      'ftpsocket' => 'ftpsocket',
      //'ssh2' => 'ssh2'
      ), $backuper->filertype);
      break;
      
      case 'local':
      $args->timezones = $this->gettimezones();
      break;
    }
    
  $result  = $this->html->{$this->name}($args);
    return $this->html->fixquote($result);
  }
  
  public function processform() {
    litepublisher::$urlmap->clearcache();
    if ($form = $this->getautoform($this->name)) return $form->processform();
    extract($_POST, EXTR_SKIP);
    $options = litepublisher::$options;
    
    switch ($this->name) {
      case 'home':
      $home = thomepage::instance();
      $home->lock();
      $home->image = $image;
      $home->hideposts = isset($hideposts);
      $home->invertorder = isset($invertorder);
      $home->includecats = tadminhtml::check2array('category-');
      $home->excludecats = tadminhtml::check2array('exclude_category-');
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
      
      case 'view':
      $options->icondisabled = isset($icondisabled);
      if (!empty($perpage)) $options->perpage = (int) $perpage;
      $filter = tcontentfilter::instance();
      $filter->usefilter = isset($usefilter);
      $filter->automore = isset($automore);
      $filter->automorelength = (int) $automorelength;
      $filter->autolinks = isset($autolinks);
      $filter->commentautolinks = isset($commentautolinks);
      $filter->save();
      $template = ttemplate::instance();
      $template->lock();
      $template->hovermenu = isset($hovermenu);
      $template->unlock();
      
      $replacelang  = isset($replacelang );
      $themeparser = tthemeparser::instance();
      if ($replacelang != $themeparser->replacelang) {
        $themeparser->replacelang = $replacelang;
        $themeparser->save();
      }
      
      $parser = tmediaparser::instance();
      $parser->enablepreview = isset($enablepreview);
      $parser->ratio = isset($ratio);
      $parser->previewwidth = $previewwidth;
      $parser->previewheight = $previewheight;
      $parser->save();
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
      case 'catstags':
      litepublisher::$classes->archives->lite = isset($litearch);
      $cats = litepublisher::$classes->categories;
      $cats->lite = isset($litecats);
      $cats->includeparents = isset($parentcats);
      $cats->includechilds = isset($childcats);
      $cats->save();
      $tags = litepublisher::$classes->tags;
      $tags->lite = isset($litetags);
      $tags->includeparents = isset($parenttags);
      $tags->includechilds = isset($childtags);
      $tags->save();
      break;
      
      case 'secure':
      if (isset($_POST['oldpassword'])) {
        $h2 = $this->html->h2;
        if ($oldpassword == '') return $h2->badpassword;
        if (($newpassword == '') || ($newpassword != $repassword))  return $h2->difpassword;
        if (!$options->auth($options->login, $oldpassword)) return $h2->badpassword;
        $options->changepassword($newpassword);
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
        
        $backuper = tbackuper::instance();
        if ($backuper->filertype != $filertype) {
          $backuper->filertype = $filertype;
          $backuper->save();
        }
      }
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
      $id = $menus->createitem(0, 'users', 'author', 'tadminusers');
      $menus->createitem($id, 'options', 'admin', 'tadminusers');
    } else {
      $menus->deleteurl('/admin/users/options/');
      $menus->deleteurl('/admin/users/');
    }
    $menus->unlock();
  }
  
}//class
?>