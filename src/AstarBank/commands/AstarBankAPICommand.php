<?php

namespace AstarBank\commands;

use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;

use AstarBank\AstarBankAPI;
use pocketmine\Player;

abstract class AstarBankAPICommand extends PluginCommand implements PluginIdentifiableCommand{
	public function __construct(AstarBankAPI $plugin, $name){
		parent::__construct($name, $plugin);
	}

	public function execute(CommandSender $sender, $label, array $params){
		if(!$this->getPlugin()->isEnabled() or !$this->testPermission($sender)){
			return false;
		}

		if($this instanceof InGameCommand and !$sender instanceof Player){
			$sender->sendMessage(InGameCommand::ERROR_MESSAGE);
			return true;
		}

		return $this->exec($sender, $params);
	}

	public abstract function exec(CommandSender $sender, array $params);
}