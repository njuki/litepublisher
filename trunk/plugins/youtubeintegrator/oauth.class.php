<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* based on lib_oauth - A standalone PHP4 OAuth library
**/

	# lib_oauth - A standalone PHP4 OAuth library
	#
	# By Cal Henderson <cal@iamcal.com>
	#
	# Heavily based on the PHP5 OAuth library
	# http://code.google.com/p/oauth-php/
	#
	# Patches from:
	#  * Kellan <kellan@pobox.com>
	#    - Flickr compatibility fix
	#  * Zhihong Zhang <zhihong.zhang@corp.aol.com>
	#    - quoted key names for E_WARNINGS mode
	#    - caught the urlencode() vs rawurlencode() bug
	#  * Paul Webster <paul@dabdig.com>
	#    - POST support
	#    - cURL support
	#
	# This program is free software; you can redistribute it and/or modify
	# it under the terms of the GNU General Public License as published by
	# the Free Software Foundation; either version 2 of the License, or
	# (at your option) any later version.
	# 
	# This program is distributed in the hope that it will be useful,
	# but WITHOUT ANY WARRANTY; without even the implied warranty of
	# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	# GNU General Public License for more details.
	# 
	# You should have received a copy of the GNU General Public License
	# along with this program; if not, write to the Free Software
	# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	#


class toauth {
	# use fopen wrappers for GETs - this will usually work on servers
	# that don't have cURL installed, but wont work for POST requests
	public $use_fopen;
	# seconds before HTTP requests time out (cURL only)
	public $timeout;

public function __construct() {
	$this->use_fopen = false;
	$this->timeout = 2;
}

	private function getsign($key_bucket, $url, $params=array(), $method="GET"){
		# fold query params passed on the URL into params array
		$url_parsed = parse_url($url);
		if (isset($url_parsed['query'])){
			parse_str($url_parsed['query'], $url_params);
			$params = array_merge($params, $url_params);
		}
		
		# create the request thingy
		$params['oauth_version']		= '1.0';
		$params['oauth_nonce']		= md5('_oauth_rand_' . microtime() . mt_rand());
		$params['oauth_timestamp']	= gmmktime();
		$params['oauth_consumer_key']	= $key_bucket['oauth_key'];

		if (isset($key_bucket['user_key'])){
			$params['oauth_token']		= $key_bucket['user_key'];
		}

		$params['oauth_signature_method']	= 'HMAC-SHA1';
		$params['oauth_signature']	= $this->getsignature($key_bucket, $url, $params, $method);
		return $params;
	}

	public function geturl($key_bucket, $url, $params=array(), $method="GET"){
return $this->normalize_url($url) . "?" . $this->getparams($this->getsign($key_bucket, $url, $params, $method));
	}

	public function getdata($key_bucket, $url, $params=array(), $method="GET"){
		$url = $this->geturl($key_bucket, $url, $params, $method);
		if ($method == 'POST'){
			list($url, $postdata) = explode('?', $url, 2);
		}else{
			$postdata = null;
		}

		return $this->dorequest($url, $method, $postdata);
	}

	private function getsignature($key_bucket, $url, $params, $method){
		$sig = array(
			rawurlencode(strtoupper ($method)),
			preg_replace('/%7E/', '~', rawurlencode($this->normalize_url($url))),
			rawurlencode($this->get_signable($params)),
		);

		$key = rawurlencode($key_bucket['oauth_secret']) . "&";

		if (isset($key_bucket['user_key'])){
			$key .= rawurlencode($key_bucket['user_secret']);
		}

		$raw = implode("&", $sig);
return base64_encode($this->hmac_sha1($raw, $key, TRUE));
	}

	private function normalize_url($url){
		$parts = parse_url($url);
		$port = '';
		if (array_key_exists('port', $parts) && $parts['port'] != '80'){
			$port = ':' . $parts['port'];
		}
		return $parts['scheme'] . '://' .  $parts['host'] . $port . $parts['path'];
	}

	private function get_signable($params){
		ksort($params);
		$total = array();
		foreach ($params as $k => $v) {
			if ($k == "oauth_signature") continue;
			$total[] = rawurlencode($k) . "=" . rawurlencode($v);
		}
		return implode("&", $total);
	}

	private function getparams($params){
		$total = array();
		foreach ($params as $k => $v) {
			$total[] = rawurlencode($k) . "=" . rawurlencode($v);
		}
return implode("&", $total);
	}

	public function getauthorization($key_bucket, $params) {
$params = $this->getsign($key_bucket, '', $params, 'post');
ksort($params);
		$result = array();
		foreach ($params as $k => $v) {
			$result[] = sprintf('%s="%s"', $k, urlencode($v));
		}
return implode(', ', $result);
	}


	private function hmac_sha1($data, $key, $raw=TRUE){
		if (strlen($key) > 64){
			$key =  pack('H40', sha1($key));
		}

		if (strlen($key) < 64){
			$key = str_pad($key, 64, chr(0));
		}

	$_ipad = (substr($key, 0, 64) ^ str_repeat(chr(0x36), 64));
		$_opad = (substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64));

		$hex = sha1($_opad . pack('H40', sha1($_ipad . $data)));

		if ($raw){
			$bin = '';
			while (strlen($hex)){
				$bin .= chr(hexdec(substr($hex, 0, 2)));
				$hex = substr($hex, 2);
			}
			return $bin;
		}

		return $hex;
	}

	public function gettoken(&$key_bucket, $url, $params=array()){
		if ($bits = $this->getbits($this->geturl($key_bucket, $url, $params))) {
		$key_bucket['request_key']	= $bits['oauth_token'];
		$key_bucket['request_secret']	= $bits['oauth_token_secret'];
		if ($key_bucket['request_key'] && $key_bucket['request_secret']){
			return true;
		}
}
		return false;
	}

	private function getbits($url){
		if ($crap = $this->dorequest($url)) {
		$bits = explode("&", $crap);
		$result = array();
		foreach ($bits as $bit){
			list($k, $v) = explode('=', $bit, 2);
			$result[urldecode($k)] = urldecode($v);
		}

		return $result;
}
return false;
	}

	public function getaccess(&$key_bucket, $url, $params=array()){
		$key_bucket['user_key']		= $key_bucket['request_key'];
		$key_bucket['user_secret']	= $key_bucket['request_secret'];
		if ($bits = $this->getbits($this->geturl($key_bucket, $url, $params))) {
		$key_bucket['user_key']		= $bits['oauth_token'];
		$key_bucket['user_secret']	= $bits['oauth_token_secret'];
		if ($key_bucket['user_key'] && $key_bucket['user_secret']){
			return true;
		}
}
		return false;
	}

	private function dorequest($url, $method='GET', $postdata=null){
		if ($this->use_fopen && $method == 'GET'){
if ($data = file($url)) {
return implode("", $data);
}
return false;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); 	// Get around error 417
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

		if ($method == 'GET'){
			# nothing special for GETs
		}elseif ($method == 'POST'){
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		}else{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		}
		
		$response = curl_exec($ch);
		$headers = curl_getinfo($ch);
		curl_close($ch);
//var_dump($response, $headers);
	        if ($headers['http_code'] != '200') return false;
		return $response;
	}
}//class
	
?>