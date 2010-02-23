<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tfoafutil  extends tevents {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getfoafdom(&$foafurl) {
$s = http::get($foafurl);
if (!$s) return false;
      if (!$this->isfoaf($s)) {
      $foafurl = $this->discoverfoafurl($s);
      if (!$foafurl) return false;
$s = http::get($foafurl);
if (!$s) return false;
          if (!$this->isfoaf($s)) return false;
        }

        $dom = new domDocument;
    $dom->loadXML($s);
    return $dom;
    }
    
      public function getinfo($url) {
$dom = $this->getfoafdom($url);
if (!$dom) return false;
      $person = $dom->getElementsByTagName('RDF')->item(0)->getElementsByTagName('Person')->item(0);
            $result = array(
      'nick' => $person->getElementsByTagName('nick')->item(0)->nodeValue,
      'url' => $person->getElementsByTagName('weblog')->item(0)->attributes->getNamedItem('resource')->nodeValue,
      'foafurl' => $url
      );
      return $result;
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
  
  public function checkfriend($foafurl) {
$dom = $this->getfoafdom($foafurl);
if (!$dom) return false;

        $knows = $dom->getElementsByTagName('knows');
        foreach ($knows  as $node) {
          $blog = $node->getElementsByTagName('Person')->item(0)->getElementsByTagName('weblog')->item(0)->attributes->getNamedItem('resource')->nodeValue;
          $seealso = $node->getElementsByTagName('Person')->item(0)->getElementsByTagName('seeAlso')->item(0)->attributes->getNamedItem('resource')->nodeValue;
          if (($blog == litepublisher::$options->url . '/') && ($seealso == litepublisher::$options->url . '/foaf.xml')) {
return true;
          }
        }
        return false;
        }

    public function check() {
    $result = '';
    tlocal::loadlang('admin');
    $lang = tlocal::$instance('foaf');
    $foaf = tfoaf::instance();
$items = $foaf->getapproved(0);
    foreach ($items as $id) {
    $item = $foaf->getitem(4item);
if (!$this->checkfriend($item['foafurl'])) {
        $result.= sprintf($lang['error'], $item['nick'], $item['blog'], $url);
$foaf->lock();
$foaf->setvalue($id, 'errors', ++$item['errors']);
if ($item['errors'] > 3) {
$foaf->setstatus($id, 'error');
        $result.= sprintf($lang['error'], $item['nick'], $item['blog'], $url);
}
$foaf->unlock();
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