<?php

namespace AstarBank\commands;

use pocketmine\command\CommandSender;

use AstarBank\AstarBankAPI;

class MyBankCommand extends AstarBankAPICommand implements InGameCommand{
	public function __construct(AstarBankAPI $plugin, $cmd = "mybank"){
		parent::__construct($plugin, $cmd);
		$this->setUsage("/$cmd");
		$this->setDescription("은행잔고를 보여줍니다");
		$this->setPermission("astarbankapi.command.mybank");
	}
	
	public function exec(CommandSender $sender, array $args){
		$username = $sender->getName();
		$result = $this->getPlugin()->myBank($username);
		$sender->sendMessage($this->getPlugin()->getMessage("mybank-mybank", $sender->getName(), array($result, "%2", "%3", "%4")));
		return true;
	}
}