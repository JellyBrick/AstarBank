<?php

namespace AstarBank\event;

use AstarBank\AstarBankAPI;
use AstarBank\event\AstarBankAPIEvent;

class MoneyChangedEvent extends AstarBankAPIEvent{
	private $username, $money;
	public static $handlerList;

	public function __construct(AstarBankAPI $plugin, $username, $money, $issuer){
		parent::__construct($plugin, $issuer);
		$this->username = $username;
		$this->money = $money;
	}

	public function getUsername(){
		return $this->username;
	}

	public function getMoney(){
		return $this->money;
	}
}