<?php

namespace Riot;

class API {
	private $key;
	public $region;
	private $host = "https://euw.api.pvp.net/";

	function __construct($key, $region = 'euw'){
		$this->key = $key;
		$this->region = strtolower($region);
	}

	function summoner($search){
		if(is_numeric($search)){
			$search = [$search];
		}elseif(is_string($search)){
			$search = explode(",", $search);
		}

		if(is_array($search)){
			$names = array();
			$nums = array();
			foreach($search as $player){
				if(is_numeric($player)){ $nums[] = $player; }
				else{ $names[] = $player; }
			}

			$final = array();

			if(!empty($names)){
				$method = "api/lol/euw/v1.4/summoner/by-name/";
				$url = $this->url($method, implode(",", $names));
				$data = file_get_contents($url);
				$res = json_decode($data, TRUE);
				if(!empty($res)){
					foreach($res as $name => $data){
						$final[$name] = new Summoner($data, $this);
					}
				}
			}

			if(!empty($nums)){
				$method = "api/lol/euw/v1.4/summoner/";
				$url = $this->url($method, implode(",", $nums));
				$data = file_get_contents($url);
				$res = json_decode($data, TRUE);
				if(!empty($res)){
					foreach($res as $name => $data){
						$final[$name] = new Summoner($data, $this);
					}
				}
			}

			if(count($names) + count($nums) == 1 && count($final) == 1){
				return current($final);
			}

			return $final;
		}
	}

	function spectate($user){
		if($user instanceof Summoner){ $user = $user->id; }
		if(!is_numeric($user)){ return FALSE; }

		$method = "observer-mode/rest/consumer/getSpectatorGameInfo/" .strtoupper($this->region) ."1/";
		$url = $this->url($method, $user);
		$data = file_get_contents($url);
		if(empty($data)){ return FALSE; }

		$res = json_decode($data, TRUE);
		var_dump($res);

		return $res;
	}

	private function url($method, $user){
		return $this->host .$method .$user ."?api_key=" .$this->key;
	}
}

class Summoner {
	public $id;
	public $name;
	public $region;
	public $profileIconId;
	public $revisionDate;
	public $summonerLevel;
	private $api;

	function __construct($id, $name = NULL, $level = NULL){
		if(is_array($id)){
			foreach($id as $k => $v){
				$this->$k = $v;
			}
			if(!empty($level) && $level instanceof API){
				$this->api = $level;
				$this->region = $this->api->region;
			}
			elseif(!empty($name) && $name instanceof API){
				$this->api = $name;
				$this->region = $this->api->region;
			}

			if(!empty($name) && is_string($name)){ $this->region = strtoupper($name); }
			return $this;
		}

		$this->id = $id;
		$this->name = $name;
		$this->summonerLevel = $level;

		return $this;
	}

	function __get($name){
		if($name == "level"){ return $this->summonerLevel; }
		elseif($name == "date"){ return $this->revisionDate; }
		elseif($name == "icon"){ return $this->profileIconId; }

		return NULL;
	}

	function date($format = NULL){
		if($format == TRUE){ return round($this->revisionDate / 1000); }
		if(empty($format)){ $format = "Y-m-d H:i:s"; }
		return date($format, $this->date(TRUE));
	}

	function array(){
		return [
			'id' => $this->id,
			'region' => $this->region,
			'name' => $this->name,
			'level' => $this->summonerLevel,
			'updated' => $this->date()
		];
	}

	function is_playing($with = NULL){
		$res = $this->api->spectate($this);
		if(!$res){ return FALSE; }
		// TODO $with
		return TRUE;
	}
}

class Player {
	public $teamId; // 100 or 200
	public $spell1Id;
	public $spell2Id;
	public $championId;
	public $profileIconId;
	public $summonerName;
	public $bot = FALSE;
	public $summonerId;
	public $runes = array();
	public $masteries = array();

	function __construct(&$data){
		if($data instanceof Summoner){
			$this->summonerName = $data->name;
			$this->profileIconId = $data->profileIconId;
			$this->summonerId = $data->id;
			$this->bot = FALSE;
		}
	}
}

?>
