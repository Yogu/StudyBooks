<?php

/**
 * Defines a response that returns simply a string
 */
class TextResponse extends Response {
	private $_text;
	private $_contentType;
	private $_statusCode;
	public $request;
	
	/**
	 * Creates response that returns simply a string
	 * 
	 * @param string $text the text to send
	 * @param string $contentType the MIME content type string
	 * @param string|null $code the HTTP status code, by default 200 OK
	 */
	public function __construct(Request $request, $text, $contentType, $code = 200)
	{
		$this->request = $request;
		$this->_text = $text;
		$this->_contentType = $contentType;
		$this->_statusCode = $code;
	}
	
	/**
	 * Gets the content to be sent
	 * 
	 * @return string
	 */
	public function getContent() {
		return $this->_text;
	}
	
	/**
	 * Gets the MIME type of this response
	 * 
	 * @return string
	 */
	public function getContentType() {
		return $this->_contentType;
	}
	
	/**
	 * Gets the HTML status code to be sent (e.g. 200 for OK)
	 * 
	 * @return int
	 */
	public function getStatusCode() {
		return $this->_statusCode;
	}
}


