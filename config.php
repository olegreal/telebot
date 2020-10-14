<?php
error_reporting(E_ALL&~E_NOTICE);
date_default_timezone_set("Europe/Moscow");
$CONFIG = [
	'DBHost' => '<YOUR_MYSQL_HOST>',
	'DBUser' => '<YOUR_MYSQL_USER>',
	'DBPass' => '<YOUR_MYSQL_PASSWORD>',
	'DBName' => '<YOUR_DATABASE_NAME>',
	'DBPort' => 3306,
	'TELEGRAM_BOT_TOKEN' => '<YOUR_BOT_TOKEN>',
	'TELEGRAM_BOT_NAME' => '<YOUR_BOT_NAME>',
	'ADMIN_LOGIN' => 'admin',
	'ADMIN_PASSWORD' => 'password',
	'SCRIPT_URL_PREFIX' => "https://{$_SERVER['HTTP_HOST']}/INFOBOT/"
];
$CONFIG['AUTHTOKEN'] = md5($CONFIG['ADMIN_LOGIN'].$CONFIG['ADMIN_PASSWORD'].date('d.m.Y H'));