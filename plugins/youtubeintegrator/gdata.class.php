<?php

class tyoutubeoauth extends toauth {
public $devkey;

public function __construct() {
parent::__construct();
$this->devkey = '';
}

public function getkeys() {
return array('scope' => 'http://gdata.youtube.com');
}

public function getextraheaders() {
return array(
'Content-Type: application/atom+xml; charset=UTF-8',
'GData-Version: 2',
'X-GData-Key: key=' . $this->devkey
);
}

}//class

class tgdata {
public $oauth;

public function __construct() {
$oauth = new tyoutubeoauth();
$oauth->urllist['gettokenupload'] = 'http://gdata.youtube.com/action/GetUploadToken';
$oauth->devkey = '';
$this->oauth = $oauth;
}

public function getuploadtoken($title, $description, $category, $keywords) {
$dom = new domDocument();
    $dom->encoding = 'utf-8';
    $dom->appendChild($dom->createComment('generator="Lite Publisher'));
    $entry  = $dom->createElement('entry');
    $dom->appendChild($entry);
    
    AddAttr($entry, 'xmlns', 'http://www.w3.org/2005/Atom');
    AddAttr($entry, 'xmlns:media', 'http://search.yahoo.com/mrss/');
    AddAttr($entry, 'xmlns:yt', 'http://gdata.youtube.com/schemas/2007');
    $group = AddNode($entry, 'media:group');
    $node = AddNodeValue($group, 'media:title', $title);
AddAttr($node, 'type', 'plain');

    $node = AddNodeValue($group, 'media:category', $category);
AddAttr($node, 'scheme', 'http://gdata.youtube.com/schemas/2007/categories.cat');

    $node = AddNodeValue($group, 'media:keywords', $keywords);

$postdata = $dom->saveXML();
if ($response = $this->oauth->postdata($postdata, $this->oauth->urllist['gettokenupload'])) {
/*
array(2) {
  ["url"]=>
  string(174) "http://uploads.gdata.youtube.com/action/FormDataUpload/AIwbFAQxRT5mv6Y0uccQeN1FuWuX-dgX9IdTktzvbQQvv_ajheJzuE0mK5JIwQSCZq_l1I7QVkfjj6SImefsZ1y5WfaU_TUu24DAEPpOgUZ9q1RE6uc62ZU"
  ["token"]=>
  string(290) "AIwbFARvRGwEGdKMYE_c4jlanBZUGOSERhjFsFcqfXh757AUr89IOO8vRpdDXRLmwMSSwddJYAJVL_fsZmoKoZ8iz2e6ha8oAV5AIZn1AEFPTucyjehmovN5fI9k2LJ2x2QiqCOitk0P0wJis9JVnR9ategVnzEblhzEJu46U_wq1geHN2ZAU5Mqs3worKmgxlbJ3PtztGJjc-vkd6WRLJEiKhhxLCIA_9ibBJ39ZY95XLH5NdwZCNUpbw0JiiXO6EzaXEYVcym12xup0g9Dg4OwMa3glsOCSg"
}
*/
		$result = xml2array($response);
var_dump($result);
return $result['response'];
}
return false;
}

public function request($arg) {
switch ($arg) {
case 'accesstoken':
return $this->getaccesstoken();

case 'uploaded':
return $this->uploaded();
}
}

}//class
?>