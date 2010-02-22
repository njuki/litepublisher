<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tfoafmanager extends tevents {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getinfo($url) {
    if ($dom= $this->getfoafdocument($url)) {
      $person = $dom->getElementsByTagName('RDF')->item(0)->getElementsByTagName('Person')->item(0);
      $result = array(
      'nick' =>$person->getElementsByTagName('nick')->item(0)->nodeValue,
      //'url' =>$person->getElementsByTagName('weblog')->item(0)->attributes->getNamedItem('resource')->nodeValue,
      'url' => $url,
      'foafurl' =>$url
      );
      return $result;
    }
    return false;
  }

    public function getfoafdocument($url) {
    if ($s = http::get($url)) {
      if ($this->isfoaf($s)) {
        return $this->parse($s);
      } elseif ($url = $this->discoverfoafurl($s)) {
        if ($s = http::get($url)) {
          if ($this->isfoaf($s)) {
            return $this->parse($s);
          }
        }
      }
    }
    return false;
  }
  
  private function isfoaf(&$s) {
    return strpos($s, '<rdf:RDF') > 0;
  }
  
  private function discoverfoafurl(&$s) {
    $tag = '<link rel="meta" type="application/rdf+xml" title="FOAF" href="';
    if ($i = strpos($s, $tag)) {
      $i = $i + strlen($tag);
      $i2 = strpos($s, '"', $i);
      return substr($s, $i, $i2 - $i);
    } else {
      $tag = str_replace('"', "'", $tag);
      if ($i = strpos($s, $tag)) {
        $i = $i + strlen($tag);
        $i2 = strpos($s, "'", $i);
        return substr($s, $i, $i2 - $i);
      }
    }
    return false;
  }
  
  private function parse(&$s) {
    $dom = new domDocument;
    $dom->loadXML($s);
    return $dom;
  }
  
  private function ExtractDomain($Url) {
    $Url = strtolower(trim($Url));
    if (preg_match('/(http:\/\/|https:\/\/|)(www\.|)([-\.\w]+)\/?/', $Url, $Found)) {
      return isset($Found[3]) && !empty($Found[3]) ? $Found[3] : false;
    }
    return false;
  }
  
  private function samedomain($url, $foafurl) {
    $actions = TXMLRPCOpenAction ::instance();
    if (($foaf = $this->ExtractDomain($friend['foaf'])) && ($blog = $this->ExtractDomain($friend['blog'])) &&($from = $this->ExtractDomain($actions->from))) {
      $self = $this->ExtractDomain(litepublisher::$options->url);
      if (($foaf == $blog) && ($blog == $from) && ($from != $self)) return true;
    }
    return false;
  }
  
  public function GetUrlByID($id) {
    foreach ($this->items as $url => $item) {
      if ($id == $item['id']) return $url;
    }
    return false;
  }
  
  public function SetStatus($url, $status) {
    if (!isset($this->items[$url])) return false;
    $this->Lock();
    switch ($status) {
      case 'accepted':
      if (!$this->AcceptInvate($url)) {
        $foaf = &TFoaf::instance();
        $foaf->Add($this->items[$url]['nick'], $this->items[$url]['foaf'], $url);
        unset($this->items[$url]);
      }
      break;
      
      case 'delete':
      unset($this->items[$url]);
      break;
      
      case 'rejected':
      $this->RejectInvate($url);
      break;
      
      default:
      $this->items[$url]['status'] = $status;
    }
    $this->Unlock();
  }
  
  public function CheckFriendship() {
    $result = '';
    tlocal::loadlang('admin');
    $lang = tlocal::$data['foaf'];
    $foaf = tfoaf::instance();
    foreach ($foaf->items as $id => $item) {
      $found = false;
      $url = $item['foaf'];
      if ($dom = $this->getfoafdocument($url)) {
        $knows = $dom->getElementsByTagName('knows');
        foreach ($knows  as $node) {
          $blog = $node->getElementsByTagName('Person')->item(0)->getElementsByTagName('weblog')->item(0)->attributes->getNamedItem('resource')->nodeValue;
          $seealso = $node->getElementsByTagName('Person')->item(0)->getElementsByTagName('seeAlso')->item(0)->attributes->getNamedItem('resource')->nodeValue;
          if (($blog == litepublisher::$options->url . litepublisher::$options->home) && ($seealso == litepublisher::$options->foaf)) {
            $found = true;
            break;
          }
        }
      }
      if (!$found)  {
        $result.= sprintf($lang['error'], $item['nick'], $item['blog'], $url);
      }
    }
    
    if ($result != '')
    $this->NotifyModerator(str_replace('\n', "\n", $result), 'error');
  }
  
  private function NotifyModerator($url, $type) {
    $html = THtmlResource::instance();
    $html->section = 'foaf';
    $lang = &TLocal::instance();
    
    if ($type == 'error') {
      eval('$subject = "'. $html->errorsubj . '";');
      eval('$body = "'. $html->errorbody . '\n";');
    } else {
      $status = sprintf($lang->notify, TLocal::$data['foaf'][$type]);
      eval('$subject = "'. $html->subject . '";');
      eval('$body = "'. $html->body . '\n";');
    }
    
    tmailer::sendmail(litepublisher::$options->name, litepublisher::$options->fromemail, 'admin', litepublisher::$options->email,  $subject, $body);
  }
  
}//class

?>