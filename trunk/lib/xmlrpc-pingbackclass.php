<?php
require_once(dirname(__file__) . DIRECTORY_SEPARATOR  . 'xmlrpc-abstractclass.php');

class TXMLRPCPingback extends TXMLRPCAbstract {
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  public function ping(&$args) {
    global $Options, $paths;
    
    $from = $args[0];
    $to   = $args[1];
    $home = $Options->url;
    if ($home != substr($to, 0, strlen($home))) {
      return new IXR_Error(0, 'Is there no link to us?');
    }
    
    $url = substr($to, strlen($Options->url) );
    $Urlmap = TUrlmap::Instance();
    if (!($item = &$Urlmap->FindItem($url))) {
      return new IXR_Error(0, 'Is there no link to us?');
    }
    
    if ($item['class'] != 'TPost') {
      return new IXR_Error(33, 'The specified target URL cannot be used as a target. It either doesn\'t exist, or it is not a pingback-enabled resource.');
    }
    
    $post = &TPost::Instance($item['arg']);
    if (!$post->pingenabled) {
      return new IXR_Error(33, 'The specified target URL cannot be used as a target. It either doesn\'t exist, or it is not a pingback-enabled resource.');
    }
    
    $comments = &$post->comments;
    if ($comments->HasPingback($from)) {
      return new IXR_Error(48, 'The pingback has already been registered.');
    }
    
    require_once($paths['libinclude'] . 'utils.php');
    
    if (!($s = GetWebPage($from))) {
      return new IXR_Error(16, 'The source URL does not exist.');
    }
    
    $s = str_replace('<!DOC', '<DOC', $s);
    $s = preg_replace( '/[\s\r\n\t]+/', ' ', $s ); // normalize spaces
    
    if (!preg_match('|<title>([^<]*?)</title>|is', $s, $matchtitle) ||  empty( $matchtitle[1]) ) {
      return new IXR_Error(32, 'We cannot find a title on that page.');
    }
    
    $s = strip_tags( $s, '<a>' );
    if (!preg_match("|<a([^>]+?" . preg_quote($to) . "[^>]*)>[^>]+?</a>|", $s, $match)) {
      return new IXR_Error(17, 'The source URL does not contain a link to the target URL, and so cannot be used as a source.');
    }
    
    if (preg_match('/nofollow|noindex/is', $match[1])) {
      return new IXR_Error(32, 'The source URL contain nofollow or noindex atribute');
    }
    $CommentManager = &TCommentManager::Instance();
    $CommentManager->AddPingback($post, $from, $matchtitle[1]);
    
    return "Pingback from $from to $to registered. Keep the web talking! :-)";
  }
  
}//class

?>