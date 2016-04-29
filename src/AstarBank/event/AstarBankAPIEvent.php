<?php

namespace AstarBank\event;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\event\Cancellable;

use AstarBank\AstarBankAPI;

class AstarBankAPIEvent extends PluginEvent implements Cancellable{
	private $issuer;
	
	public function __construct(AstarBankAPI $plugin, $issuer){
		parent::__construct($plugin);
		$this->issuer = $issuer;
	}
	
	public function getIssuer(){
		return $this->issuer;
	}
}