<?php

namespace AstarBank\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;

use AstarBank\AstarBankAPI;

class WithdrawMoneyCommand extends AstarBankAPICommand{
	
	public function __construct(AstarBankAPI $plugin, $cmd = "withdraw"){
		parent::__construct($plugin, $cmd);
		$this->setUsage("/$cmd <액수>");
		$this->setPermission("astarbankapi.command.withdraw");
		$this->setDescription("돈을 출급합니다");
	}
	
	public function exec(CommandSender $sender, array $params){
		$player = $sender->getName();
		$amount = array_shift($params);
		
		if(trim($player) === "" or trim($amount) === "" or !is_numeric($amount)){
			$sender->sendMessage("Usage: /".$this->getName()." <액수>");
			return true;
		}
		
		if($amount <= 0){
			$sender->sendMessage($this->getPlugin()->getMessage("takemoney-invalid-number", $sender->getName()));
			return true;
		}
		
		$server = Server::getInstance();
		$p = $server->getPlayer($player);
		if($p instanceof Player){
			$player = $p->getName();
		}
		
		$result = $this->getPlugin()->reduceMoney($player, $amount, false, "WithdrawMoneyCommand");
		$output = "";
		switch($result){
			case AstarBankAPI::RET_SUCCESS:
			$output .= $this->getPlugin()->getMessage("withdraw-took-money", $sender->getName(), array($player, $amount, "%3", "%4"));
			break;
			case AstarBankAPI::RET_INVALID:
			$output .= $this->getPlugin()->getMessage("withdraw-player-lack-of-money", $sender->getName(), array($player, $amount, $this->getPlugin()->myBank($player), "%4"));
			break;
			default:
			$output .= $this->getPlugin()->getMessage("withdraw-failed", $sender->getName());
		}
		$sender->sendMessage($output);
		return true;
	}
}