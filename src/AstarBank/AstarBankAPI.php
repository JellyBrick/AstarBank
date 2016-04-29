<?php

namespace AstarBank;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat as Color;
use onebone\economyapi\EconomyAPI;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\Player;
use pocketmine\utils\Utils;
use AstarBank\event\AddMoneyEvent;
use AstarBank\event\ReduceMoneyEvent;
use AstarBank\event\MoneyChangedEvent;
use AstarBank\event\CreateAccountEvent;
use AstarBank\task\SaveTask;


class AstarBankAPI extends PluginBase implements Listener
{
	
	const API_VERSION = 1;

	const PACKAGE_VERSION = "5.7";

	private static $instance = null;

	private $bank = [];

	private $config = null;

	private $command = null;
	
	private $langRes = [];

	private $playerLang = [];
	
	private $bankUnit = "$";
	
	public $economyAPI = null;

	const RET_ERROR_1 = -4;

	const RET_ERROR_2 = -3;
	
	const RET_CANCELLED = -2;
	
	const RET_NOT_FOUND = -1;

	const RET_INVALID = 0;

	const RET_SUCCESS = 1;
	
	const CURRENT_DATABASE_VERSION = 0x02;
	
	
	
	private $langList = [
		"ko" => "한국어",
	];

	public static function getInstance(){
		return self::$instance;
	}

	public function onLoad(){
		self::$instance = $this;
	}
	
	public function onEnable()
	{
		@mkdir($this->getDataFolder());
		$this->createConfig();
		$this->scanResources();
		
		if(!is_file($this->getDataFolder() . "PlayerLang.dat")){
			file_put_contents($this->getDataFolder() . "PlayerLang.dat", serialize([]));
		}

		$this->playerLang = unserialize(file_get_contents($this->getDataFolder() . "PlayerLang.dat"));

		if(!isset($this->playerLang["console"])){
			$this->getLangFile();
		}
		
		$commands = [
			"deposit" => "AstarBank\\commands\\DepositMoneyCommand",
		    "withdraw" => "AstarBank\\commands\\WithdrawMoneyCommand",
			"mybank" => "AstarBank\\commands\\MyBankCommand"
		];
		$commandMap = $this->getServer()->getCommandMap();
		foreach($commands as $key => $command){
			foreach($this->command->get($key) as $cmd){
				$commandMap->register("astarbankapi", new $command($this, $cmd));
			}
		}
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$bankConfig = new Config($this->getDataFolder() . "AstarBank.yml", Config::YAML, [
			"bank" => [],
		]);
		$this->bank = $bankConfig->getAll();
		$this->moneyUnit = $this->config->get("bank-unit");

		$time = $this->config->get("auto-save-cycle");
		if(is_numeric($time)){
			$interval = $time * 1200;
			$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new SaveTask($this), $interval, $interval);
			$this->getLogger()->notice("Auto save Cycle : ".$time." minutes");
		}
		
