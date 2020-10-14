<?php

function step_start($INPUTDATA){
	$GLOBALS['db']->update('telegram_bot_users','user_id',[
		'user_id' => $INPUTDATA['user_id'],
		'kak_uznal' => NULL,
		'city' => NULL,
		'wants_to_help' => NULL,
		'age_gte_18' => NULL,
		'accept_risk' => NULL,
		'agree_personal_data' => NULL,
		'comment' => NULL,
		'current_status' => 0
	]);
	$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"Привет $INPUTDATA[from_username] ! Как ты о нас узнал? 😀");
}

function action_1($INPUTDATA){
	$GLOBALS['db']->update('telegram_bot_users','user_id',[
		'user_id' => $INPUTDATA['user_id'],
		'kak_uznal' => $INPUTDATA['message_text'],
		'current_status' => 1
	]);
}

function step_1($INPUTDATA){
	$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"Акция проходит 30 октября 2020.\n<b>В каком городе</b> ты бы хотел принять участие в акции?");
}

function action_2($INPUTDATA){
	$GLOBALS['db']->update('telegram_bot_users','user_id',[
		'user_id' => $INPUTDATA['user_id'],
		'city' => $INPUTDATA['message_text'],
		'current_status' => 2
	]);
}

function step_2($INPUTDATA){
	$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"А ты хочешь помочь с организацией?",'HTML',array(
		"reply_markup" => json_encode(array(
			"keyboard" => array(
				array(
					array(
						'text' => 'Да',
						'request_contact' => false
					),
					array(
						'text' => 'Нет',
						'request_contact' => false
					)
				)
			),
			"one_time_keyboard" => true,
			"resize_keyboard" => true
		))
	));
}

function action_3($INPUTDATA){
	if (in_array($INPUTDATA['message_text'],[
		'Да',
		'Нет'
	])) {
		$GLOBALS['db']->update('telegram_bot_users','user_id',[
			'user_id' => $INPUTDATA['user_id'],
			'wants_to_help' => ($INPUTDATA['message_text']=="Да"?1:-1),
			'current_status' => 3
		],[
			'wants_to_help'
		]);
	} else {
		$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"Неверный ответ, можно ответить только <b>Да</b> или <b>Нет</b> .");
		step_2($INPUTDATA);
		exit();
	}
}

function step_3($INPUTDATA){
	$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"Тебе есть 18?",'HTML',array(
		"reply_markup" => json_encode(array(
			"keyboard" => array(
				array(
					array(
						'text' => 'Да',
						'request_contact' => false
					),
					array(
						'text' => 'Нет',
						'request_contact' => false
					)
				)
			),
			"one_time_keyboard" => true,
			"resize_keyboard" => true
		))
	));
}

function action_4($INPUTDATA){
	if (in_array($INPUTDATA['message_text'],[
		'Да',
		'Нет'
	])) {
		$GLOBALS['db']->update('telegram_bot_users','user_id',[
			'user_id' => $INPUTDATA['user_id'],
			'age_gte_18' => ($INPUTDATA['message_text']=="Да"?1:-1),
			'current_status' => ($INPUTDATA['message_text']=="Да"?5:4)
		]);
		if ($INPUTDATA['message_text']=="Да") {
			step_5($INPUTDATA);
			exit();
		} else {
			step_4($INPUTDATA);
			exit();
		}
	} else {
		$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"Неверный ответ, можно ответить только <b>Да</b> или <b>Нет</b> .");
		step_3();
		exit();
	}
}

function step_4($INPUTDATA){
	$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"длинное сообщение про риски тогда участвуешь?",'HTML',array(
		"reply_markup" => json_encode(array(
			"keyboard" => array(
				array(
					array(
						'text' => 'Да',
						'request_contact' => false
					),
					array(
						'text' => 'Нет',
						'request_contact' => false
					)
				)
			),
			"one_time_keyboard" => true,
			"resize_keyboard" => true
		))
	));
}

