<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class twordpressthemeparser {
  public static function get_about_wordpress_theme($name) {
    $filename = litepublisher::$paths->themes . $name . DIRECTORY_SEPARATOR . 'style.css';
    if (!@file_exists($filename)) return false;
    $data = self::wp_get_theme_data($filename);
    $about = array(
    'author' => $data['Author'],
    'url' => $data['URI'] != ''  ? $data['URI'] :$data['AuthorURI'],
    'description' => $data['Description'],
    'version' => $data['Version']
    );
    
    return $about;
  }
  
  public static function wp_get_theme_data( $theme_file ) {
    $default_headers = array(
    'Name' => 'Theme Name',
    'URI' => 'Theme URI',
    'Description' => 'Description',
    'Author' => 'Author',
    'AuthorURI' => 'Author URI',
    'Version' => 'Version',
    'Template' => 'Template',
    'Status' => 'Status',
    'Tags' => 'Tags'
    );
    
    $theme_data = self::wp_get_file_data( $theme_file, $default_headers, 'theme' );
    
    $theme_data['Name'] = $theme_data['Title'] = strip_tags( $theme_data['Name']);
    $theme_data['URI'] = strip_tags( $theme_data['URI'] );
    $theme_data['AuthorURI'] = strip_tags( $theme_data['AuthorURI'] );
    $theme_data['Version'] = strip_tags( $theme_data['Version']);
    
    if ( $theme_data['Author'] == '' ) {
      $theme_data['Author'] = 'Anonymous';
    }
    
    return $theme_data;
  }
  
  public static function wp_get_file_data( $file, $default_headers, $context = '' ) {
    $fp = fopen( $file, 'r' );
    $file_data = fread( $fp, 8192 );
    fclose( $fp );
    
    foreach ( $default_headers as $field => $regex ) {
    preg_match( '/' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, ${$field});
    if ( !empty( ${$field} ) )
  ${$field} = self::_cleanup_header_comment( ${$field}[1] );
      else
    ${$field} = '';
    }
    
    return compact( array_keys($default_headers) );
  }
  
  public static function _cleanup_header_comment($str) {
    return trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $str));
  }
  
}//class
