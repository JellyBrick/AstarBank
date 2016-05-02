<?php

namespace AstarBank\commands;

use pocketmine\command\CommandSender;

use AstarBank\AstarBankAPI;

class MyDebtCommand extends AstarBankAPICommand implements InGameCommand{
	public function __construct(AstarBankAPI $plugin, $cmd = "mydebt"){
		parent::__construct($plugin, $cmd);
		$this->setUsage("/$cmd");
		$this->setDescription("대출금을 보여줍니다");
		$this->setPermission("astarbankapi.command.mydebt");
	}
	
	public function exec(CommandSender $sender, array $args){
		$username = $sender->getName();
		$result = $this->getPlugin()->myBank($username);
		$sender->sendMessage($this->getPlugin()->getMessage("mydebt-mydebt", $sender->getName(), array($result, "%2", "%3", "%4")));
		return true;
	}
}