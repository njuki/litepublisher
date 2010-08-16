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
            $val = array(
            0 => $values[$i]['value'],
            'attributes' => $values[$i]['attributes']
            );
          } else {
            $val = $values[$i]['value'];
          }
        } elseif (isset($values[$i]['attributes'])) {
          $val = $values[$i]['attributes'];
        } else {
          $val = '';
        }
        if (!isset($result[$name])) {
          $result[$name]= $val;
        } elseif(is_array($result[$name])) {
          $result[$name][] = $val;
        } else {
          $result[$name] = array($result[$name], $val);
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

class tdomrss extends domDocument {
  public $items;
  public $rss;
  public $channel;
  
  public function __construct() {
    parent::__construct();
    $this->items = array();
  }
  
  public function CreateRoot($url, $title) {
    $this->encoding = 'utf-8';
    $this->appendChild($this->createComment('generator="Lite Publisher/' . litepublisher::$options->version . ' version"'));
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
    AddNodeValue($this->channel , 'description', litepublisher::$options->description);
    AddNodeValue($this->channel , 'pubDate', date('r'));
    AddNodeValue($this->channel , 'generator', 'http://litepublisher.com/generator.htm?version=' . litepublisher::$options->version);
    AddNodeValue($this->channel , 'language', 'en');
  }
  
  public function CreateRootMultimedia($url, $title) {
    $this->encoding = 'utf-8';
    $this->appendChild($this->createComment('generator="Lite Publisher/' . litepublisher::$options->version . ' version"'));
    $this->rss = $this->createElement('rss');
    $this->appendChild($this->rss);
    
    AddAttr($this->rss, 'version', '2.0');
    AddAttr($this->rss, 'xmlns:media', "http://video.search.yahoo.com/mrss");
    AddAttr($this->rss, 'xmlns:atom', "http://www.w3.org/2005/Atom");
    
    $this->channel = AddNode($this->rss, 'channel');
    
    $link = AddNode($this->channel, 'atom:link');
    AddAttr($link, 'href', $url);
    AddAttr($link, 'rel', "self");
    AddAttr($link,'type', "application/rss+xml");
    
    AddNodeValue($this->channel , 'title', $title);
    AddNodeValue($this->channel , 'link', $url);
    AddNodeValue($this->channel , 'description', litepublisher::$options->description);
    AddNodeValue($this->channel , 'pubDate', date('r'));
    AddNodeValue($this->channel , 'generator', 'http://litepublisher.com/generator.htm?version=' . litepublisher::$options->version);
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

//wraper for domnode
class tnode implements Countable, arrayaccess, Iterator  {
  private $index;
  public $node;
  
  public function __construct($node = null) {
    $this->node = $node;
    $this->index = 0;
  }
  
  public static function newinstance($node) {
    $class = __class__;
    return new $class($node);
  }
  
  public function getlist() {
    return $this->node instanceof DOMNodeList? $this->node : $this->node->childNodes;
  }
  
  public function getitem($name) {
    if (($node = $this->current()) && ($node->nodeName == $name)) return $node;
    $count = $this->list->length;
    for ($i = 0; $i < $count; $i++) {
      $node = $this->list->item($i);
      if ($node->nodeName == $name) return $node;
    }
    return false;
  }
  
  public function __get($name) {
    if ($name == 'list') return $this->getlist();
    if ($node = $this->getitem($name)) {
      return self::newinstance($node);
    }
    
    if ($list = $this->node->getElementsByTagName($name)) {
      switch ($list->length) {
        case 0: return false;
        case 1: return self::newinstance($list->item(0));
        default: return self::newinstance($list);
      }
    }
    return false;
  }
  
  public function __tostring() {
    if ($this->node instanceof DOMText) {
      if ($this->node->isWhitespaceInElementContent() ) return '';
      return $this->node->wholeText;
    }
    if ($this->count() > 0) {
      $node = $this->offsetGet(0);
      if ($node instanceof DOMText) return $node->wholeText;
    }
    if ($this->node instanceof DOMNode) return $this->node->nodeValue;
    return '';
  }
  
  public function __set($name, $value) {
    if ($node = $this->getitem($name)) {
      self::setvalue($node, $value);
    } else {
      self::addvalue($this->node, $name, $value);
    }
  }
  
  public function node_exists($name) {
    if (!($this->node instanceof DOMNode)) return false;
  if ($list  = $this->node->getElementsByTagName($name)) {}
    return $list->length > 0;
    return false;
  }
  
  //Countable
  public function count() {
    if ($list = $this->list) {
      return $list->length;
    } else {
      return 0;
    }
  }
  
  //arrayaccess
  public function offsetSet($offset, $value) {
    self::setvalue($this->offsetGet($offset), $value);
  }
  
  public function offsetExists($offset) {
    if (is_int($offset)) {
      return ($offset >= 0) && ($offset < $this->count());
    } else {
      return $this->node_exists($offset);
    }
  }
  
  public function offsetUnset($offset) {
    if ($node = $this->offsetGet($offset)) {
      $node->parentNode->removeChild($node);
    }
  }
  
  public function offsetGet($offset) {
    if (is_int($offset)) {
      return $this->list->item($offset);
    } else {
      return $this->getitem($offset);
    }
  }
  
  //Iterator
  function rewind() {
    $this->index = 0;
  }
  
  function current() {
    if ($this->valid())         return $this->list->item($this->index);
    return false;
  }
  
  function key() {
    return $this->list->item($this->index)->nodeName;
  }
  
  function next() {
    ++$this->index;
  }
  
  function valid() {
    return ($this->index >= 0) && ($this->index < $this->count());
  }
  
  function add($name) {
    $result = $this->node->ownerDocument->createElement($name);
    $this->node->appendChild($result);
    return self::newinstance($result);
  }
  
  public static function addvalue($owner, $name, $value) {
    $result = $owner->ownerDocument->createElement($name);
    $textnode = $owner->ownerDocument->createTextNode($value);
    $result->appendChild($textnode);
    $owner->appendChild($result);
    Return $result;
  }
  
  public static function append(DOMNode $owner, $name) {
    $result = $owner->ownerDocument->createElement($name);
    $owner->appendChild($result);
    return $result;
  }
  
  public static function setvalue(DOMNode $node, $value) {
    if ($node->hasChildNodes()) {
      //replace
      if ($value instanceof DOMNode) {
        $node->replaceChild($value, $node->firstChild);
      } else {
        $textnode = $node->ownerDocument->createTextNode($value);
        $node->replaceChild($textnode , $node->firstChild);
      }
    } else {
      //add
      if ($value instanceof DOMNode) {
        $node->appendChild($value);
      } else {
        $textnode = $node->ownerDocument->createTextNode($value);
        $node->appendChild($textnode);
      }
    }
  }
  
}//class

?>