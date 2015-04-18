<?php
session_start();

require_once('userClass.php');
$user = new User;
$result = 'x';

if($_GET['action'] == 'signup'){
	if(array_key_exists('username', $_GET)){
		$result = $user->signup();	
	}
	else{
		$result = $user->signupForm();
	}	
}
elseif($_GET['action'] == 'forgotPassword'){
	if(array_key_exists('username', $_GET)){
		$result = $user->forgotPassword();	
	}
	else{
		$result = $user->forgotPasswordForm();
	}	
}
elseif($_GET['action'] == 'changePassword'){
	if(array_key_exists('password', $_GET)){
		$result = $user->changePassword();	
	}
	else{
		$result = $user->changePasswordForm();
	}	
}
elseif($_GET['action'] == 'resetPassword'){
	$result = $user->resetPassword();
}
elseif($_GET['action'] == 'logout'){
	session_destroy();
	$result = $user->loginForm();
}
else{
	if(array_key_exists('username', $_GET)){
		$result = $user->login();	
	}
	elseif(array_key_exists('username',$_SESSION)){
		$user->username = $_SESSION['username'];
		$result = 'You are logged in!<ul class="list-inline"><li><a href="#" id="logoutLink">Logout</a></li><li><a href="#" id="changePasswordLink">Change Password</a></li></ul>';
	}
	else{
		$result = $user->loginForm();
	}	
}
echo $result;


