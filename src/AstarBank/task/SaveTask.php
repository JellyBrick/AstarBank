<?php

namespace AstarBank\task;

use AstarBank\AstarBankAPI;

use pocketmine\scheduler\PluginTask;

class SaveTask extends PluginTask {
	public function __construct(AstarBankAPI $plugin){
		parent::__construct($plugin);
	}
	
	public function onRun($currentTick){
		$this->getOwner()->save();
	}
}