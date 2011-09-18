<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tkeywordsplugin  extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function urldeleted($item) {
    tfiler::deletemask(litepublisher::$paths->data . 'keywords' . DIRECTORY_SEPARATOR. $item['id'] . ".*.php");
  }
  
  public function parseref($url) {
    if (strbegin($url, '/admin/') || strbegin($url, '/croncron.php')) return;
    $ref=  isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    if (empty($ref)) return;
    $urlarray = parse_url($ref);
    if ( $urlarray['scheme'] !== 'http' )  return;
    $host = $urlarray['host'];
    if (($host == 'search.msn.com')  || is_int(strpos($host, '.google.'))) {
      parse_str($urlarray['query']);
      $keywords=$q;
      if (isset($ie) && ($ie == 'windows-1251')) {
        $keywords= @iconv("windows-1251", "utf-8", $keywords);
      }
    } elseif ($host == 'www.rambler.ru') {
      parse_str($urlarray['query']);
      $keywords= @iconv("windows-1251", "utf-8", $words);
    } elseif ($host == 'www.yandex.ru') {
      parse_str($urlarray['query']);
      $keywords = $text;
    } else {
      return;
    }
    
    $keywords = trim($keywords);
    if (empty($keywords)) return;
    
    $c = substr_count($keywords, chr(208));
    if (($c < 3) && $this->hasru($keywords)) {
      $keywords= @iconv('windows-1251', 'utf-8', $keywords);
    }
    
    $keywords = trim($keywords);
    if (empty($keywords)) return;
    if (false !== strpos($keywords, 'site:')) return;
    if (false !== strpos($keywords, 'inurl:')) return;
    if (false !== strpos($keywords, 'intext:')) return;
    if (false !== strpos($keywords, 'http:')) return;
    if (false !== strpos($keywords, 'ftp:')) return;
    if (false !== strpos($keywords, 'ftp:')) return;
    if (false !== strpos($keywords, 'downloads%3Cscript%')) return;
    if (false !== strpos($keywords, '\\')) return;
    
    $keywords = htmlspecialchars($keywords, ENT_QUOTES);
    
    //$link =" <a href=\"http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]\">$keywords</a>";
    $widget = tkeywordswidget::i();
    //if (in_array($link, $widget->links)) return;
    foreach ($widget->links as $item) {
      if ($keywords == $item['text']) return;
    }
    $widget->links[] = array(
    'url' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
    'text' => $keywords
    );
    
    $widget->save();
  }
  
  private function hasru($s) {
  return preg_match('/[à-ÿÀ-ß]{1,}/', $s);
  }
  
  public function added($filename, $content) {
    $filename = basename($filename);
    $site = litepublisher::$site;
    $subject ="[$site->name] new keywords added";
    $body = "The new widget has been added on
  $site->url{$_SERVER['REQUEST_URI']}
    
    Widget content:
    
    $content
    
    You can edit this links at:
  $site->url/admin/plugins/{$site->q}plugin=keywords&filename=$filename
    ";
    
    tmailer::sendmail($site->name, litepublisher::$options->fromemail,
    'admin', litepublisher::$options->email,  $subject, $body);
  }
  
}//class