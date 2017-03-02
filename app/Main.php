<?php

class Main extends TelegramApp\Module {
    public function run(){
        require 'Riot.php';
        parent::run();
    }

    function username($user){
    	/*
    	if($user instanceof \Telegram\User){ $user = $user->id; }
    	$query = $this->db->where('telegramid', $user)->get('user');
    	if($query){ return $query[0]['name']; }
    	return NULL; */

    	// if(isset($user->username) && !empty($user->username)){ return $user->username; }
    	// return strval($user);
    }

    protected function hooks(){
        if($this->telegram->callback && $this->telegram->callback == "apuntar"){
        	$str = $this->telegram->text_message();
        	$user = username($this->telegram->user);

        	if(empty($user)){
        		$this->telegram->answer_if_callback("Regístrate primero con tu nombre de invocador.", TRUE);
        		$this->end();
        	}

        	if($this->telegram->text_contains($user)){
        		$this->telegram->answer_if_callback("¡Ya estás apuntado en la lista!", TRUE);
        		$this->end();
        	}

        	$str .= "\n" .$user;

        	$amount = 1 + (strpos($str, "5vs5") !== FALSE ? 5 : 3);
        	$exp = explode("\n", $str);

        	if(count($exp) < $amount){
        		$this->telegram->send
        			->inline_keyboard()
        				->row_button("¡Me apunto!", "apuntar")
        			->show();
        	}else{
        		$str .= "\n\nCola llena!";
        	}

        	$this->telegram->send
        		->message(TRUE)
        		->text($str, 'HTML')
        	->edit('text');

        	$this->telegram->answer_if_callback("¡Hecho! A ver si se apunta alguien más...");
        	$this->end();
        }

        if($this->telegram->text_command("5vs5") or $this->telegram->text_command("3vs3")){
        	$game = ($this->telegram->text_command("5vs5") ? "5vs5" : "3vs3");

        	$str = "Partida #$game, participan:\n";
        	$str .= username($this->telegram->user);

        	$this->telegram->send
        		->inline_keyboard()
        			->row_button("¡Me apunto!", "apuntar")
        		->show()
        		->text($str, 'HTML')
        	->send();

        	$this->end();
        }
    }

    public function sal(){
        if($this->telegram->user->id == CREATOR or in_array($this->telegram->user->id, $this->telegram->chat->admins($bot))){
    		$this->telegram->send->leave_chat();
    	}
        $this->end();
    }

}
?>
