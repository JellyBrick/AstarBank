<?php

namespace AstarBank\commands;

use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\Server;


use AstarBank\AstarBankAPI;

class DepositMoneyCommand extends AstarBankAPICommand{
	 
	public function __construct(AstarBankAPI $plugin, $cmd = "deposit"){
		parent::__construct($plugin, $cmd);
		$this->setUsage("/$cmd <액수>");
		$this->setDescription("돈을 입금합니다");
		$this->setPermission("astarbankapi.command.deposit");
	}
	
	public function exec(CommandSender $sender, array $args){
		$amount = array_shift($args);
		$player = $sender->getName();
		
		if(trim($amount) === "" or !is_numeric($amount)){
			$sender->sendMessage("Usage: /".$this->getName()." <액수>");
			return true;
		}
		
		if($amount <= 0){
			$sender->sendMessage($this->getPlugin()->getMessage("deposit-invalid-number", $sender->getName()));
			return true;
		}
		
		$server = Server::getInstance();
		$p = $server->getPlayer($player);
		if($p instanceof Player){
			$player = $p->getName();
		}
		/*$money = $this->economyAPI->myMoney ( $player );
		if ($money < $amount) {
			$this->getPlugin()->getMessage( "deposit-not-enough-money", $sender->getName(), array($money, "%2", "%3", "%4"));
			return;
		} //this part have error.
		$this->economyAPI->reduceMoney ( $player, $amount );
		*/
		$result = $this->getPlugin()->addMoney($player, $amount);
		$output = "";
		switch($result){
			case -2:
			$output .= "Your request have been cancelled";
			break;
			case -1:
			$output .= $this->getPlugin()->getMessage("player-never-connected", $sender->getName(), array($player, "%2", "%3", "%4"));
			break;
			case 1:
			$output .= $this->getPlugin()->getMessage("deposit-money", $sender->getName(), array($amount, "%2", "%3", "%4"));
			break;
		}
		$sender->sendMessage($output);
		return true;
	}
}