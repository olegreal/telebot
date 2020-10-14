<?php
include (__DIR__."/config.php");
include (__DIR__."/db.class.php");
include (__DIR__."/telegram.php");
include (__DIR__."/functions.php");
$GLOBALS['TELEGRAM'] = new TELEGRAM_BOTAPI($CONFIG['TELEGRAM_BOT_TOKEN']);

$MessageType = "text";
$INPUTDATA = array(
	'postdata' => file_get_contents("php://input")
);
$INPUTDATA['postarray'] = json_decode($INPUTDATA['postdata']);

$INPUTDATA['update_id'] = $INPUTDATA['postarray']->update_id;
$fp = file("/tmp/telebot2_updates");
if (!in_array($INPUTDATA['update_id'],$fp)) {
	file_put_contents("/tmp/telebot2_updates",$INPUTDATA['update_id']."\n",FILE_APPEND);
} else {
	exit();
}

$INPUTDATA['user_id'] = $INPUTDATA['postarray']->message->from->id;
$INPUTDATA['from_username'] = "".$INPUTDATA['postarray']->message->from->first_name;
$INPUTDATA['from_username_lastname'] = "".$INPUTDATA['postarray']->message->from->last_name;
$INPUTDATA['from_usernamereal'] = "".$INPUTDATA['postarray']->message->from->username;
$INPUTDATA['message_text'] = $INPUTDATA['postarray']->message->text;
if (intval($INPUTDATA['user_id'])!=0) {
	$GLOBALS['TELEGRAM']->log($INPUTDATA);
}
if (strlen($INPUTDATA['postarray']->message->sticker->file_id)>0) {
	$GLOBALS['TELEGRAM']->sendsticker($INPUTDATA['user_id'],"".$INPUTDATA['postarray']->message->sticker->file_id);
	exit();
}

if (substr($INPUTDATA['message_text'],0,1)=="/") {
	$INPUTDATA['message_text'] = substr($INPUTDATA['message_text'],1);
}
$INPUTDATA['message_words'] = explode(" ",$INPUTDATA['message_text']);

$INPUTDATA['USERDATA'] = $GLOBALS['TELEGRAM']->get_userdata($INPUTDATA);

if ($INPUTDATA['USERDATA']['can_use_bot']!=1) {
	$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"$INPUTDATA[from_username] ! К сожалению я с вами не разговариваю.");
	exit();
}

if ($GLOBALS['TELEGRAM']->checktrigger_start($INPUTDATA['message_words'])) {
	step_start($INPUTDATA);
	exit();
}

if (mb_strtolower($INPUTDATA['message_text'])=='админка') {
	if ($INPUTDATA['USERDATA']['is_admin']==1) {
		$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"<a href='{$CONFIG['SCRIPT_URL_PREFIX']}?action=login&token={$CONFIG['AUTHTOKEN']}'>Ссылка на панель администратора</a>\nссылка доступна <b>до конца текущего часа</b>");
	} else {
		$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"К сожалению у вас нет доступа к этой функции.");
	}
	exit();
}

if ($INPUTDATA['USERDATA']['current_status']==0) {
	action_1($INPUTDATA);
	step_1($INPUTDATA);
	exit();
} elseif ($INPUTDATA['USERDATA']['current_status']==1) {
	action_2($INPUTDATA);
	step_2($INPUTDATA);
	exit();
} elseif ($INPUTDATA['USERDATA']['current_status']==2) {
	action_3($INPUTDATA);
	step_3($INPUTDATA);
	exit();
} elseif ($INPUTDATA['USERDATA']['current_status']==3) {
	action_4($INPUTDATA);
	exit();
} elseif ($INPUTDATA['USERDATA']['current_status']==4) {
	action_5($INPUTDATA);
	exit();
} elseif ($INPUTDATA['USERDATA']['current_status']==5) {
	action_6($INPUTDATA);
	exit();
} elseif ($INPUTDATA['USERDATA']['current_status']==6) {
	action_7($INPUTDATA);
	step_7($INPUTDATA);
	exit();
} elseif ($INPUTDATA['USERDATA']['current_status']==7) {
	step_7($INPUTDATA);
	exit();
}

echo "ok";
