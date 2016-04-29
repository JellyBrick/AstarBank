<?php

namespace AstarBank\data;

use pocketmine\utils\Config;

class DataConverter{
	private $moneyData, $version, $moneyFile;
	
	const VERSION_1 = 0x01;
	const VERSION_2 = 0x02;
	
	public function __construct($moneyFile){
		$this->parseData($moneyFile);
	}
	
	private function parseData($moneyFile){
		$moneyCfg = new Config($moneyFile, Config::YAML);
		$this->moneyFile = $moneyCfg;
		
		if($moneyCfg->exists("version")){
			$this->version = $moneyCfg->get("version");
		}else{
			$this->version = self::VERSION_1;
		}
		
		if($this->version === self::VERSION_1){
			$this->moneyData = $moneyCfg->get("money");
		}else{
			switch($this->version){
				case self::VERSION_2:
				$money = [];
				foreach($moneyCfg->get("money") as $player => $m){
					$money[strtolower($player)] = $m;
				}
				$this->moneyData = $money;
				break;
			}
		}
	}
	
	public function convertData($targetVersion){
		switch($this->version){
			case self::VERSION_1:
			switch($targetVersion){
				case self::VERSION_1:
				return true;
				
				case self::VERSION_2:
				$this->moneyFile->set("version", self::VERSION_2);
				
				$money = [];
				foreach($this->moneyData as $player => $m){
					$money[strtolower($player)] = $m;
				}
				
				$this->moneyFile->set("money", $money);
				
				$this->moneyFile->save();
				return true;
			}
			break;
			case self::VERSION_2:
			
			break;
		}
		return false;
	}
	
	public function getMoneyData(){
		return $this->moneyData;
	}
	
	public function getVersion(){
		return $this->version;
	}
}