$(document).ready( function(){
	sendAjax({action: 'login'});
});
function sendAjax(data){
	$.ajax({
		url: "user.php",
		data: data,
		success: function( data ){
			$("#itsBusinessTime").html(data);
			listeners();
		}
	});
}
function listeners(){
	$('#loginLink').click(function(){sendAjax({action: 'login'}); return false;});
	$('#logoutLink').click(function(){sendAjax({action: 'logout'}); return false;});
	$('#signupLink').click(function(){sendAjax({action: 'signup'}); return false;});
	$('#forgotPasswordLink').click(function(){sendAjax({action: 'forgotPassword'}); return false;});
	$('#changePasswordLink').click(function(){sendAjax({action: 'changePassword'}); return false;});

	$('#loginForm').submit(function(){
		sendAjax({
			action: 'login', 
			username: $('#usernameInput').val(), 
			password: $('#passwordInput').val() 
		}); 
		return false;
	});
	
	$('#signupForm').submit(function(){
		sendAjax({
			action: 'signup',
			username: $('#usernameInput').val(), 
			confirmUsername: $('#confirmUsernameInput').val(), 
			password: $('#passwordInput').val() 
		});
		return false;
	});
	$('#forgotPasswordForm').submit(function(){
		sendAjax({
			action: 'forgotPassword',
			username: $('#usernameInput').val(), 
		});
		return false;
	});
	$('#changePasswordForm').submit(function(){
		sendAjax({
			action: 'changePassword',
			password: $('#passwordInput').val(), 
			newPassword: $('#newPasswordInput').val(), 
		});
		return false;
	});
}