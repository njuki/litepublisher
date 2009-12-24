<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function AddAttr($node, $name, $value) {
  $attr = $node->ownerDocument->createAttribute($name);
  $attr->value = $value;
  $node->appendChild($attr);
}

function AddNode($node, $name) {
  $result = $node->ownerDocument->createElement($name);
  $node->appendChild($result);
  return $result;
}

function AddNodeValue($node, $name, $value) {
  $result = $node->ownerDocument->createElement($name);
  $textnode = $node->ownerDocument->createTextNode($value);
  $result->appendChild($textnode);
  $node->appendChild($result);
  Return $result;
}

function AddCData($node, $name, $value) {
  $result = $node->ownerDocument->createElement($name);
  $textnode = $node->ownerDocument->createCDATASection($value);
  $result->appendChild($textnode);
  $node->appendChild($result);
  Return $result;
}

function _struct_to_array(&$values, &$i)  {
  $result = array();
  if (isset($values[$i]['value'])) array_push($result, $values[$i]['value']);
  
  while (++$i < count($values)) {
    switch ($values[$i]['type']) {
      case 'cdata':
      array_push($result, $values[$i]['value']);
      break;
      
      case 'complete':
      $name = $values[$i]['tag'];
      if(!empty($name)){
        if (isset($values[$i]['value'])) {
          if (isset($values[$i]['attributes'])) {
            $result[$name]= array(
            0 => $values[$i]['value'],
            'attributes' => $values[$i]['attributes']
            );
          } else {
            $result[$name]= $values[$i]['value'];
          }
        } elseif (isset($values[$i]['attributes'])) {
          $result[$name] = $values[$i]['attributes'];
        } else {
          $result[$name]= '';
        }
      }
      break;
      
      case 'open':
      $name = $values[$i]['tag'];
      $size = isset($result[$name]) ? sizeof($result[$name]) : 0;
      $result[$name][$size] = _struct_to_array($values, $i);
      break;
      
      case 'close':
      return $result;
      break;
    }
  }
  return $result;
}//_struct_to_array

function xml2array($xml)  {
  $values = array();
  $index  = array();
  $result  = array();
  $parser = xml_parser_create();
  xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
  xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
  xml_parse_into_struct($parser, $xml, $values, $index);
  xml_parser_free($parser);
  
  $i = 0;
  $name = $values[$i]['tag'];
  $result[$name] = isset($values[$i]['attributes']) ? $values[$i]['attributes'] : '';
  $result[$name] = _struct_to_array($values, $i);
  return $result;
}

class Tdomrss extends domDocument {
  public $items;
  public $rss;
  public $channel;
  
  public function __construct() {
    parent::__construct();
    $this->items = array();
  }
  
  public function CreateRoot($url, $title) {
    global $Options;
    $this->encoding = 'utf-8';
    $this->appendChild($this->createComment("generator=\"Lite Publisher/$Options->version version\""));
    $this->rss = $this->createElement('rss');
    $this->appendChild($this->rss);
    
    AddAttr($this->rss, 'version', '2.0');
    AddAttr($this->rss, 'xmlns:content', "http://purl.org/rss/1.0/modules/content/");
    AddAttr($this->rss, 'xmlns:wfw',  "http://wellformedweb.org/CommentAPI/");
    AddAttr($this->rss, 'xmlns:dc', "http://purl.org/dc/elements/1.1/");
    AddAttr($this->rss, 'xmlns:atom', "http://www.w3.org/2005/Atom");
    
    $this->channel = AddNode($this->rss, 'channel');
    
    $link = AddNode($this->channel, 'atom:link');
    AddAttr($link, 'href', $url);
    AddAttr($link, 'rel', "self");
    AddAttr($link,'type', "application/rss+xml");
    
    AddNodeValue($this->channel , 'title', $title);
    AddNodeValue($this->channel , 'link', $url);
    AddNodeValue($this->channel , 'description', $Options->description);
    AddNodeValue($this->channel , 'pubDate', date('r'));
    AddNodeValue($this->channel , 'generator', 'http://litepublisher.com/generator/?version=' . $Options->version);
    AddNodeValue($this->channel , 'language', 'en');
  }
  
  public function AddItem() {
    $result = AddNode($this->channel, 'item');
    $this->items[] = $result;
    return $result;
  }
  
  public function GetStripedXML() {
    $s = $this->saveXML();
    return substr($s, strpos($s, '?>') + 2);
  }
  
}//class

?>