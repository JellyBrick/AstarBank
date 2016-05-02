<?php

namespace AstarBank\task;

use AstarBank\AstarBankAPI;

use pocketmine\scheduler\PluginTask;

class InterestTask extends PluginTask {
	public function __construct(AstarBankAPI $plugin){
		parent::__construct($plugin);
	}
	
	public function onRun($currentTick){
		foreach($this->getOwner()->bank["bank"] as $name => $account) {
   $this->bank["bank"][$name] += 1000;
}
}
}
