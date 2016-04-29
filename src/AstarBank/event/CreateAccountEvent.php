<?php

namespace AstarBank\event;

use AstarBank\event\AstarBankAPIEvent;
use AstarBank\AstarBankAPI;

class CreateAccountEvent extends AstarBankAPIEvent{
	private $username, $defaultMoney;
	public static $handlerList;
	
	public function __construct(AstarBankAPI $plugin, $username, $defaultMoney, $issuer){
		parent::__construct($plugin, $issuer);
		$this->username = $username;
		$this->defaultMoney = $defaultMoney;
	}
	
	public function getUsername(){
		return $this->username;
	}
	
	public function setDefaultMoney($money){
		$this->defaultMoney = $money;
	}
	
	public function getDefaultMoney(){
		return $this->defaultMoney;
	}
}