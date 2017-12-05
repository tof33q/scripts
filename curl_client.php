<?php
/* 
	Curl client to make GET/POST calls 
	@author: Tofeeq
	@examples:
	$client = new CurlClient(array(
				'API_KEY' 			=> 1234455,
			), true);
	$params = array("param1" => "value1");
	$response = $this->post(
		'https://www.tpapartner.net/gateway/member.cfm', $params
	);
*/
class CurlClient {

	protected $_debug = false;
	protected $_ch;
	protected $_secureParams = array();
	protected $_debugLog = "";

	public function __construct($secureParams = null, $debug = false) {
		$this->_debug = $debug;
		$this->_secureParams = $secureParams;
		$this->_ch = curl_init();
		 

		curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->_ch, CURLOPT_CONNECTTIMEOUT , 20);
		curl_setopt($this->_ch, CURLOPT_TIMEOUT, 20);

		curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, 0);
	}

	protected function _arrayToUrl(array $array) {
		$url = "";
		foreach ($array as $key => $val) {
			$url .= $key . "=" . $val . "&";
		}
		return $url;
	}

	public function post($url, array $params) {
		$postvars = "";

		if (!empty($this->_secureParams)) {
			$postvars = $this->_arrayToUrl($this->_secureParams);
		}

		$postvars .= $this->_arrayToUrl($params);
		
		$this->_debugLog .= "post vars: \n";
		$this->_debugLog .= var_export($postvars, 1);
		$this->_debugLog .= "\nurl: ";
		$this->_debugLog .= $url;

		if ($this->_debug) {
			echo $this->_debugLog;
		}

		curl_setopt($this->_ch, CURLOPT_URL, $url);
		curl_setopt($this->_ch, CURLOPT_POST, 1);                //0 for a get request
		curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $postvars);

		$response = curl_exec($this->_ch);
		

		if ($this->_debug) {
			if($response === false) {
				echo "<br>\nCurl error: " . curl_error($this->_ch);
			} else {
				echo "<br>\nresposne \n<br>: " . $response;
			}
		}


		curl_close ($this->_ch);
		return $response;
	}

	public function get($url, $params = null) {
		$postvars = "";

		if (!empty($this->_secureParams)) {
			$postvars = $this->_arrayToUrl($this->_secureParams);
		}

		
		if (!empty($params)) {
			$postvars .= $this->_arrayToUrl($params);
		}

		curl_setopt($this->_ch, CURLOPT_URL, $url . "?" . $postvars);
		$response = curl_exec($this->_ch);
		curl_close ($this->_ch);
		return $response;
	}
}