<?php

namespace AstarBank\event;

use AstarBank\event\AstarBankAPIEvent;
use AstarBank\AstarBankAPI;

class ReduceMoneyEvent extends AstarBankAPIEvent{
	private $username, $amount;
	public static $handlerList;
	
	public function __construct(AstarBankAPI $plugin, $username, $amount, $issuer){
		parent::__construct($plugin, $issuer);
		$this->username = $username;
		$this->amount = $amount;
	}
	
	public function getUsername(){
		return $this->username;
	}
	
	public function getAmount(){
		return $this->amount;
	}
}