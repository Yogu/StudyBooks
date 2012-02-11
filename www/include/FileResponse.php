<?php

/**
 * Defines a response that sends the contents of a file
 */
class FileResponse extends Response {
	private $_fileName;
	private $_displayFileName;
	private $_disposition;
	private $_useCache;
	private $_contentType;
	private $_statusCode;
	public $request;
	
	/**
	 * Creates a redirection response
	 * 
	 * @param string $fileName the path to the file
	 * @param string $displayFileName the sent file name (without path)
	 * @param string $contentType the MIME type of this response
	 * @param int $disposition 'inline' or 'attachment'
	 * @param bool $useCache true to allow the client to cache the file
	 * @param string|null $code the HTTP status code, by default 200 OK
	 */
	public function __construct(Request $request, $fileName, $displayFileName,
		$contentType, $disposition = 'inline', $useCache = true, $code = 200)
	{
		if (!file_exists($fileName))
			throw new RuntimeException('File not found: '. $fileName);
		
		$this->request = $request;
		$this->_fileName = $fileName;
		$this->_displayFileName = $displayFileName;
		$this->_contentType = $contentType;
		$this->_disposition = $disposition;
		$this->_useCache = $useCache;
		$this->_statusCode = $code;
	}
	
	/**
	 * Gets the content to be sent
	 * 
	 * @return string
	 */
	public function getContent() {
		return file_get_contents($this->_fileName);
	}
	
	/**
	 * Sends this response to the client.
	 * 
	 * Do not call this method manually because it allows to send a response
	 * multiple times. It's recommended to use Premanager\IO\Output::send()
	 * instead.
	 */
	public function send() {
		if ($this->sendHeaders())
			return;
			
		readfile($this->_fileName);
	}
	
	/**
	 * Sends only the headers
	 * 
	 * @return true, if the file has been cached and should not be sent
	 */
	protected function sendHeaders() {
		parent::sendHeaders();
		
		if ($this->_useCache) {
			// Send only if modified since last request
		  $fileTime = filemtime($this->_fileName);
		  $lastLoad = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ?
				strtotime(trim($_SERVER['HTTP_IF_MODIFIED_SINCE'])) : false;
		  if (!strpos(strtolower(($this->request->userAgent)), 'msie 6.0')) {
		  	// Current version must be in browser cache and file must not have been 
				// modified for 10 seconds (otherwise, changes might not be noticed) 
		    if ($lastLoad && $lastLoad == $fileTime && $fileTime + 10 < time()) {
		      if (@php_sapi_name() === 'CGI')
		        header('Status: 304 Not Modified', true, 304);
		      else
		        header('HTTP/1.0 304 Not Modified', true, 304);
		
		      // seems that we need those too ... browsers
		      header('Pragma: public');
		      header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T',
						time() + 31536000 /* 1 year */));
		 			return true;
		    } else {
		      header('Last-Modified: '.
						gmdate('D, d M Y H:i:s', $fileTime) . ' GMT');
		    }
		  }
		}
		
		header("Content-Type: ".$this->_contentType);
		header("Content-Length: " . filesize($this->_fileName));
		
		header("Content-Disposition: $this->_disposition; ".
			"filename=\"$this->_displayFileName\"");
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


