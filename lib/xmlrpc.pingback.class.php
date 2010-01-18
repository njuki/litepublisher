<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCPingback extends TXMLRPCAbstract {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function ping($from, $to) {
    global $options, $paths, $classes;
    
    if (!strbegin($to, $options->url)) {
      return new IXR_Error(0, 'Is there no link to us?');
    }
    
    $url = substr($to, strlen($options->url) );
    $urlmap = turlmap::instance();
    if (!($item = $urlmap->finditem($url))) {
      return $this->xerror(0, 'Is there no link to us?');
    }
    
    if ($item['class'] != $classes->classes['post'])  {
      return $this->xerror(33, 'The specified target URL cannot be used as a target. It either doesn\'t exist, or it is not a pingback-enabled resource.');
    }
    
    $post = tpost::instance($item['arg']);
    if (!$post->pingenabled || ($post->status != 'published')) {
      return $this->xerror(33, 'The specified target URL cannot be used as a target. It either doesn\'t exist, or it is not a pingback-enabled resource.');
    }
    
    $pingbacks = $post->pingbacks;
    if ($pingbacks->exists($from)) {
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
    
    $pingbacks->add($from, $matchtitle[1]);
    
    return "Pingback from $from to $to registered. Keep the web talking! :-)";
  }
  
}//class

?>