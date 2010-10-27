<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmanifest extends tevents {
  
  static function instance() {
    return getinstance(__class__);
  }
  
  public function request($arg) {
    $site = litepublisher::$site;
    $s = turlmap::xmlheader();
    switch ($arg) {
      case 'manifest':
      $s .= '<manifest xmlns="http://schemas.microsoft.com/wlw/manifest/weblog">' .
      '<options>' .
      '<clientType>WordPress</clientType>' .
      '<supportsKeywords>Yes</supportsKeywords>' .
      '<supportsGetTags>Yes</supportsGetTags>' .
      '<supportsNewCategories>Yes</supportsNewCategories>' .
      '</options>' .
      
      '<weblog>' .
      '<serviceName>Lite Publisher</serviceName>' .
      
      "<homepageLinkText>$site->name</homepageLinkText>" .
      "<adminLinkText>$site->name</adminLinkText>" .
      "<adminUrl>$site->url/admin/</adminUrl>" .
      '<postEditingUrl>' .
  "<![CDATA[$site->url/admin/posts/editor/{$site->q}id={post-id}]]>" .
      '</postEditingUrl>' .
      '</weblog>' .
      
      '<buttons>' .
      '<button>' .
      '<id>0</id>' .
      '<text>Manage Comments</text>' .
      //'<imageUrl>images/wlw/wp-comments.png</imageUrl>' .
      '<imageUrl>/favicon.ico</imageUrl>' .
      '<clickUrl>' .
      "<![CDATA[$site->url/admin/comments/]]>" .
      '</clickUrl>' .
      '</button>' .
      
      '</buttons>' .
      '</manifest>';
      break;
      
      case 'rsd':
      $s .= '<rsd version="1.0" xmlns="http://archipelago.phrasewise.com/rsd">' .
      '<service>' .
      '<engineName>Lite Publisher</engineName>' .
      '<engineLink>http://litepublisher.com/</engineLink>' .
      "<homePageLink>$site->url/</homePageLink>" .
      '<apis>' .
      '<api name="WordPress" blogID="1" preferred="true" apiLink="' . $site->url . '/rpc.xml" />' .
      '<api name="Movable Type" blogID="1" preferred="false" apiLink="' . $site->url . '/rpc.xml" />' .
      '<api name="MetaWeblog" blogID="1" preferred="false" apiLink="' . $site->url . '/rpc.xml" />' .
      '<api name="Blogger" blogID="1" preferred="false" apiLink="' . $site->url . '/rpc.xml" />' .
      '</apis>' .
      '</service>' .
      '</rsd>';
      break;
    }
    
    return  $s;
  }
  
}
?>