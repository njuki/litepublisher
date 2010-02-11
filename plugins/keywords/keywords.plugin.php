<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tkeywordsplugin  extends tplugin {
 
 public static function instance() {
  return getinstance(__class__);
 }

public function urldeleted($item) {
tfiler::deletemask(litepublisher::$paths['data'] . 'keywords' . DIRECTORY_SEPARATOR. $item['id'] . ".*.php");
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
  
  $keywords = htmlspecialchars($keywords, ENT_QUOTES);

  $link =" <a href=\"http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]\">$keywords</a>";

$widget = tkeywordswidget::instance();
  if (in_array($link, $twidget->links)) return;
  $widget->links[] = $link;
  $widget->save();
 }
 
private function hasru($s) {
 return preg_match('/[à-ÿÀ-ß]{1,}/', $s);
 }

public function added($filename, $content) {
$options = litepublisher::$options;
$subject ="[$options->name] new keywords added";
$body = "The new widget has been added on
$options->url{$_SERVER['REQUEST_URI']}
links:
$content

You can edit this links at:
$options->url/admin/plugins/{$options->q}plugin=keywords&filename=$filename
";

    tmailer::sendmail($options->name, $options->fromemail,
    'admin', $options->email,  $subject, $body);
}
 
}//class

?>