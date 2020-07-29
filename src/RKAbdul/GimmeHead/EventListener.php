<?php

declare(strict_types=1);

namespace RKAbdul\GimmeHead;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use onebone\economyapi\EconomyAPI;

class EventListener implements Listener{

    /**
     * @var Main
     */
    private $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function onDeath(PlayerDeathEvent $event){
      $player = $event->getPlayer();
      $onlyKill = $this->plugin->getConfig()->get("only-kill");
      if ($onlyKill === "true") {
         if(!($player->getLastDamageCause() instanceof EntityDamageEvent)){
        return;
     }
     if($player->getLastDamageCause()->getCause() === EntityDamageEvent::CAUSE_ENTITY_ATTACK){
        $killer = $player->getLastDamageCause()->getDamager();
        if(!($killer instanceof Player)){
        return;
      }
      }
     }
       $economy = EconomyAPI::getInstance();
       $head = Item::get(Item::MOB_HEAD, 3, 1);
       $playername = $player->getName();
       $percent = $this->plugin->getConfig()->get("money-percent");
       $usermoney = $economy->myMoney($player);
       $reward = round(($percent / 100) * $usermoney);
       $name = str_replace(["&", "{player}"], ["§", $playername], $this->plugin->getConfig()->get("head-name"));
       $head->setNamedTagEntry(new StringTag("money", "$reward"));
       $head->setNamedTagEntry(new StringTag("author", $playername));
       $head->setCustomName($name);
       $lore = str_replace(["{player}", "&", "{value}"], [$playername, "§", $reward], $this->plugin->getConfig()->get("head-lore"));
       $head->setLore([$lore]);
       $playerX = $player->getX();
       $playerY = $player->getY();
       $playerZ = $player->getZ(); 
       $vector3Pos = new Vector3($playerX, $playerY, $playerZ);
       $player->getLevel()->dropItem($vector3Pos, $head);
  }
  public function onInteract(PlayerInteractEvent $event) {
    $player = $event->getPlayer();
    $head = $event->getItem();
      if ($head->getNamedTag()->hasTag("money")) {
        $money = $head->getNamedTag()->getTag("money")->getValue();
        $author = $head->getNamedTag()->getTag("author")->getValue();
        $economy = EconomyAPI::getInstance();
        if ($this->plugin->getConfig()->get("reduce-money") === "true") {
        if ($economy->myMoney($author) < $money) {
          $economy->setMoney($author, 0);
        }
        $economy->reduceMoney($author, $money);
        }
        $economy->addMoney($player, $money);
        $player->sendMessage("§aSuccesfully Redeemed $author's head with the value of $money");
        $head->setCount($head->getCount() - 1);
        $player->getInventory()->setItem($player->getInventory()->getHeldItemIndex(), $head);
        $event->setCancelled(true);
      }
    }
 }
