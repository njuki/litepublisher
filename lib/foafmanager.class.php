<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class TFoafManager extends titems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'foafmanager';
$this->dbversion = false;
  }
  
  public function Invate($friend) {
    if (!$this->SameDomain($friend)) return false;
    $url = (string) $friend['blog'];
    if ($this->HasFriend($url)) return false;
    $this->items[$url] =  array(
    'id' => ++$this->autoid,
    'nick' => $friend['nick'],
    'foaf' => (string) $friend['foaf'],
    'status' => 'hold'
    );
    $this->Save();
    $this->NotifyModerator($url, 'invated');
    return true;
  }
  
  public function Accept($friend) {
    if (!$this->SameDomain($friend)) return false;
    $url = (string) $friend['blog'];
    $foaf = &TFoaf::instance();
    if ($foaf->HasFriend($url)) return true;
    if (!isset($this->items[$url]) || ($this->items[$url]['status'] != 'invated')) return false;
    $foaf->Add($this->items[$url]['nick'], $this->items[$url]['foaf'], $url);
    unset($this->items[$url]);
    $this->Save();
    $this->NotifyModerator($url, 'accepted');
    return true;
  }
  
  public function Reject($friend) {
    if (!$this->SameDomain($friend)) return false;
    $url = (string) $friend['blog'];
    $foaf = &TFoaf::instance();
    if ($foaf->HasFriend($url))  {
      $foaf->DeleteUrl($url);
      $this->NotifyModerator($url, 'rejected');
      return true;
    } elseif (isset($this->items[$url])) {
      unset($this->items[$url]);
      $this->Save();
      $this->NotifyModerator($url, 'reject');
      return true;
    }
    return false;
  }
  
  public function Add($url) {
    global $Options;
    if ($ping = TPinger::Discover($url)) {
      $actions =&TXMLRPCOpenAction::instance();
      if ($actions->CallAction($ping, 'friend.invate', $this->GetProfile())) {
        if ($friend = $this->GetFriendInfo($url)) {
          $friend['status'] = 'invated';
          $this->items[$url] = $friend;
          $this->Save();
          return true;
        }
      }
    }
    return false;
  }
  
  public function AcceptInvate($url) {
    global $Options;
    if (!isset($this->items[$url])) return false;
    if ($ping = TPinger::Discover($url)) {
      $actions =&TXMLRPCOpenAction::instance();
      if ($actions->CallAction($ping, 'friend.accept', $this->GetProfile())) {
        $foaf = &TFoaf::instance();
        $foaf->Add($this->items[$url]['nick'], $this->items[$url]['foaf'], $url);
        unset($this->items[$url]);
        $this->Save();
        return true;
      }
    }
    return false;
  }
  
  public function RejectInvate($url) {
    global $Options;
    if (!isset($this->items[$url])) return false;
    $this->items[$url]['status'] = 'rejected';
    $this->Save();
    
    if ($ping = TPinger::Discover($url)) {
      $actions =&TXMLRPCOpenAction::instance();
      if ($actions->CallAction($ping, 'friend.reject', $this->GetProfile())) {
        return true;
      }
    }
    return false;
  }
  
  public function Ban($url) {
    $this->items[$url]['status'] = 'ban';
    $this->Save();
  }
  
  private function GetProfile() {
    global $Options;
    $profile = &TProfile::instance();
    return array(
    'nick' => $profile->nick,
    'foaf' => $Options->foaf,
    'blog' => $Options->url . $Options->home
    );
  }
  
  private function GetFriendInfo($url) {
    if ($dom= $this->GetFoaf($url)) {
      $person = $dom->getElementsByTagName('RDF')->item(0)->getElementsByTagName('Person')->item(0);
      $result = array(
      'id' => ++$this->autoid,
      'nick' =>$person->getElementsByTagName('nick')->item(0)->nodeValue,
      //'blog' =>$person->getElementsByTagName('weblog')->item(0)->attributes->getNamedItem('resource')->nodeValue
      'foaf' =>$url
      );
      return $result;
    }
    return false;
  }
  
  public function GetFoaf(&$url) {
    global $paths;
    require_once($paths['libinclude'] . 'utils.php');
    if ($s = GetWebPage($url)) {
      if ($this->IsFoaf($s)) {
        return $this->ParseFoaf($s);
      } elseif ($url = $this->GetFoafUrl($s)) {
        if ($s = GetWebPage($url)) {
          if ($this->IsFoaf($s)) {
            return $this->ParseFoaf($s);
          }
        }
      }
    }
    return false;
  }
  
  private function IsFoaf(&$s) {
    return strpos($s, '<rdf:RDF') > 0;
  }
  
  private function GetFoafUrl(&$s) {
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
  
  private function &ParseFoaf(&$s) {
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
  
  private function SameDomain($friend) {
    global $Options;
    $actions = &TXMLRPCOpenAction ::instance();
    if (($foaf = $this->ExtractDomain($friend['foaf'])) && ($blog = $this->ExtractDomain($friend['blog'])) &&($from = $this->ExtractDomain($actions->from))) {
      $self = $this->ExtractDomain($Options->url);
      if (($foaf == $blog) && ($blog == $from) && ($from != $self)) return true;
    }
    return false;
  }
  
  private function HasFriend($url) {
    if (isset($this->items[$url])) return true;
    $foaf = &TFoaf::instance();
    if ($foaf->HasFriend($url)) return true;
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
    global $Options;
    $result = '';
    tlocal::loadlang('admin');
    $lang = tlocal::$data['foaf'];
    $foaf = tfoaf::instance();
    foreach ($foaf->items as $id => $item) {
      $found = false;
      $url = $item['foaf'];
      if ($dom = $this->GetFoaf($url)) {
        $knows = $dom->getElementsByTagName('knows');
        foreach ($knows  as $node) {
          $blog = $node->getElementsByTagName('Person')->item(0)->getElementsByTagName('weblog')->item(0)->attributes->getNamedItem('resource')->nodeValue;
          $seealso = $node->getElementsByTagName('Person')->item(0)->getElementsByTagName('seeAlso')->item(0)->attributes->getNamedItem('resource')->nodeValue;
          if (($blog == $Options->url . $Options->home) && ($seealso == $Options->foaf)) {
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
    global $Options;
    $html = &THtmlResource::instance();
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
    
    TMailer::SendMail($Options->name, $Options->fromemail, 'admin', $Options->email,  $subject, $body);
  }
  
}//class

?>