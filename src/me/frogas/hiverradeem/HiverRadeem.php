<?php

namespace me\frogas\hiverradeem;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\{Config, TextFormat as TF};
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\{ConsoleCommandSender, CommandSender, Command};
use pocketmine\form\api\{SimpleForm, CustomForm};

class HiverRadeem extends PluginBase implements Listener {
	
	public $code;
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveResource("code.yml");
		$this->code_data = new Config($this->getDataFolder() . "code.yml", Config::YAML);
		$this->code = $this->code_data->getAll();
	}
	
	public function save(){
		$this->code_data->setAll($this->code);
		$this->code_data->save();
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$this->checkPlayerData($player->getName());
	}
	
	public function checkPlayerData(string $name){
		if(!isset($this->code["code-used"][$name])){
			$this->code["code-used"][$name] = [];
			$this->save();
			return true;
		}
	}
	
	public function addCode(string $code, string $msg, string $type, string $cmd){
		$this->code["code-data"][$code] = array(
          "message" => $msg, 
          "type" => $type, 
          "command" => $cmd
        );
		$this->save();
	}
	
	public function setUsedCode(string $player, string $code){
		$this->code["code-used"][$player][$code] = true;
		$this->save();
	}
	
	public function getType(string $code){
		return $this->code["code-data"][$code]["type"];
	}
	
	public function getCommand(string $code){
		return $this->code["code-data"][$code]["command"];
	}
	
	public function getMessage(string $code){
		return $this->code["code-data"][$code]["message"];
	}
	
	public function giftCommand(Player $player, string $code){
		if($this->getType($code) === "private"){
		    $this->getServer()->getCommandMap()->dispatch($player, $this->sendReplace($player, $this->getCommand($code)));
		}
		if($this->getType($code) === "public"){
		    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $this->sendReplace($player, $this->getCommand($code)));
		}
	}
	
	public function getAllCode() : array {
		$list = [];
		foreach(array_keys($this->code["code-data"]) as $code){
			$list[] = $code;
		}
		return $list;
	}
	
	public function onCommand(CommandSender $player, Command $cmd, String $label, Array $args) : Bool {
		if($cmd->getName() === "rcode"){
			if($player instanceof Player){
				if(!isset($args[0])){
					$this->sendManage($player, "You can exchange your radeem code to get exclusive prizes.");
					return true;
				}
			    if($args[0] === "add"){
				    if(!isset($args[1])){
					    $player->sendMessage("(RCode) > Usage /rcode add (code) (msg) (private|public) (commands..)");
					    return true;
					}
					if(!isset($args[2])){
					    $player->sendMessage("(RCode) > Usage /rcode add (code) (msg) (private|public) (commands..)");
					    return true;
					}
					if(!isset($args[3])){
					    $player->sendMessage("(RCode) > Usage /rcode add (code) (msg) (private|public) (commands..)");
					    return true;
					}
					if(!isset($args[4])){
					    $player->sendMessage("(RCode) > Usage /rcode add (code) (msg) (private|public) (commands..)");
					    return true;
					}
					if(isset($this->code["code-data"][$args[1]])){
						$player->sendMessage("(RCode) > This code already created!");
						return true;
					}
				    $this->addCode($args[1], $args[2], $args[3], $args[4]);
				    $player->sendMessage("(RCode) >  Successfully created new radeem code.");
				}
			}else{
				$player->sendMessage("(RCode) > Usage commands on server.");
			}
		}
		return true;
	}
	
	public function sendManage(Player $player, string $label){
		$form = new CustomForm(function(Player $player, $data){
			$result = $data;
			if($result === null){
				return true;
			}
			if(!isset($result[1])){
				$this->sendManage($player, TF::RED . "Please write input first dont leave this input!");
				return true;
			}
			if(!isset($this->code["code-data"][$result[1]])){
				$this->sendManage($player, TF::RED . "Error, " . $result[1] . " exists code wrong, no code yet!");
				return true;
			}
			if(isset($this->code["code-used"][$player->getName()][$result[1]])){
				$this->sendManage($player, TF::RED . "Error, " . $result[1] . " you have used the code!");
				return true;
			}
			$this->giftCommand($player, $result[1]);
			$this->setUsedCode($player->getName(), $result[1]);
			$player->sendMessage("(RCode) > " . $this->getMessage($result[1]));
		});
		$form->setTitle("( RadeemCode > Input )");
		$form->addLabel($label);
		$form->addInput("Exchange here:");
		$form->sendToPlayer($player);
	}
	
	public function sendReplace(Player $player, String $text){
		$text = str_replace("{player}", $player->getName(), $text);
		$text = str_replace("{line}", "\n", $text);
		return $text;
	}
}
