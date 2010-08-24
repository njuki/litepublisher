<?php

class tgdata extends toauth {
public $devkey;
public $devsecret;
public $domain;
public $urlaccess;
public $urluploaded;

public function __construct($devkey, $devsecret) {
$this->devkey = $devkey;
$this->devsecret = $devsecret;
$this->domain = $_SERVER['HTTP_HOST'];
$this->urlaccess = '/oauth/access/';
$this->urluploaded = '/oauth/uploaded/';
}


public function getuploadtoken($accesstoken, $secret, $title, $description, $category, $keywords) {
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
//echo $postdata ;

$this->domain = 'key';
	$keys = array(
		'oauth_key'		=> $this->domain,
	'oauth_secret'		=> $secret,
'user_key' => $accesstoken
);
$params = array();
//'scope' => 'http://gdata.youtube.com');
//$oauth = toauth::instance();
$oauth = new toauth();
$authorization = $oauth->getauthorization($keys, $params);
//var_dump($authorization );

		$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 
//'http://litepublisher.ru/auth/head.php');
//'http://gdata.youtube.com/action/GetUploadToken');
//$url);
'http://term.ie/oauth/example/echo_api.php');

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'Authorization: OAuth '. $authorization,
//sprintf('Authorization: AuthSub token="%s"', urlencode($accesstoken)),
'Content-Type: application/atom+xml; charset=UTF-8',
'Content-Length: ' . strlen($postdata ),
'GData-Version: 2',
'X-GData-Key: key=' . $this->devkey,
'Expect:'));

		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 2);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata );
		
		$response = curl_exec($ch);
		$headers = curl_getinfo($ch);
		curl_close($ch);
var_dump($response , $headers);
	        if ($headers['http_code'] != "200") return false;
		$result = xml2array($response);
return $result['response'];
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