<?php
include (__DIR__."/config.php");
include (__DIR__."/db.class.php");
include (__DIR__."/telegram.php");
include (__DIR__."/functions.php");
$GLOBALS['TELEGRAM'] = new TELEGRAM_BOTAPI($CONFIG['TELEGRAM_BOT_TOKEN']);
session_start();
if ($_REQUEST['action']=='login') {
	$_SESSION['authorized'] = false;
	if (($_REQUEST['login']==$CONFIG['ADMIN_LOGIN'] and $_REQUEST['password']==$CONFIG['ADMIN_PASSWORD']) or $_REQUEST['token']==$CONFIG['AUTHTOKEN']) {
		$_SESSION['authorized'] = true;
		header("Location: ./?action=bot_users_table");
	} else {
		echo 'Login failed: incorrect login or password<br>'.authform();
	}
	exit();
}

if ($_SESSION['authorized']===true) {
	if ($_REQUEST['action']=='sendmessage') {
		TG_send_multi_message(array_filter(array_keys($_REQUEST['user_id']),function ($value){
			return is_numeric($value);
		}),$_REQUEST['message_text']);
		header("Location: ./?action=bot_users_table&msgs_sent=1");
		exit();
	} elseif ($_REQUEST['action']=='logout') {
		session_destroy();
		unset($_SESSION);
		header("Location: ./?action=authform");
		exit();
	} elseif ($_REQUEST['action']=='messages_history') {
		echo messages_history(intval($_REQUEST['id']));
		exit();
	} elseif ($_REQUEST['action']=='bot_users_table') {
		echo bot_users_table();
		exit();
	} else {
		echo bot_users_table();
		exit();
	}
} else {
	echo authform();
	exit();
}