function action_5($INPUTDATA){
	if (in_array($INPUTDATA['message_text'],[
		'Да',
		'Нет'
	])) {
		$GLOBALS['db']->update('telegram_bot_users','user_id',[
			'user_id' => $INPUTDATA['user_id'],
			'accept_risk' => ($INPUTDATA['message_text']=="Да"?1:-1),
			'current_status' => ($INPUTDATA['message_text']=="Да"?5:-1)
		]);
		if ($INPUTDATA['message_text']=="Да") {
			step_5($INPUTDATA);
			exit();
		} else {
			step_bye($INPUTDATA);
			exit();
		}
	} else {
		$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"Неверный ответ, можно ответить только <b>Да</b> или <b>Нет</b> .");
		step_4();
		exit();
	}
}

function step_5($INPUTDATA){
	$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"Ты даешь согласие на обработку персональных данных?",'HTML',array(
		"reply_markup" => json_encode(array(
			"keyboard" => array(
				array(
					array(
						'text' => 'Да',
						'request_contact' => false
					),
					array(
						'text' => 'Нет',
						'request_contact' => false
					)
				)
			),
			"one_time_keyboard" => true,
			"resize_keyboard" => true
		))
	));
}

function action_6($INPUTDATA){
	// $INPUTDATA['message_text'] = mb_strtolower($INPUTDATA['message_text']);
	if (in_array($INPUTDATA['message_text'],[
		'Да',
		'Нет'
	])) {
		$GLOBALS['db']->update('telegram_bot_users','user_id',[
			'user_id' => $INPUTDATA['user_id'],
			'agree_personal_data' => ($INPUTDATA['message_text']=="Да"?1:-1),
			'current_status' => ($INPUTDATA['message_text']=="Да"?6:-1)
		]);
		if ($INPUTDATA['message_text']=="Да") {
			step_6($INPUTDATA);
			exit();
		} else {
			step_bye($INPUTDATA);
			exit();
		}
	} else {
		$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"Неверный ответ, можно ответить только <b>Да</b> или <b>Нет</b> .");
		step_4();
		exit();
	}
}

function step_6($INPUTDATA){
	$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"Ништяк, что еще расскажешь?");
}

function action_7($INPUTDATA){
	$GLOBALS['db']->update('telegram_bot_users','user_id',[
		'user_id' => $INPUTDATA['user_id'],
		'comment' => $INPUTDATA['message_text'],
		'current_status' => 7
	]);
}

function step_7($INPUTDATA){
	$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"Спасибо , на связи");
}

function step_bye($INPUTDATA){
	$GLOBALS['TELEGRAM']->sendmessage($INPUTDATA['user_id'],"Ну ок, если что пиши /start . До свидания.");
}

function authform(){
	return "<form method='post' action='?action=login'>
<input type=text name=login placeholder='login'><br>
<input type=password name=password placeholder='password'><br>
<input type=submit value='Войти'>
</form>";
}

