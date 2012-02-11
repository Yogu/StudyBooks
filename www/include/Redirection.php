<?php 
defined('IN_APP') or die;

/**
 * Defines a redirection response
 */
class Redirection extends Response {
	private $_location;
	private $_statusCode;
	
	/**
	 * Creates a redirection response
	 * 
	 * @param string|null $location the new location or null to redirect to the
	 *   request url
	 * @param string $code the HTTP status code, by default 303 See other
	 */
	public function __construct(Request $request, $location = null, $code = 303) {
		if ($location === null)
			$location = $request->url;
		elseif (!preg_match('/^[a-zA-Z0-9+.-]+\:/', $location))
			$location = ROOT_URL.$location;
		$this->_location = $location;
		$this->_statusCode = $code;
	}
	
	/**
	 * Gets the content to be sent
	 * 
	 * @return string
	 */
	public function getContent() {
		return '<?xml version="1.0" encoding="utf-8" ?'.'><!DOCTYPE html>'.
			'<html lang="en"><head><meta charset="utf-8" />'.
			'<title>Redirection</title><meta http-equiv="refresh" '.
			'content="0;url='.htmlspecialchars($this->_location).'" />'.
			'<script type="text/javascript">document.location=\''.
			addslashes($this->_location).'\';'.
			'</script></head><body><h1>Redirection</h1>'.
			'<p>You have been redirected to <a href="'.
			htmlspecialchars($this->_location).'">'.
			htmlspecialchars($this->_location).'</a>.</p></body></html>';
	}
	
	/**
	 * Gets the MIME type of this response
	 * 
	 * @return string
	 */
	public function getContentType() {
		return 'text/html';
	}
	
	/**
	 * Gets the HTML status code to be sent (e.g. 200 for OK)
	 * 
	 * @return int
	 */
	public function getStatusCode() {
		return $this->_statusCode;
	}
	
	/**
	 * Sends the headers
	 */
	protected function sendHeaders() {
		parent::sendHeaders();
		header('Location: '.$this->_location, true, $this->_statusCode);
	}
}


