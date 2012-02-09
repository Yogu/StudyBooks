<?php
defined('IN_APP') or die;

abstract class Response {
	private static $_statusPhrases = array( 
		 100 => 'Continue',
		 101 => 'Switching Protocols',
		 102 => 'Processing',
		 200 => 'OK',
		 201 => 'Created',
		 202 => 'Accepted',
		 203 => 'Non-Authoritative Information',
		 204 => 'No Content',
		 205 => 'Reset Content',
		 206 => 'Partial Content',
		 207 => 'Multi-Status',
		 300 => 'Multiple Choices',
		 301 => 'Moved Permanently',
		 302 => 'Found',
		 303 => 'See Other',
		 304 => 'Not Modified',
		 305 => 'Use Proxy',
		 306 => 'unused',
		 307 => 'Temporary Redirect',
		 400 => 'Bad Request',
		 401 => 'Authorization Required',
		 402 => 'Payment Required',
		 403 => 'Forbidden',
		 404 => 'Not Found',
		 405 => 'Method Not Allowed',
		 406 => 'Not Acceptable',
		 407 => 'Proxy Authentication Required',
		 408 => 'Request Time-out',
		 409 => 'Conflict',
		 410 => 'Gone',
		 411 => 'Length Required',
		 412 => 'Precondition Failed',
		 413 => 'Request Entity Too Large',
		 414 => 'Request-URI Too Large',
		 415 => 'Unsupported Media Type',
		 416 => 'Requested Range Not Satisfiable',
		 417 => 'Expectation Failed',
		 418 => 'unused',
		 419 => 'unused',
		 420 => 'unused',
		 421 => 'unused',
		 422 => 'Unprocessable Entity',
		 423 => 'Locked',
		 424 => 'Failed Dependency',
		 425 => 'No code',
		 426 => 'Upgrade Required',
		 500 => 'Internal Server Error',
		 501 => 'Method Not Implemented',
		 502 => 'Bad Gateway',
		 503 => 'Service Temporarily Unavailable',
		 504 => 'Gateway Time-out',
		 505 => 'HTTP Version Not Supported',
		 506 => 'Variant Also Negotiates',
		 507 => 'Insufficient Storage',
		 508 => 'unused',
		 509 => 'unused',
		 510 => 'Not Extended');
	
	/**
	 * Gets the content to be sent
	 * 
	 * @return string
	 */
	public abstract function getContent();
	
	/**
	 * Gets the MIME type of this response
	 * 
	 * @return string
	 */
	public abstract function getContentType();
	
	/**
	 * Gets the HTML status code to be sent (e.g. 200 for OK)
	 * 
	 * @return int
	 */
	public abstract function getStatusCode();
	
	/**
	 * Sends this response to the client.
	 */
	public function send() {
		$this->sendHeaders();
		echo $this->getContent();
	}
	
	/**
	 * Sends the headers
	 */
	protected function sendHeaders() {
		$code = $this->getStatusCode();
		header('HTTP/1.1 '.$code.' '.self::$_statusPhrases[$code], true,
			$code);
		
		$contentType = $this->getContentType();
		if (substr($contentType, 0, 4) == 'text')
			$contentType .= '; charset=UTF-8';
		header('Content-Type: '.$contentType);
	}
}

?>
