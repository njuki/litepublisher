<?php

class TKeywordsPlugin extends TPlugin {
 protected $links;
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 public function CreateData() {
  parent::CreateData();
  $this->basename = 'keywords' . DIRECTORY_SEPARATOR   . 'index';
  $this->Data['count'] = 6;
  $this->AddDataMap('links', array());
 }
 
public function getwidget() {
  global $Options, $Urlmap, $paths;
  if ($Urlmap->IsAdminPanel) return '';
  if ('/croncron.php' == substr($Urlmap->url, 0, strlen('/croncron.php'))) return '';

  $filename = $paths['data'] . 'keywords' . DIRECTORY_SEPARATOR. "$Urlmap->urlid-$Urlmap->pagenumber.php";
  
  if (@file_exists($filename)) {
   $links = file_get_contents($filename);
  } else {
   if (count($this->links) < $this->count) return '';
$arlinks = array_splice($this->links, 0, $this->count);
   $this->Save();

   $links = "\n<li>" . implode("</li>\n<li>", $arlinks)  . "</li>";
   file_put_contents($filename, $links);
  }
$Template  = TTemplate::Instance();
  $result = $Template->GetBeforeWidget('keywords');
  $result .= $links;
  $result .= $Template->GetAfterWidget();
  
  return $result;
 }
 
 public function ParseRef($url) {
  if ('/admin/' == substr($url, 0, 7)) return '';
  if ('/croncron.php' == substr($url, 0, strlen('/croncron.php'))) return '';

  $Ref=  isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
//$Ref = 'http://www.google.com/search?q=speaking+mouse+sape5+jaws+' . time();
  if (empty($Ref)) return;
  $UrlArray = parse_url($Ref);
  if ( $UrlArray['scheme'] !== 'http' )  return;
  $RefHost = $UrlArray['host'];
  $IsGoogle = strpos($RefHost, '.google.');
  if (($RefHost == 'search.msn.com')  || is_int($IsGoogle)) {
   parse_str($UrlArray['query']);
   $KeyWords=$q;
   if (isset($ie) && ($ie == 'windows-1251')) {
    $KeyWords= @iconv("windows-1251", "utf-8", $KeyWords);
   }
  } elseif ($RefHost == 'www.rambler.ru') {
   parse_str($UrlArray['query']);
   $KeyWords= @iconv("windows-1251", "utf-8", $words);
  } elseif ($RefHost == 'www.yandex.ru') {
   parse_str($UrlArray['query']);
   $KeyWords = $text;
  } else {
return;
}

  $KeyWords = trim($KeyWords);
  if (empty($KeyWords)) return;
  
  $c = substr_count($KeyWords, chr(208));
  if (($c < 3) && $this->HasRuChars($KeyWords)) {
   $KeyWords= @iconv('windows-1251', 'utf-8', $KeyWords);
  }
  
  $KeyWords = trim($KeyWords);
  if (empty($KeyWords)) return;
  
  $KeyWords = htmlspecialchars($KeyWords, ENT_QUOTES);

  $link =" <a href=\"http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]\">$KeyWords</a>";
  if (in_array($link, $this->links)) return;
  $this->links[] = $link;
  $this->Save();
 }
 
 function HasRuChars($s) {
 return preg_match('/[à-ÿÀ-ß]{1,}/', $s);
 }
 
}//class

?>