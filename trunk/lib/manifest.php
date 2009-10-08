<?php

class TManifest extends TEventClass {
  
  static function &Instance() {
    return GetInstance(__class__);
  }
  
  public function Request($arg) {
    global $Options;
    
    $s = "<?php
    @header('Content-Type: text/xml; charset=utf-8');
    @ header('Last-Modified: " . date('r') ."');
    @header('X-Pingback: $Options->url/rpc.xml');
    echo '<?xml version=\"1.0\" encoding=\"utf-8\" ?>
    '; ?>";
    switch ($arg) {
      case 'manifest':
      $s .= '<manifest xmlns="http://schemas.microsoft.com/wlw/manifest/weblog">
      <options>
      <clientType>WordPress</clientType>
      <supportsKeywords>Yes</supportsKeywords>
      <supportsGetTags>Yes</supportsGetTags>
      <supportsNewCategories>Yes</supportsNewCategories>
      </options>
      
      <weblog>
      <serviceName>Lite Publisher</serviceName>';
      
      $s .= "    <homepageLinkText>$Options->name</homepageLinkText>
      <adminLinkText>$Options->name</adminLinkText>
      <adminUrl>$Options->url/admin/</adminUrl>
      <postEditingUrl>
      <![CDATA[
  $Options->url/admin/posteditor/{$Options->q}postid={post-id}
      ]]>
      </postEditingUrl>
      </weblog>
      
      <buttons>
      <button>
      <id>0</id>
      <text>Manage Comments</text>
      <imageUrl>images/wlw/wp-comments.png</imageUrl>
      <clickUrl>
      <![CDATA[
      $Options->url/admin/moderate/
      ]]>
      </clickUrl>
      </button>
      
      </buttons>
      </manifest>";
      break;
      
      case 'rsd':
      $s .= '<rsd version="1.0" xmlns="http://archipelago.phrasewise.com/rsd">
      <service>
      <engineName>Lite Publisher</engineName>
      <engineLink>http://litepublisher.com/</engineLink>';
      $s .= "    <homePageLink>$Options->url</homePageLink>
      <apis>
      <api name=\"WordPress\" blogID=\"1\" preferred=\"true\" apiLink=\"$Options->url/rpc.xml\" />
      <api name=\"Movable Type\" blogID=\"1\" preferred=\"false\" apiLink=\"$Options->url/rpc.xml\" />
      <api name=\"MetaWeblog\" blogID=\"1\" preferred=\"false\" apiLink=\"$Options->url/rpc.xml\" />
      <api name=\"Blogger\" blogID=\"1\" preferred=\"false\" apiLink=\"$Options->url/rpc.xml\" />
      </apis>
      </service>
      </rsd>";
      break;
    }
    
    return  $s;
  }
  
}
?>