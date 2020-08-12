<?php

namespace RKAbdul\HeadHunter;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Server;

class Main extends PluginBase {
	public function onEnable(){
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}
}
