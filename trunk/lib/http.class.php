<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class http {
  public static $timeout = 10;
  
  public static function get($url) {
    $parsed = @parse_url($url);
    if ( !$parsed || !is_array($parsed) ) return false;
    if ( !isset($parsed['scheme']) || !in_array($parsed['scheme'], array('http','https')) ) {
      $url = 'http://' . $url;
    }
    if ( ini_get('allow_url_fopen') ) {
      if($fp = @fopen( $url, 'r' )) {
        @stream_set_timeout($fp, self::$timeout);
        $result = '';
        while( $remote_read = fread($fp, 4096) )  $result .= $remote_read;
        fclose($fp);
        return $result;
      }
      return false;
    } elseif ( function_exists('curl_init') ) {
      $ch = curl_init();
      curl_setopt ($ch, CURLOPT_URL, $url);
      curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 2);
      curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt ($ch, CURLOPT_TIMEOUT, self::$timeout);
      $result= curl_exec($ch);
      curl_close($ch);
      return $result;
    } else {
      return false;
    }
  }
  
  public static function post($url, array $post) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, self::$timeout);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    
    $response = curl_exec($ch);
    $headers = curl_getinfo($ch);
    curl_close($ch);
    if ($headers['http_code'] != '200') return false;
    return $response;
  }
  
}//class