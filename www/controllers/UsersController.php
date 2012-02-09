<?php
defined('IN_APP') or die;

class UsersController extends Controller {
	public function index() {
		if ($r = $this->requireAdmin()) return $r;
		
		$this->data->users = User::getList("ORDER BY name ASC");
		
		return $this->view();
	}

	public function add() {
		if ($r = $this->requireAdmin()) return $r;
		
		$this->data->user = new User();
		
		if ($this->request->post['submit']) {
			$this->data->errors = $this->loadUser($this->data->user, false);
				
			if (!$this->data->errors) {
				$this->data->user->insert();
				return $this->redirection('users/'.$this->data->user->id);
			}
		}
		
		return $this->view();
	}

	public function details() {
		if ($r = $this->requireAdmin()) return $r;
		
		$this->data->user = User::getByID($this->request->get['id']);
		if (!$this->data->user) {
			$this->data->details =
				'There is no user with the specified name.';
			return new View($this->request, '404', 'errors', 404);
		}
		
		return $this->view();
	}

	public function edit() {
		if ($r = $this->requireAdmin()) return $r;
		
		$this->data->user = User::getByID($this->request->get['id']);
		if (!$this->data->user) {
			$this->data->details =
				'There is no user with the specified name.';
			return new View($this->request, '404', 'errors', 404);
		}
	
		if ($this->request->post['submit']) {
			$this->data->errors = $this->loadUser($this->data->user, true);
				
			if (!$this->data->errors) {
				$this->data->user->saveChanges();
				if ($this->data->user->rawPassword)
					$this->data->user->changePassword();
				return $this->redirection('users/'.$this->data->user->id);
			}
		}
		
		return $this->view();
	}

	public function delete() {
		if ($r = $this->requireAdmin()) return $r;
		
		$this->data->user = User::getByID($this->request->get['id']);
		if (!$this->data->user) {
			$this->data->details =
				'There is no user with the specified name.';
			return new View($this->request, '404', 'errors', 404);
		}
	
		if ($this->request->post['confirm']) {
			$this->data->user->delete();
			return $this->view('deleted');
		} else if ($this->request->post['cancel'])
			return $this->redirection('users/'.$this->data->user->id);
		
		return $this->view();
	}
	
	private function loadUser(&$user, $isEditing) {
		$user->name = $this->request->post['name'];
		$user->email = $this->request->post['email'];
		$user->role = $this->request->post['role'];
		if (!in_array($user->role, array('guest', 'poster', 'admin'), true))
			$user->role = 'poster';
		$user->isBanned = !!$this->request->post['isBanned'];
		$user->rawPassword = $this->request->post['password'];
		$passwordConfirmation = $this->request->post['passwordConfirmation'];
		
		if (!$user->name)
			$errors .= '<p>Please enter a user name.</p>';
		if (!$isEditing && User::getByName($user->name))
			$errors .= '<p>This user name is already in use.</p>';
		if (!$isEditing && !$user->rawPassword)
			$errors .= '<p>Please enter a password.</p>';
		if ($user->rawPassword != $passwordConfirmation)
			$errors .= '<p>Password confirmation does not match the password.</p>';
		return $errors;
	}
	
	public function massCreate() {
		$this->data->userCount = 50;
		$this->data->users = array();
		$this->data->errors = array();
		
		if ($this->request->post['listAsText']) {
			$list = explode("\n", $this->request->post['listAsText']);
			for ($i = 0; $i < count($list); $i++) {
				$line = $list[$i];
				list($name, $email) = explode(';', $line, 2);
				if ($this->request->post['invertNames']) {
					list($lastName, $firstName) = explode(',', $name, 2);
					$name = trim($firstName.' '.$lastName);
				}
				$this->request->post['name'.$i] = $name;
				$this->request->post['email'.$i] = $email;
				$this->request->post['role'.$i] = 'poster';
			}
			if (count($list) > $this->data->userCount)
				$this->data->userCount = count($list);
		}
		
		$submitted = $this->request->post['submit'];
		$check = $submitted || $this->request->post['check'];
		
		for ($i = 0; $i < $this->data->userCount; $i++) {
			$this->data->users[$i] = new User();
			if ($check) {
				$this->data->users[$i]->name = $this->request->post['name'.$i];
				$this->data->users[$i]->email = $this->request->post['email'.$i];
				$this->data->users[$i]->role = $this->request->post['role'.$i];
				if (!in_array($this->data->users[$i]->role, array('guest', 'poster', 'admin')))
					$this->data->users[$i]->rolej = 'poster';
	
				$errors = '';
				if ($this->data->users[$i]->name) {
					if (User::getByName($this->data->users[$i]->name))
						$errors .= '<p>This name is already in use.</p>';
					if (!$this->data->users[$i]->email)
						$errors .= '<p>Please enter an email address.</p>';
				}
				if ($errors) {
					$this->data->errors[$i] = $errors;
					$hasErrors = true;
				}
			}
		}
	
		if ($submitted && !$hasErrors) {
			foreach ($this->data->users as $user) {
				if ($user->name) {
					$user->rawPassword = Security::generateRandomPassword();
					$user->insert();
					if ($this->sendWelcomeMail($user))
						$this->data->successful[] = $user;
					else
						$this->data->failed[] = $user;
				}
			}
			return $this->view('massCreated');
		}
		
		return $this->view();	
	}

	private function sendWelcomeMail($user) {
		$mail = new Mail();
		$mail->title = 'Password for '.Config::$config->site->title;
		$mail->plainContent = "Hi $user->name!\n\n".Config::$config->mail->welcomeBody."\n\n".
			"On the website ".$this->request->urlPrefix." you can log in using the following credentials:\n\n".
			"User Name: ".$user->name."\n".
			"Password: ".$user->rawPassword."\n\n".
			Config::$config->mail->greetings;
		return $mail->send($user->email, $user->name);
	}
}

?>
