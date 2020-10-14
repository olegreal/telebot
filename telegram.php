<?php
class TELEGRAM_BOTAPI {

	public function __construct($token){
		$this->compression = 'gzip';
		$this->proxy = '';
		$this->token = $token;
		$this->headers = [];
		$this->user_agent = 'Mozilla Curl';
	}

	function get($url, $user = 0, $pass = 0){
		$process = curl_init($url);
		curl_setopt($process,CURLOPT_HTTPHEADER,$this->headers);
		curl_setopt($process,CURLOPT_HEADER,0);
		curl_setopt($process,CURLOPT_USERAGENT,$this->user_agent);
		
		curl_setopt($process,CURLOPT_ENCODING,$this->compression);
		curl_setopt($process,CURLOPT_TIMEOUT,30);
		// if ($this->proxy) curl_setopt($cUrl, CURLOPT_PROXY, ‘proxy_ip:proxy_port');
		curl_setopt($process,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($process,CURLOPT_FOLLOWLOCATION,1);
		$return = curl_exec($process);
		curl_close($process);
		return $return;
	}

	function post($url, $data, $user = 0, $pass = 0, $follow_location = true, $header = false){
		$process = curl_init($url);
		curl_setopt($process,CURLOPT_HTTPHEADER,$this->headers);
		if ($header!=true) {
			curl_setopt($process,CURLOPT_HEADER,0);
		} else {
			curl_setopt($process,CURLOPT_HEADER,1);
		}
		curl_setopt($process,CURLOPT_USERAGENT,$this->user_agent);
		
		curl_setopt($process,CURLOPT_ENCODING,$this->compression);
		curl_setopt($process,CURLOPT_TIMEOUT,30);
		// if ($this->proxy) curl_setopt($cUrl, CURLOPT_PROXY, ‘proxy_ip:proxy_port’);
		curl_setopt($process,CURLOPT_POSTFIELDS,$data);
		curl_setopt($process,CURLOPT_RETURNTRANSFER,1);
		
		if ($follow_location==true) {
			curl_setopt($process,CURLOPT_FOLLOWLOCATION,1);
		} else {
			curl_setopt($process,CURLOPT_FOLLOWLOCATION,0);
		}
		
		curl_setopt($process,CURLOPT_POST,1);
		curl_setopt($process,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($process,CURLOPT_SSL_VERIFYHOST,0);
		
		if ($user!=0&&$pass!=0) {
			curl_setopt($process,CURLOPT_USERPWD,$user.':'.$pass);
		}
		
		$return = curl_exec($process);
		curl_close($process);
		return $return;
	}

	public function sendsticker($chat_id, $file_id){
		return $this->post("https://api.telegram.org/bot{$this->token}/sendsticker","chat_id=$chat_id&sticker=$file_id");
	}

	public function sendinvoice($chat_id, $title, $description, $payload, $provider_token, $start_parameter, $currency, $prices){
		$token = $this->token;
		$PRICESREQ = "";
		$showcount = 0;
		foreach ( $prices as $pricekey => $priceval ) {
			$PRICESREQ .= "&prices[$showcount][label]=$pricekey&prices[$showcount][amount]=$priceval";
			$showcount++;
		}
		$response = $this->post("https://api.telegram.org/bot".$token."/sendinvoice","chat_id=$chat_id&title=$title&description=$description&payload=$payload&provider_token=$provider_token&start_parameter=$start_parameter&currency=$currency"."".$PRICESREQ);
		
		echo $response;
		exit();
		
		return $response;
	}

	public function sendmessage($chat_id, $message_text, $parse_mode = "HTML", $options = array()){
		$addopts = "";
		foreach ( $options as $optionkey => $optionval ) {
			$addopts .= "&$optionkey=$optionval";
		}
		$URL = "https://api.telegram.org/bot".$this->token."/sendmessage";
		$maxsize = 4000;
		$chunks = array();
		$current_chunk = 0;
		$text_size = ceil(strlen($message_text)/$maxsize);
		while ( $current_chunk<$text_size ) {
			array_push($chunks,substr($message_text,$current_chunk*$maxsize,$maxsize));
			$current_chunk++;
		}
		$return = '';
		foreach ( $chunks as $message_text1 ) {
			$DATA = "chat_id=$chat_id&parse_mode=$parse_mode&text=".urlencode(strtr($message_text1,array(
				"<br>" => "\n"
			)))."$addopts";
			$return .= $this->post($URL,$DATA);
		}
		
		// LOG OUTGOING::::
		$GLOBALS['db']->insert('telegram_bot_messages',[
			'timestamp' => time(),
			'real_timestamp' => time(),
			'user_id' => $chat_id,
			'message_text' => $message_text,
			'raw_postdata' => $return,
			'is_outgoing_message' => 1
		]);
		
		return $return;
	}

	public function sendchataction($chat_id, $action = "typing"){
		$token = $this->token;
		$response = $this->post("https://api.telegram.org/bot".$token."/sendchataction","chat_id=$chat_id&action=$action");
		// sleep(5);
		return $response;
	}

	public function translit($text, $tolang = 'ru'){
		$translit = array(
			'а' => 'a',
			'б' => 'b',
			'в' => 'v',
			'г' => 'g',
			'д' => 'd',
			'е' => 'e',
			'ё' => 'yo',
			'ж' => 'zh',
			'з' => 'z',
			'и' => 'i',
			'й' => 'j',
			'к' => 'k',
			'л' => 'l',
			'м' => 'm',
			'н' => 'n',
			'о' => 'o',
			'п' => 'p',
			'р' => 'r',
			'с' => 's',
			'т' => 't',
			'у' => 'u',
			'ф' => 'f',
			'х' => 'x',
			'ц' => 'c',
			'ч' => 'ch',
			'ш' => 'sh',
			'щ' => 'shh',
			'ь' => '\'',
			'ы' => 'y',
			'ъ' => '\'\'',
			'э' => 'e\'',
			'ю' => 'yu',
			'я' => 'ya',
			'А' => 'A',
			'Б' => 'B',
			'В' => 'V',
			'Г' => 'G',
			'Д' => 'D',
			'Е' => 'E',
			'Ё' => 'YO',
			'Ж' => 'Zh',
			'З' => 'Z',
			'И' => 'I',
			'Й' => 'J',
			'К' => 'K',
			'Л' => 'L',
			'М' => 'M',
			'Н' => 'N',
			'О' => 'O',
			'П' => 'P',
			'Р' => 'R',
			'С' => 'S',
			'Т' => 'T',
			'У' => 'U',
			'Ф' => 'F',
			'Х' => 'X',
			'Ц' => 'C',
			'Ч' => 'CH',
			'Ш' => 'SH',
			'Щ' => 'SHH',
			'Ь' => '\'',
			'Ы' => 'Y\'',
			'Ъ' => '\'\'',
			'Э' => 'E\'',
			'Ю' => 'YU',
			'Я' => 'YA'
		);
		if ($tolang=="ru") {
			return strtr($text,array_flip($translit)); // обратная транслитерация. Переменная $word получит значение 'прочее'
		} else {
			return strtr($text,$translit); // транслитерация. Переменная $word получит значение 'prochee'
		}
	}

	public function get_userdata($INPUTDATA){
		global $db;
		$db->query("SELECT * FROM telegram_bot_users WHERE user_id='$INPUTDATA[user_id]' ");
		if ($db->num_rows()>0) {
			$INPUTDATA['USERDATA'] = $db->next_record();
		} else {
			// CREATING USER
			$this->create_user($INPUTDATA);
			// GETTING USERDATA AGAIN
			$db->query("SELECT * FROM telegram_bot_users WHERE user_id='$INPUTDATA[user_id]' ");
			$INPUTDATA['USERDATA'] = $db->next_record();
		}
		return $INPUTDATA['USERDATA'];
	}

	public function log($INPUTDATA, $custom_timestamp = 0){
		$GLOBALS['db']->insert('telegram_bot_messages',[
			'timestamp' => time(),
			'real_timestamp' => time(),
			'user_id' => $INPUTDATA['user_id'],
			'message_text' => $INPUTDATA['message_text'],
			'raw_postdata' => $INPUTDATA['postdata'],
			'is_outgoing_message' => 0
		]);
	}

	public function create_user($INPUTDATA){
		if ($INPUTDATA['user_id']!=0) {
			$GLOBALS['db']->insert('telegram_bot_users',[
				'user_id' => $INPUTDATA['user_id'],
				'first_name' => $INPUTDATA['from_username'],
				'last_name' => $INPUTDATA['from_username_lastname'],
				'username' => $INPUTDATA['from_usernamereal'],
				'can_use_bot' => 1,
				'is_admin' => 0,
				'current_status' => 0
			]);
			
			return true;
		} else {
			return false;
		}
	}

	public function checktrigger_start($message_words){
		if (in_array(mb_strtolower($message_words[0]),array(
			'start',
			'привет',
			'ghbdtn',
			'старт'
		))) {return true;}
		return false;
	}

	// CHECKING TRIGGERS
	public function checktrigger_cancel($INPUTDATA){
		if (in_array(mb_strtolower($INPUTDATA['message_words'][0]),array(
			'отмена',
			'cancel'
		))) {return true;}
		return false;
	}
}
