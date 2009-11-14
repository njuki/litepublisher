<?php

class TContentFilter extends TEventClass {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'contentfilter';
    $this->addevents('OnComment', 'OnPost', 'OnRSS', 'OnExcerpt', 'BeforeSetPostContent', 'AfterSetPostContent');
    $this->data['automore'] = true;
    $this->data['automorelength'] = 250;
$this->data['phpcode'] = true;
  }
  
  public function GetCommentContent($content) {
    $s = $this->OnComment($content);
    if ($s != '') $content  = $s;
    $result = trim($content);
    $result = htmlspecialchars($result);
    $result = str_replace("\r\n", "\n", $result);
    $result = str_replace("\r", "\n", $result);
    $result = str_replace("\n\n", "</p><p>", $result);
    $result = str_replace("\n", "<br />\n", $result);
    
    return $result;
  }
  
  public function SetPostContent(tpost $post, $s) {
    $this->BeforeSetPostContent($post->id);
    $s = $this->FilterInternalLinks($s);
    if ( preg_match('/<!--more(.*?)?-->/', $s, $matches)  ||
    preg_match('/\[more(.*?)?\]/', $s, $matches)  ||
    preg_match('/\[cut(.*?)?\]/', $s, $matches)
    ) {
      $parts = explode($matches[0], $s, 2);
      $post->excerpt = $this->filter($parts[0]);
      $post->filtered = $post->excerpt . $this->ExtractPages($post,$parts[1]);
      $post->rss =  $post->excerpt;
      $post->moretitle =  self::NormalizeMoreTitle($matches[1]);
      if ($post->moretitle == '')  $post->moretitle = TLocal::$data['default']['more'];
    } else {
      if ($this->automore) {
        $post->filtered = $this->ExtractPages($post, $s);
        $post->excerpt = self::GetExcerpt($s, $this->automorelength);
        $post->rss =  $post->excerpt;
        $post->moretitle = TLocal::$data['default']['more'];
      } else {
        $post->excerpt = $this->ExtractPages($post, $s);
        $post->filtered = $post->excerpt;
        $post->rss =  $post->excerpt;
        $post->moretitle =  '';
      }
    }
    $post->description = self::GetExcerpt($post->excerpt, 80);
    $this->DoFilterEvents($post);
    $this->AfterSetPostContent($post->id);
  }
  
  public function ExtractPages(tpost $post, $s) {
    $tag = '<!--nextpage-->';
    $post->deletepages();
    if (!strpos( $s, $tag) )  return $this->filter($s);
    
    while($i = strpos( $s, $tag) ) {
      $page = trim(substr($s, 0, $i));
      $post->addpage($this->filter($page));
      $s = trim(substr($s, $i + strlen($tag)));
    }
    if ($s != '') $post->addpage($this->filter($s));
    return $post->GetPage(0);
  }
  
  private function DoFilterEvents(tpost $post) {
    $s = $this->OnPost(    $post->filtered);
    if ($s != '') $post->filtered =  $s;
    
    $s = $this->OnExcerpt($post->excerpt);
    if ($s != '') $post->excerpt = $s;
    
    $s = $this->OnRSS($post->rss);
    if ($s != '') $post->rss = $s;
  }
  
  public static function NormalizeMoreTitle($s) {
    $s = trim($s);
    $s = preg_replace('/\0+/', '', $s);
    $s = preg_replace('/(\\\\0)+/', '', $s);
    $s = strip_tags($s);
    return trim($s);
  }
  
  public function filter($content) {
    $result = trim($content);
    $result = $this->replacecode($result);
    
    $result = str_replace("\r\n", "\n", $result);
    $result = str_replace("\r", "\n", $result);
    //после тега до конца строки удаляются пробеллы
    $result = preg_replace('/\>(\s*?)?\n/',">\n", $result);
    //ставятся параграфы вместо двух разрывов строк
    $result = str_replace("\n\n", "</p>\n<p>", $result);
    //замена разрывов строк на <br /> до и после тегов a|img|b|i|u
$result = preg_replace('/\n<(a|img)([^<]*)>/im', "<br />\n<$1$2>", $result);
result = preg_replace('/<img src=([^<]*)>\n/im', "<img src=$1><br />\n", $result);

/*
    $result = preg_replace('/\n<(a|img)(.*)>/im', "<br />\n<$1$2>", $result);
    $result = preg_replace('/<img src=(.*)>\n/im', "<img src=$1><br />\n", $result);
*/

    $result = preg_replace('/\n<(b|i|u|strong|em)>/im', "<br />\n<$1>", $result);
    $result = preg_replace('/<\/(a|b|i|u|strong|em)>\n/im', "</$1><br/>\n", $result);
    //переводы строки если нет в конце тегов
    $result = preg_replace('/(?<!\>)\n(?!\s*\<)/im', "<br />\n", $result);

if (!preg_match('/^<p>/i', $result, $m)) $result = '<p>'. $result;
if (!preg_match('/<\/p>$/i', $result, $m)) $result .= '</p>';
        return $result;
  }
  
  public function replacecode($s) {
if ($this->phpcode) $s = preg_replace_callback('/\<\?(php)?(.*?)\?\>/ims', 'TContentFilter::CallbackReplaceCode', $s);
    return preg_replace_callback('/<code>(.*?)<\/code>/ims', 'TContentFilter::CallbackReplaceCode', $s);

  }
  
  public static function CallbackReplaceCode($found) {
    $code = str_replace(' ', '&nbsp;', htmlspecialchars($found[1]));
    return "<code>$code</code>";
  }
  
  public static function getexcerpt($content, $len) {
    $result = strip_tags($content);
    if (strlen($result) <= $len) return $result;
    $chars = "\n ,.;!?:(";
    $p = strlen($result);
    for ($i = strlen($chars) - 1; $i >= 0; $i--) {
    if($pos = strpos($result, $chars{$i}, $len)) {
        $p = min($p, $pos + 1);
      }
    }
    return substr($result, 0, $p);
  }
  
  public static function ValidateEmail($email) {
  return  preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email);
  }
  
  public function FilterInternalLinks($s) {
    global $Options;
    if (strpos($s, '[bloglink]')) {
      $bloglink = "<a href=\"$Options->url$Options->home\">$Options->name</a>";
      $s = str_replace('[bloglink]', $bloglink, $s);
    }
    
    if (strpos($s, '[prevpost]')) {
      $posts = &TPosts::instance();
      $last = $posts->GetRecent(1);
      $post = &TPost::instance($last[0]);
      $link = "<a href=\"$Options->url$post->url\">$post->title</a>";
      $s = str_replace('[lastpost]', $link, $s);
    }
    
    if (strpos($s, '[file]')) {
      $files = &TFiles::instance();
      $s = str_replace('[file]', $files->Getlink($files->autoid), $s);
    }
    
    return $s;
  }
  
  public static function quote($s) {
    return strtr ($s, array('"'=> '&quot;', "'" => '&#039;', '\\'=> '&#092;'));
  }
  
  public static function escape($s) {
    return self::quote(htmlspecialchars(trim(strip_tags($s))));
  }
  
}
?>