function bot_users_table(){
	$OUT = "
<script>
function selectall_checkbox_changeaction(){
	var bchecked = document.getElementById('selectall_checkbox').checked;

	var x = document.getElementsByClassName(\"cbu_checkbox\");
var i;
for (i = 0; i < x.length; i++) {
//  x[i].style.backgroundColor = \"\#FF9900\";
 x[i].checked = bchecked;
} 


}
</script>
".menu()."
<hr>
<form method=post action='./?action=sendmessage'>
<table>
<tr style='background-color:#CCCCCC'>
<td># <input type='checkbox' id=selectall_checkbox onchange='selectall_checkbox_changeaction();'></td>
<td>@username</td>
<td>Имя</td>
<td>Фамилия</td>
<td>Город</td>
<td>Как узнал</td>
<td>Есть 18 лет?</td>
<td>Согласие с риском</td>
<td>Хочет помочь с организацией</td>
<td>Согласен с обработкой ПД</td>
<td>Комментарий</td>
</tr>";
	$bgcolor = '#FFFFFF';
	foreach ( $GLOBALS['db']->select("SELECT * FROM telegram_bot_users ORDER BY user_id asc") as $tbuser ) {
		if ($bgcolor=='#FFFFFF') {
			$bgcolor = '#CCCCFF';
		} else {
			$bgcolor = '#FFFFFF';
		}
		if ($tbuser['age_gte_18']==-1 and $tbuser['accept_risk']==1) {
			$bgcolor = '#FF9900';
		}
		$OUT .= "<tr style='background-color:{$bgcolor}'>";
		$OUT .= "<td> <input type='checkbox' id='cbu{$tbuser['user_id']}' name='user_id[{$tbuser['user_id']}]' value='on' class='cbu_checkbox'>
<a href='./?action=messages_history&id={$tbuser['user_id']}' target='_blank'>{$tbuser['user_id']}</a>
		</td>";
		if (!empty($tbuser['username'])) {
			$OUT .= "<td><a href='https://t.me/{$tbuser['username']}' target='_blank'>@{$tbuser['username']}</a></td>";
		} else {
			$OUT .= "<td>&nbsp;</td>";
		}
		$OUT .= "<td>{$tbuser['first_name']}</td>";
		$OUT .= "<td>{$tbuser['last_name']}</td>";
		$OUT .= "<td>{$tbuser['city']}</td>";
		$OUT .= "<td>{$tbuser['kak_uznal']}</td>";
		$OUT .= "<td>".($tbuser['age_gte_18']==1?'Да':'-')."</td>";
		$OUT .= "<td>".($tbuser['accept_risk']==1?'Да':'-')."</td>";
		$OUT .= "<td>".($tbuser['wants_to_help']==1?'Да':'-')."</td>";
		$OUT .= "<td>".($tbuser['agree_personal_data']==1?'Да':'-')."</td>";
		$OUT .= "<td>{$tbuser['comment']}</td>";
		$OUT .= "</tr>";
	}
	$OUT .= "</table>";
	$OUT .= "<br><textarea name=message_text placeholder='текст сообщения'></textarea><br><input id=submitbutton1 type=submit value='Отправить сообщение'>";
	$OUT .= "</form>";
	return $OUT;
}

function TG_send_multi_message($user_ids, $message){
	foreach ( $user_ids as $user_id ) {
		$GLOBALS['TELEGRAM']->sendmessage($user_id,$message);
	}
	return true;
}

function menu(){
	return "
<a href='./?action=bot_users_table'>Пользователи бота</a>
&nbsp;&nbsp;
<a href='./?action=logout'>Выход</a>
";
}

function messages_history(int $user_id){
	global $CONFIG;
	$USERS = $GLOBALS['db']->select("SELECT * FROM telegram_bot_users where user_id='$user_id'");
	if (empty($USERS)) {
		die('user by id not found');
	}
	$USER = $USERS[0];
	$OUT = menu()."

<hr>
<form method=post action='./?action=sendmessage&user_id[{$user_id}]=on'>
<h3>История переписки с {$USER['first_name']} {$USER['last_name']} ".(empty($USER['username'])?'':'<a href="https://t.me/'.$USER['username'].'">@'.$USER['username'].'</a>')."</h3>
<div style='height:500px; overflow: scroll; border:1px solid #000000;'>
<table width=100%>
";
	$bgcolor = '#FFFFFF';
	foreach ( $GLOBALS['db']->select("SELECT * FROM telegram_bot_messages ORDER BY id asc") as $tbmsg ) {
		if ($tbmsg['is_outgoing_message']==1) {
			$bgcolor = '#CCCCFF';
			$OUT .= "
			<tr style='background-color:{$bgcolor}'>
			<td align='right'><div style='font: 9px Verdana'>@".$CONFIG['TELEGRAM_BOT_NAME']."<br>".date('c',$tbmsg['timestamp'])."</div><b>{$tbmsg['message_text']}</b></td>
			</tr>";
		} else {
			$bgcolor = '#FFFFFF';
			$OUT .= "
			<tr style='background-color:{$bgcolor}'>
			<td align='left'><div style='font: 9px Verdana'>".$USER['first_name']."<br>".date('c',$tbmsg['timestamp'])."</div><b>{$tbmsg['message_text']}</b></td>
			</tr>";
		}
	}
	$OUT .= "</table>";
	$OUT .= "</div>";
	$OUT .= "<br><textarea name=message_text placeholder='текст сообщения'></textarea><br><input id=submitbutton1 type=submit value='Отправить сообщение'>";
	$OUT .= "</form>";
	return $OUT;
}