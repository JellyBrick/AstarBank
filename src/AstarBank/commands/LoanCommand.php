<?php

namespace AstarBank\commands;

use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\Server;




use AstarBank\AstarBankAPI;

class LoanCommand extends AstarBankAPICommand{
	 

	public function __construct(AstarBankAPI $plugin, $cmd = "loan"){
		parent::__construct($plugin, $cmd);
		$this->setUsage("/$cmd <액수>");
		$this->setDescription("돈을 대출합니다");
		$this->setPermission("astarbankapi.command.loan");
	}
	
	public function exec(CommandSender $sender, array $args){
		$amount = array_shift($args);
		$player = $sender->getName();
		
		if(trim($amount) === "" or !is_numeric($amount)){
			$sender->sendMessage("Usage: /".$this->getName()." <액수>");
			return true;
		}
		
		if($amount <= 0){
			$sender->sendMessage($this->getPlugin()->getMessage("loan-invalid-number", $sender->getName()));
			return true;
		}
		
		$money = AstarBankAPI::getInstance()->economyAPI->myMoney ( $player );
		if(AstarBankAPI::getInstance()->config->get("max-debt") < $amount) {
			$sender->sendMessage($this->getPlugin()->getMessage("excess-max-debt", $sender->getName(), array(AstarBankAPI::getInstance()->config->get("max-debt"), "%2", "%3", "%4")));
			return;
		}
		AstarBankAPI::getInstance()->economyAPI->addMoney ( $player, $amount );
		
		$result = $this->getPlugin()->addDebt($player, $amount);
		$output = "";
		switch($result){
			case -2:
			$output .= "Your request have been cancelled";
			break;
			case -1:
			$output .= $this->getPlugin()->getMessage("player-never-connected", $sender->getName(), array($player, "%2", "%3", "%4"));
			break;
			case 1:
			$output .= $this->getPlugin()->getMessage("loan-money", $sender->getName(), array($amount, "%2", "%3", "%4"));
			break;
		}
		$sender->sendMessage($output);
		return true;
	}
}