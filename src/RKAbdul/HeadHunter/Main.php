<?php

namespace RKAbdul\HeadHunter;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Server;

class Main extends PluginBase {

        public const VERSION = 2;

        private $cfg;

	public function onEnable(){
		$this->saveDefaultConfig();
                $this->cfg = $this->getConfig()->getAll();
                if ($this->cfg["version"] < self::VERSION) {
                    $this->getLogger()->error("Config Version is outdated! Please delete your current config file!");
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}
}
