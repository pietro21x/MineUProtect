<?php

namespace MineUProtect;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Cancellable;

class MineUProtect extends PluginBase implements Listener {
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder());
		$this->initConfig();
	}
	
	public function initConfig(){
		if(!file_exists($this->getDataFolder(). "config.yml")){
			$config = new Config($this->getDataFolder(). "config.yml", Config::YAML);
			$config->set("error_message", "§8[§eMineU§bProtect§8] §4You cannot do that here!");
			$config->set("protected_worlds", array());
			$config->save();
		}
	}
	
	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$config = new Config($this->getDataFolder(). "config.yml", Config::YAML);
		$message = $config->get("error_message");
		
		if(in_array($player->getLevel()->getName(), $config->get("protected_worlds"))){
			$event->setCancelled(true);
			$player->sendMessage($message);
		}
	}
	
	public function onPlace(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		$config = new Config($this->getDataFolder(). "config.yml", Config::YAML);
		$msg = $config->get("error_message");
		
		if(in_array($player->getLevel()->getName(), $config->get("protected_worlds"))){
			$event->setCancelled(true);
			$player->sendMessage($msg);
		}
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) :bool {
		$config = new Config($this->getDataFolder(). "config.yml", Config::YAML);
	
		if($cmd->getName() == "world"){
			if($sender->hasPermission("usage.protect")){
				if(empty($args[0])){
					$sender->sendMessage("§2- §4Please use §e/welt <lock|unlock>");
					return true;
				}
				if($args[0] == "lock"){
					$protected = $config->get("protected_worlds");
					$protected[] = $sender->getLevel()->getName();
					$config->set("protected_worlds", $protected);
					$config->save();
					$sender->sendMessage("§7[§eMineU§bProtect§7] §7The world §e" . $sender->getLevel()->getName() . "§7 has been locked!");
					return true;
				}
				if($args[0] == "unlock"){
					$protected = $config->get("protected_worlds");
					if(in_array($sender->getLevel()->getName(), $protected)){
						unset($protected[array_search($sender->getLevel()->getName(), $protected)]);
						$config->set("protected_worlds", $protected);
						$config->save();
						$sender->sendMessage("§7[§eMineU§bProtect§7] §7The world §e" . $sender->getLevel()->getName() . "§7 has been unlocked!");
						return true;
					}
					if(!in_array($sender->getLevel()->getName(), $protected)){
						$sender->sendMessage("§7[§eMineU§bProtect§7] §7This world cannot be unlocked becuase it is not locked!");
						return true;
					}
				}
			}
			if(!$sender->hasPermission("usage.protect")){
				$sender->sendMessage("§4You dont have permission to use this command!");
				return true;
			}
		}
	}
}
