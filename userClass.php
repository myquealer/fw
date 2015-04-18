<?php

class User {
	private $dbLogin = array(
		'username' => 'fw',
		'password' => 'yT3q6EZGlwQf',
		'host' => 'localhost',
		'database' => 'fw'
	);
	
	private $db;
	private $username = '';
	private $confirmUsername = '';
	private $password = '';
	private $newPassword = '';
	private $resetPassword = '';
	private $message = '';
	private $salt = '$5$rounds=5000$ABKlWC650hNECIt1fp14NvyGFxcvFpGenj1r$';
	
	public function __construct(){
		if(array_key_exists('username', $_SESSION)){
			$this->username = $_SESSION['username'];
		}
		elseif(array_key_exists('username', $_GET)){
			$this->username = $_GET['username'];
		}
		if(array_key_exists('confirmUsername', $_GET)){
			$this->confirmUsername = $_GET['confirmUsername'];
		}
		if(array_key_exists('password', $_GET)){
			$this->password = $_GET['password'];
		}
		if(array_key_exists('newPassword', $_GET)){
			$this->newPassword = $_GET['newPassword'];
		}
		if(array_key_exists('resetPassword', $_GET)){
			$this->resetPassword = $_GET['resetPassword'];
		}
	}
	public function loginForm(){
		$header = '<h1>Login</h1>' . $this->message;
		$inputs = array(
			'username'=>array('type'=>'email','placeholder'=>'Email Address','label'=>'Email Address'),
			'password'=>array('type'=>'password','placeholder'=>'Password','label'=>'Password')
		);
		$form = $this->_returnForm($inputs,'Login');
		$links = $this->_returnLinks('login');
		return $header . $form . $links;
	}
	public function login(){
		$this->_dbConnect();
		$query = "update users set last_login = now() where user_name = '" . $this->db->escape_string($this->username) . "' and password_hash='" . $this->_encryptPassword($this->password) . "'";
		$match = $this->_dbQuery($query, false);
		if(!$match){
			$this->_setMessage('error','Email Address or Password incorrect');
			return $this->loginForm();
		}
		$_SESSION['username'] = $this->username;
		return 'Welcome ' . $this->username . '!<ul class="list-inline"><li><a href="#" id="logoutLink">Logout</a></li><li><a href="#" id="changePasswordLink">Change Password</a></li></ul>';
	}
	public function signupForm(){
		$header = '<h1>Signup</h1>' . $this->message;
		$inputs = array(
			'username'=>array('type'=>'email','placeholder'=>'Email Address','label'=>'Email Address'),
			'confirmUsername'=>array('email'=>'text','placeholder'=>'Confirm Email Address','label'=>'Confirm Email Address'),
			'password'=>array('type'=>'password','placeholder'=>'Password','label'=>'Password (must contain uppercase, lowercase, and number)', 'pattern'=>'^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$')
		);
		if(strlen($this->username)){
			$inputs['username']['value'] = $this->username;
		}
		if(strlen($this->confirmUsername)){
			$inputs['confirmUsername']['value'] = $this->confirmUsername;
		}
		$form = $this->_returnForm($inputs,'Signup');
		$links = $this->_returnLinks('signup');
		return $header . $form . $links;
	}
	public function signup(){
		if($this->username != $this->confirmUsername){
			$this->_setMessage('error','Email Addresses do not match');
			return $this->signupForm();
		}
		elseif(!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$/',$this->password)){
			$this->_setMessage('error','Password must contain uppercase, lowercase, and number');
			return $this->signupForm();
		}
		elseif(!filter_var($this->username, FILTER_VALIDATE_EMAIL)){
			$this->_setMessage('error','Email Address must be a valid format');
			return $this->signupForm();
		}
		
		$this->_dbConnect();
		$query = "select * from users where user_name = '" . $this->db->escape_string($this->username) . "'";
		$results = $this->_dbQuery($query);
		if(count($results)){
			$this->_setMessage('error','Email Address already has an account');
			return $this->loginForm();
		}
		$query = "insert into users(user_name, password_hash) values('" . $this->db->escape_string($this->username) . "', '" . $this->_encryptPassword($this->password) . "');";
		$results = $this->_dbQuery($query, false);
		$this->_setMessage('success','Account created');
		return $this->loginForm();
	}
	public function forgotPasswordForm(){
		$header = '<h1>Forgot Password</h1>' . $this->message;
		$inputs = array(
			'username'=>array('type'=>'email','placeholder'=>'Email Address','label'=>'Email Address')
		);
		$form = $this->_returnForm($inputs,'Forgot Password');
		$links = $this->_returnLinks('forgotPassword');
		return $header . $form . $links;
	}
	public function forgotPassword(){
		$this->_dbConnect();
		$newPassword = $this->_randomPassword();
		$query = "update users set password_hash = '" . $this->_encryptPassword($newPassword) . "' where user_name = '" . $this->db->escape_string($this->username) . "'";
		$match = $this->_dbQuery($query,false);
		if(!$match){
			$this->_setMessage('error','No such user found');
			return $this->forgotPasswordForm();
		}
		$message = <<<EndOfEmail
Your Feeney Wireless Password has been reset.

Your new password is $newPassword

EndOfEmail;
		mail($this->username, 'Your new FW password', $message);
		$this->_setMessage('success','Your new password has been emailed to you');
		return $this->forgotPasswordForm();
		
		
	}
	public function changePasswordForm(){
		$header = '<h1>Change Password</h1>' . $this->message;
		$inputs = array(
			'password'=>array('type'=>'password','placeholder'=>'Current Password','label'=>'Current Password'),
			'newPassword'=>array('type'=>'password','placeholder'=>'New Password','label'=>'New Password')
		);
		$form = $this->_returnForm($inputs,'Change Password');
		$links = '<ul class="list-inline"><li><a href="#" id="logoutLink">Logout</a></li></ul>';
		return $header . $form . $links;
	}
	public function changePassword(){
		if(!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$/',$this->newPassword)){
			$this->_setMessage('error','New Password must contain uppercase, lowercase, and number');
			return $this->changePasswordForm();
		}
		$this->_dbConnect();
		$query = "update users set password_hash = '" . $this->_encryptPassword($this->newPassword) . "' where user_name = '" . $this->db->escape_string($this->username) . "' and password_hash = '" . $this->_encryptPassword($this->password) . "';";
		$match = $this->_dbQuery($query,false);
		if(!$match){
			$this->_setMessage('error','Password incorrect');
			return $this->changePasswordForm();
		}
		return 'Password changed!<ul class="list-inline"><li><a href="#" id="logoutLink">Logout</a></li><li><a href="#" id="changePasswordLink">Change Password</a></li></ul>';
	}
	private function _returnForm($inputs,$button){
		$id = lcfirst(preg_replace('/\s+/', '', $button));
		$form = '<form id="' . $id . 'Form">';
		foreach($inputs as $name=>$attrs){
			$form .= '<div class="form-group">';
			if(array_key_exists('label', $attrs)){
				$form .= '<label for="' . $name . 'Input">' . $attrs['label'] . '</label>';
			}
			$form .= '<input name="' . $name . '" class="form-control" id="' . $name . 'Input" required';
			foreach($attrs as $attribute=>$value){
				$form .= ' ' . $attribute . '="' . $value . '"';
			}
			$form .= "></div>\n";
		}
		$form .= '<button type="submit" class="btn btn-default" id="' . $id . 'Button">' . $button . '</button>';
		$form .= '</form>';
		
		return $form;
	}
	private function _returnLinks($source){
		$links = '<ul class="list-inline">';
		if($source != 'login'){
			$links .= '<li><a href="#" id="loginLink">Login</a></li>';
		}
		if($source != 'signup'){
			$links .= '<li><a href="#" id="signupLink">Signup</a></li>';
		}
		if($source != 'forgotPassword'){
			$links .= '<li><a href="#" id="forgotPasswordLink">Forgot Password</a></li>';
		}
		$links .= '</ul>';
		
		return $links;
	}
	private function _dbConnect(){
		$this->db = new mysqli($this->dbLogin['host'], $this->dbLogin['username'], $this->dbLogin['password'], $this->dbLogin['database']);

		if($this->db->connect_errno > 0){
			die('Unable to connect to database [' . $this->db->connect_error . ']');
		}
	}
	private function _dbQuery($query, $return = true){
		if(!$result = $this->db->query($query)){
			die('There was an error running the query [' . $this->db->error . ']' . $query);
		}
		
		if($return){
			$resultsArray = array();
			while($row = $result->fetch_assoc()){
				$resultsArray[] = $row;
			}
			return $resultsArray;
		}
		else{
			return $this->db->affected_rows;
		}
	}
	private function _setMessage($type,$message){
		if($type == 'success'){
			$class = 'alert alert-success';
		}
		elseif($type == 'error') {
			$class = 'alert alert-danger';
		}
		else {
			$class = 'alert alert-info';
		}
		$this->message = '<div class="' . $class . '" role="alert">' . $message . '</div>';
	}
	private function _encryptPassword($password){
		return crypt($password, $this->salt);
	}
	private function _randomPassword() {
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$pass = array();
		for ($i = 0; $i < 16; $i++) {
			$n = rand(0, strlen($alphabet) - 1);
			$pass[] = $alphabet[$n];
		}
		return implode($pass);
	}
}
