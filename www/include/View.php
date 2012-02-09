<?php
defined('IN_APP') or die;

class View extends Response {
	public $request;
	public $action;
	public $controller;
	public $data;
	public $statisCode;
	public $fileName;
	
	public function __construct(Request $request, $action, $controller, $statusCode = '200') {
		$this->request = $request;
		$this->action = $action;
		$this->controller = $controller;
		$this->data = $request->data;
		$this->statusCode = $statusCode;
		$this->fileName = ROOT_PATH.'views/'.strtolower($controller).'/'.$action.'.php';
	}
	
	/**
	 * Gets the content to be sent
	 * 
	 * @return string
	 */
	public function getContent() {
		global $request;
		global $view;
		global $body;
		global $data;
		global $layout;
		$request = $this->request;
		$view = $this;
		$data = $this->data;
		$layout = '_layout';
		
		ob_start();
		include($this->fileName);
		$body = ob_get_contents();
		ob_end_clean();
		if ($layout) {
			ob_start();
			include(ROOT_PATH.'views/'.$layout.'.php');
			$body = ob_get_contents();
			ob_end_clean();
		}
		return $body;
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
		return $this->statusCode;
	}
	
	public function renderSubview($action, $controller = '') {
		global $layout;
		global $view;
		global $body;
		$oldLayout = $layout;
		$oldView = $view;
		$oldBody = $body;
		$view = new View($this->request, $action, $controller ? $controller : $this->controller);
		echo $view->getContent();
		$layout = $oldLayout;
		$view = $oldView;
		$body = $oldBody;
	}
}

?>
