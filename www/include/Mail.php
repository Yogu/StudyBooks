<?php

/**
 * Defines an email with subject, html and plain text contents and attachments
 * which can be sent
 */
class Mail {
	/**
	 * The subject
	 * 
	 * @var string
	 */
	public $title;
	/**
	 * The plain text part of the content
	 * 
	 * @var string
	 */
	public $plainContent;
	/**
	 * The html part of the content
	 * 
	 * @var string
	 */
	public $htmlContent;
	/**
	 * An array of Premanager\IO\Attachment objects
	 * 
	 * @var array
	 */
	public $attachments = array();
	/**
	 * An array of Premanager\IO\InlineAttachment objects
	 * 
	 * @var array
	 */
	public $inlineAttachments = array();
	
	/**
	 * Sends this email to the specified recipent
	 * 
	 * @param string $recipentEmail the recipent's email
	 * @param string $recipentName the recipent's name (optional)
	 * @param bool $log true to log the mail header and content
	 */
	public function send($recipentEmail, $recipentName) {
		if (count($this->attachments) || $this->htmlContent)
			return $this->extendedSend($recipentEmail, $recipentName);
		else
			return $this->simpleSend($recipentEmail, $recipentName);
	}
	
	private function extendedSend($recipentEmail, $recipentName) {
		$mainBoundary = self::generateBoundary();	
		$bodyBoundary = self::generateBoundary();
		$htmlBoundary = self::generateBoundary();
		
		$fromAddress = Config::$config->site->emailAddress;
		$fromName = Config::$config->site->title; 
	
		$from = "$fromName <$fromAddress>";

		$header  = "From: $from\n";
		$header .= "MIME-Version: 1.0\n";
		$header .= "Content-Type: multipart/mixed; ".
			"boundary=\"$mainBoundary\"\n";

		$content = "This is a multi-part message in MIME format.\n\n";

		// Body (plain & html)
		$content .=
			"--$mainBoundary\n".
			"Content-Type: multipart/alternative; boundary=\"$bodyBoundary\"\n".
			"This is a multi-part message in MIME format.\n".
			"\n".
			"--$bodyBoundary\n".
			"Content-Type: text/plain; charset=\"utf-8\"; format=flowed\n".
			"Content-Transfer-Encoding: 8bit\n\n".
			$this->plainContent."\n\n".
			"--$bodyBoundary\n".
			"Content-Type: multipart/related; boundary=\"$htmlBoundary\"\n\n\n".
			"--$htmlBoundary\n".
			"Content-Type: text/html; charset=\"utf-8\"\n".
			"Content-Transfer-Encoding: 8bit\n\n".
			$this->htmlContent.
			"\n\n";

		// Inline Attachments
		if (is_array($this->attachments))
			foreach ($this->inlineAttachments as $attachment) {
				$text = $attachment->content;
				$data = chunk_split(base64_encode($text));
				
				$content .=
					"--$htmlBoundary\n".
					"Content-Disposition: inline;\n".
					"\tfilename=\"".$attachment->fileName."\";\n".
					"Content-ID: <".$attachment->contentID.">\n".
					"Content-Length: ".strlen($text).";\n".
					"Content-Type: ".$attachment->contentID."; ".
						"name=\"".$attachment->fileName."\"\n".
					"Content-Transfer-Encoding: base64\n\n".
					$data."\n\n";
			}
		
		$content .= "--$htmlBoundary--\n\n";
		$content .= "--$bodyBoundary--\n\n";
		
		// Attachments
		if (is_array($this->attachments))
			foreach ($this->attachments as $attachment) {
				$text = $attachment->content;
	
				$data = chunk_split(base64_encode($text));
				$content .=
					"--$mainBoundary\n".
					"Content-Disposition: attachment;\n".
					"\tfilename=\"".$attachment->fileName."\";\n".
					"Content-Length: ".strlen($text).";\n".
					"Content-Type: ".$attachment->contentType.
						"; name=\"".$attachment->fileName."\"\n".
					"Content-Transfer-Encoding: base64\n\n".
					$data."\n\n";
			}

		$content .= "--$mainBoundary--\n\n";
			
		$recipentName = preg_replace('[<>\"]', '', $recipentName);
		$to = $recipentName ? "$recipentName <$recipentEmail>" : $recipentEmail;
		$subject = "=?UTF-8?Q?" . imap_8bit($this->title) . "?=";

		return mail($to, $subject, $content, $header);
	}
	
	private function simpleSend($recipentEmail, $recipentName) {
		$fromAddress = Config::$config->site->email;
		$fromName = Config::$config->site->title;
		$from = "$fromName <$fromAddress>";
		$subject = '=?UTF-8?B?'.base64_encode($this->title).'?=';
		$header = "Content-Type: text/plain; charset=\"utf-8\"\n";
		$header  .= "From: $from\n";
		$recipentName = preg_replace('[<>\"]', '', $recipentName);
		$to = $recipentName ? "$recipentName <$recipentEmail>" : $recipentEmail;
		return mail($to, $subject, $this->plainContent, $header);
	}
	
	/**
	 * Generates a random boundary divider
	 * 
	 * @return string the boundary divider
	 */
	private static function generateBoundary() {
		return "-----=".md5(uniqid(mt_rand(), 1));
	}
}