	if ($this->getServer ()->getPluginManager ()->getPlugin ( "EconomyAPI" ) != null) {
			$this->economyAPI = \onebone\economyapi\EconomyAPI::getInstance ();
			
		} else {
			$this->getLogger ()->error ( "§4EconomyAPI 플러그인이 없습니다. 플러그인을 비활성화 합니다." );
			$this->getServer ()->getPluginManager ()->disablePlugin ( $this );
		}
	}
	
	public function getConfigurationValue($key, $default = false){
		if($this->config->exists($key)){
			return $this->config->get($key);
		}
		return $default;
	}
	private function createConfig(){
		$this->config = new Config($this->getDataFolder() . "astarbanksetting.properties", Config::PROPERTIES, yaml_parse($this->readResource("config.yml")));
	    $this->command = new Config($this->getDataFolder() . "command.yml", Config::YAML, yaml_parse($this->readResource("command.yml")));
	}

	private function readResource($res){
		$resource = $this->getResource($res);
		if($resource !== null){
			return stream_get_contents($resource);
		}
		return false;
	}
	
	private function scanResources(){
		foreach($this->getResources() as $resource){
			$s = explode(\DIRECTORY_SEPARATOR, $resource);
			$res = $s[count($s) - 1];
			if(substr($res, 0, 5) === "lang_"){
				$this->langRes[substr($res, 5, -5)] = get_object_vars(json_decode($this->readResource($res)));
			}
		}
		$this->langRes["ko"] = (new Config($this->getDataFolder() . "language.properties", Config::PROPERTIES, $this->langRes["ko"]))->getAll();
	}

	private function getLangFile(){
		$lang = $this->config->get("default-lang");
		if(isset($this->langRes[$lang])){
			$this->playerLang["console"] = $lang;
			$this->playerLang["rcon"] = $lang;
			$this->getLogger()->info(TextFormat::GREEN.$this->getMessage("language-set", "console", [$this->langList[$lang], "%2", "%3", "%4"]));
		}else{
			$this->playerLang["console"] = "ko";
			$this->playerLang["rcon"] = "ko";
			$this->getLogger()->info(TextFormat::GREEN.$this->getMessage("language-set", "console", [$this->langList[$lang], "%2", "%3", "%4"]));
		}
	}
	
	public function setLang($lang, $target = "console"){
		if(isset($this->langRes[$lang])){
			$this->playerLang[strtolower($target)] = $lang;
			return $lang;
		}else{
			$lower = strtolower($lang);
			foreach($this->langList as $key => $l){
				if($lower === strtolower($l)){
					$this->playerLang[strtolower($target)] = $key;
					return $l;
				}
			}
		}
		return false;
	}
	
	public function getLangList(){
		return $this->langList;
	}

	public function getLangResource(){
		return $this->langRes;
	}
	
	public function getPlayerLang($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		if(isset($this->playerLang[$player])){
			return $this->playerLang[$player];
		}else{
			return false;
		}
	}
	
	public function getMoneyUnit(){
		return $this->bankUnit;
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, Array $args){
		switch($command->getName()){
			case "입금":
		}
	}
	
	public function onDisable(){
		$this->save();
	}
	
	public function save(){
		$bankConfig = new Config($this->getDataFolder() . "AstarBank.yml", Config::YAML);
		$bankConfig->setAll($this->bank);
		$bankConfig->save();
		file_put_contents($this->getDataFolder() . "PlayerLang.dat", serialize($this->playerLang));
	}
	
	public function addMoney($player, $amount, $force = false, $issuer = "external"){
		if($amount <= 0 or !is_numeric($amount)){
			return self::RET_INVALID;
		}
		
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);

		$amount = round($amount, 2);
		if(isset($this->bank["bank"][$player])){
			$amount = min($this->config->get("max-bank"), $amount);
			$event = new AddMoneyEvent($this, $player, $amount, $issuer);
			$this->getServer()->getPluginManager()->callEvent($event);
			if($force === false and $event->isCancelled()){
				return self::RET_CANCELLED;
			}
			$this->bank["bank"][$player] += $amount;
			$this->getServer()->getPluginManager()->callEvent(new MoneyChangedEvent($this, $player, $this->bank["bank"][$player], $issuer));
			return self::RET_SUCCESS;
		}else{
			return self::RET_NOT_FOUND;
		}
	}
	
	public function reduceMoney($player, $amount, $force = false, $issuer = "external"){
		if($amount <= 0 or !is_numeric($amount)){
			return self::RET_INVALID;
		}

		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);

		$amount = round($amount, 2);
		if(isset($this->bank["bank"][$player])){
			if($this->bank["bank"][$player] - $amount < 0){
				return self::RET_INVALID;
			}
			$event = new ReduceMoneyEvent($this, $player, $amount, $issuer);
			$this->getServer()->getPluginManager()->callEvent($event);
			if($force === false and $event->isCancelled()){
				return self::RET_CANCELLED;
			}
			$this->bank["bank"][$player] -= $amount;
			$this->getServer()->getPluginManager()->callEvent(new MoneyChangedEvent($this, $player, $this->bank["bank"][$player], $issuer));
			return self::RET_SUCCESS;
		}else{
			return self::RET_NOT_FOUND;
		}
	}
	
	public function myBank($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);

		if(!isset($this->bank["bank"][$player])){
			return false;
		}
		return $this->bank["bank"][$player];
	}
	
	public function getAllMoney(){
		return $this->bank;
	}
	
	
	
	public function onLoginEvent(PlayerLoginEvent $event){
		$username = strtolower($event->getPlayer()->getName());
		if(!isset($this->bank["bank"][$username])){
			$this->getServer()->getPluginManager()->callEvent(($ev = new CreateAccountEvent($this, $username, $this->config->get("default-bank"), $this->config->get("default-debt"), null, "AstarBankAPI")));
			$this->bank["bank"][$username] = round($ev->getDefaultMoney(), 2);
		}
		if(!isset($this->playerLang[$username])){
			$this->setLang($this->config->get("default-lang"), $username);
		}
	}
	
	public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event){
		$command = strtolower(substr($event->getMessage(), 0, 9));
		if($command === "/save-all"){
			$this->onCommandProcess($event->getPlayer());
		}
	}

	public function onServerCommandProcess(ServerCommandEvent $event){
		$command = strtolower(substr($event->getCommand(), 0, 8));
		if($command === "save-all"){
			$this->onCommandProcess($event->getSender());
		}
	}

	public function onCommandProcess(CommandSender $sender){
		$command = $this->getServer()->getCommandMap()->getCommand("save-all");
		if($command instanceof Command){
			if($command->testPermissionSilent($sender)){
				$this->save();
				$sender->sendMessage("§b[AstarBankAPI] 은행 데이터 저장이 완료되었습니다.");
			}
		}
	}
	
	public function getMessage($key, $player = "console", array $value = ["%1", "%2", "%3", "%4"]){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);

		if(isset($this->playerLang[$player]) and isset($this->langRes[$this->playerLang[$player]][$key])){
			return str_replace(["%MONEY_UNIT%", "%1", "%2", "%3", "%4"], [$this->bankUnit, $value[0], $value[1], $value[2], $value[3]], $this->langRes[$this->playerLang[$player]][$key]);
		}elseif(isset($this->langRes["ko"][$key])){
			return str_replace(["%MONEY_UNIT%", "%1", "%2", "%3", "%4"], [$this->bankUnit, $value[0], $value[1], $value[2], $value[3]], $this->langRes["ko"][$key]);
		}else{
			return "Can't find message file";
		}
	}

	public function __toString(){
		return "AstarBankAPI (total accounts: " . count($this->bank) . ")";
	}

	public function alert($player, $text = "", $mark = null) {
		if ($mark == null)
			$mark = $this->get ( "default-prefix" );
			$player->sendMessage ( TextFormat::RED . $mark . " " . $text );
	}
}
