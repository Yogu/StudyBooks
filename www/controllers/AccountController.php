<?php
defined('IN_APP') or die;

class AccountController extends Controller {
	public function login() {
		$data = new stdClass();
		if (isset($this->request->post['login'])) {
			$userName = $this->request->post['user'];
			$password = $this->request->post['password'];
			$user = User::getByName($userName);
			if ($user && $user->checkPassword($password)) {
				if ($user->isBanned)
					$this->data->isBanned = true;
				else {
					$session = new Session();
					$session->user = $user;
					$session->ip = $this->request->ip;
					$session->userAgent = $this->request->userAgent;
					$session->insert();
					$user->updateLoginTime();
					$this->request->cookies->session = $session->key;
					$this->request->session = $session;
					$this->request->user = $user;
					
					if (isset($this->request->post['referer']))
						$url = $this->request->post['referer'];
					else
						$url = $this->request->internalURL;
					return $this->redirectToURL($url);
				}
			} else 
				$this->data->isFailed = true;
		}
		
		if ($this->request->session && isset($this->request->post['logout'])) {
			$this->request->session->logout();
			$this->request->cookies->session = '';
			$this->request->session = null;
			$this->request->user = null;
					
			if (isset($this->request->post['referer']))
				$url = $this->request->post['referer'];
			else
				$url = $this->request->internalURL;
			return $this->redirectToURL($url);
		}
		
		return $this->view();
	}
	
	public function changePassword() {
		if ($r = $this->requireLogin()) return $r;
		
		if (isset($this->request->post['submit'])) {
			$oldPassword = $this->request->post['oldPassword'];
			$newPassword = $this->request->post['newPassword'];
			$passwordConfirmation = $this->request->post['passwordConfirmation'];
			$this->data->errors = '';
			if (!$this->request->user->checkPassword($oldPassword))
				$this->data->errors .= '<p><i>Current password</i> is wrong.</p>';
			if (!$newPassword)
				$this->data->errors .= '<p>Please enter a new password.</p>';
			if ($newPassword != $passwordConfirmation)
				$this->data->errors .= '<p>The password confirmation does not match the new password.</p>';
			if (!$this->data->errors) {
				$this->request->user->rawPassword = $newPassword;
				$this->request->user->changePassword();
				return $this->view('passwordChanged');
			}
		}
		return $this->view();
	}
}
