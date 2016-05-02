<?php

namespace AstarBank\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;

use AstarBank\AstarBankAPI;

class RepayCommand extends AstarBankAPICommand{
	
	public function __construct(AstarBankAPI $plugin, $cmd = "repay"){
		parent::__construct($plugin, $cmd);
		$this->setUsage("/$cmd <액수>");
		$this->setPermission("astarbankapi.command.repay");
		$this->setDescription("대출한 돈을 갚습니다");
	}
	
	public function exec(CommandSender $sender, array $params){
		$player = $sender->getName();
		$amount = array_shift($params);
		
		if(trim($player) === "" or trim($amount) === "" or !is_numeric($amount)){
			$sender->sendMessage("Usage: /".$this->getName()." <액수>");
			return true;
		}
		
		if($amount <= 0){
			$sender->sendMessage($this->getPlugin()->getMessage("repaymoney-invalid-number", $sender->getName()));
			return true;
		}
		$bankdebt = AstarBankAPI::getInstance()->myDebt ( $player );
		$money = AstarBankAPI::getInstance()->economyAPI->myMoney ( $player );
		if ($bankdebt < $amount) {
			$sender->sendMessage($this->getPlugin()->getMessage("excess-debt", $sender->getName(), array($bankdebt, "%2", "%3", "%4")));
			return;
		}
		
		if ($money < $amount) {
			$sender->sendMessage($this->getPlugin()->getMessage("repay-not-enough-money", $sender->getName(), array($money, "%2", "%3", "%4")));
			return;
		}
		AstarBankAPI::getInstance()->economyAPI->reduceMoney ( $player, $amount );
		
		$result = $this->getPlugin()->reduceDebt($player, $amount, false, "RepayCommand");
		$output = "";
		switch($result){
			case AstarBankAPI::RET_SUCCESS:
			$output .= $this->getPlugin()->getMessage("repay-took-money", $sender->getName(), array($player, $amount, "%3", "%4"));
			break;
			case AstarBankAPI::RET_INVALID:
			$output .= $this->getPlugin()->getMessage("repay-player-lack-of-money", $sender->getName(), array($player, $amount, $this->getPlugin()->myBank($player), "%4"));
			break;
			default:
			$output .= $this->getPlugin()->getMessage("repay-failed", $sender->getName());
		}
		$sender->sendMessage($output);
		return true;
	}
}