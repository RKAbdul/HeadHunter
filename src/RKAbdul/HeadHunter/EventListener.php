<?php

declare(strict_types=1);

namespace RKAbdul\HeadHunter;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;

use pocketmine\math\Vector3;
use pocketmine\utils\Config;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageEvent;

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
      var_dump($onlyKill);
      if ($onlyKill == true) {
          if($player->getLastDamageCause() instanceof EntityDamageEvent){
              if($player->getLastDamageCause()->getCause() === EntityDamageEvent::CAUSE_ENTITY_ATTACK){
                  $killer = $player->getLastDamageCause()->getDamager();
                  if(!$killer instanceof Player){
                      return;
                  }
              } else {
                  return;
              }
          } else {
              return;
          }
      }
      
      $head = $this->createHead($player);
      
      $playerX = $player->getX();
      $playerY = $player->getY();
      $playerZ = $player->getZ(); 
      
      $vector3Pos = new Vector3($playerX, $playerY, $playerZ);
      $player->getLevel()->dropItem($vector3Pos, $head);
  }
  
  public function createHead(Player $player) {
      $economy = EconomyAPI::getInstance();
      $playername = $player->getName();
      $percent = $this->plugin->getConfig()->get("money-percent");
      $reward = round(($percent / 100) * $economy->myMoney($player));
      
      $lore = str_replace(["{player}", "&", "{value}"], [$playername, "§", $reward], $this->plugin->getConfig()->get("head-lore"));
      $name = str_replace(["&", "{player}"], ["§", $playername], $this->plugin->getConfig()->get("head-name"));
      
      $head = Item::get(Item::MOB_HEAD, 3, 1);
      $head->setNamedTagEntry(new StringTag("money", "$reward"));
      $head->setNamedTagEntry(new StringTag("author", $playername));
      $head->setCustomName($name);
      $head->setLore([$lore]);
      return $head;
  }
  
  public function onInteract(PlayerInteractEvent $event) {
    $player = $event->getPlayer();
    $head = $event->getItem();
      if ($head->getNamedTag()->hasTag("money")) {
        $money = $head->getNamedTag()->getTag("money")->getValue();
        $author = $head->getNamedTag()->getTag("author")->getValue();
        $economy = EconomyAPI::getInstance();
        $economy->addMoney($player, $money);
        if ($this->plugin->getConfig()->get("reduce-money") == true) {
            $economy->reduceMoney($author, $money);
        }
        $player->sendMessage("§aSuccesfully Redeemed $author's head with the value of $money");
        $head->setCount($head->getCount() - 1);
        $player->getInventory()->setItem($player->getInventory()->getHeldItemIndex(), $head);
        $event->setCancelled(true);
      }
    }
 }
