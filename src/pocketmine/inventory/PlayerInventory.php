<?php

declare(strict_types=1);

namespace pocketmine\inventory;

use InvalidArgumentException;
use LogicException;
use pocketmine\entity\Human;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\CreativeContentPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\CreativeContentEntry;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\Player;
use function array_map;
use function in_array;
use function is_array;

class PlayerInventory extends BaseInventory{

    /** @var Human */
    protected $holder;

    /** @var int */
    protected $itemInHandIndex = 0;

    public function __construct(Human $player){
        $this->holder = $player;
        parent::__construct();
    }

    public function getName() : string{
        return "Player";
    }

    public function getDefaultSize() : int{
        return 36;
    }

    /**
     * Called when a client equips a hotbar slot. This method should not be used by plugins.
     * This method will call PlayerItemHeldEvent.
     *
     * @param int $hotbarSlot Number of the hotbar slot to equip.
     *
     * @return bool if the equipment change was successful, false if not.
     */
    public function equipItem(int $hotbarSlot): bool {
        $holder = $this->getHolder();
        if (!$this->isHotbarSlot($hotbarSlot)) {
            if ($holder instanceof Player) {
                $this->sendContents($holder);
            }
            return false;
        }

        if ($holder instanceof Player) {
            $oldItem = $this->getItem($hotbarSlot);
            $ev = new PlayerItemHeldEvent($holder, $oldItem, $hotbarSlot);
            $ev->call();

            if ($ev->isCancelled()) {
                // Cancel the event, but don't revert the inventory slot
                $this->sendHeldItem($holder);
                return false;
            }

            // If event is not cancelled, set the held item index and send the updated item
            $this->setHeldItemIndex($hotbarSlot, false);
            $this->sendHeldItem($holder);
        } else {
            $this->setHeldItemIndex($hotbarSlot, false);
        }

        return true;
    }

    private function isHotbarSlot(int $slot) : bool{
        return $slot >= 0 and $slot <= $this->getHotbarSize();
    }

    /**
     * @throws InvalidArgumentException
     */
    private function throwIfNotHotbarSlot(int $slot) : void{
        if(!$this->isHotbarSlot($slot)){
            throw new InvalidArgumentException("$slot is not a valid hotbar slot index (expected 0 - " . ($this->getHotbarSize() - 1) . ")");
        }
    }

    /**
     * Returns the item in the specified hotbar slot.
     *
     * @throws InvalidArgumentException if the hotbar slot index is out of range
     */
    public function getHotbarSlotItem(int $hotbarSlot) : Item{
        $this->throwIfNotHotbarSlot($hotbarSlot);
        return $this->getItem($hotbarSlot);
    }

    /**
     * Returns the hotbar slot number the holder is currently holding.
     */
    public function getHeldItemIndex() : int{
        return $this->itemInHandIndex;
    }

    /**
     * Sets which hotbar slot the player is currently loading.
     *
     * @param int  $hotbarSlot 0-8 index of the hotbar slot to hold
     * @param bool $send Whether to send updates back to the inventory holder. This should usually be true for plugin calls.
     *                    It should only be false to prevent feedback loops of equipment packets between client and server.
     *
     * @return void
     * @throws InvalidArgumentException if the hotbar slot is out of range
     */
    public function setHeldItemIndex(int $hotbarSlot, bool $send = true){
        $this->throwIfNotHotbarSlot($hotbarSlot);

        $this->itemInHandIndex = $hotbarSlot;

        if($this->getHolder() instanceof Player and $send){
            $this->sendHeldItem($this->getHolder());
        }

        $this->sendHeldItem($this->getHolder()->getViewers());
    }

    /**
     * Returns the currently-held item.
     */
    public function getItemInHand() : Item{
        return $this->getHotbarSlotItem($this->itemInHandIndex);
    }

    /**
     * Sets the item in the currently-held slot to the specified item.
     */
    public function setItemInHand(Item $item) : bool{
        return $this->setItem($this->getHeldItemIndex(), $item);
    }

    /**
     * Sends the currently-held item to specified targets.
     *
     * @param Player|Player[] $target
     *
     * @return void
     */
    public function sendHeldItem($target){
        $item = $this->getItemInHand();

        $pk = new MobEquipmentPacket();
        $pk->entityRuntimeId = $this->getHolder()->getId();
        $pk->item = ItemStackWrapper::legacy($item);
        $pk->inventorySlot = $pk->hotbarSlot = $this->getHeldItemIndex();
        $pk->windowId = ContainerIds::INVENTORY;

        if(!is_array($target)){
            $target->dataPacket($pk);
            if($target === $this->getHolder()){
                $this->sendSlot($this->getHeldItemIndex(), $target);
            }
        }else{
            $this->getHolder()->getLevelNonNull()->getServer()->broadcastPacket($target, $pk);
            if(in_array($this->getHolder(), $target, true)){
                $this->sendSlot($this->getHeldItemIndex(), $this->getHolder());
            }
        }
    }

    /**
     * Returns the number of slots in the hotbar.
     */
    public function getHotbarSize() : int{
        return 9;
    }

    /**
     * @return void
     */
    public function sendCreativeContents(){
        //TODO: this mess shouldn't be in here
        $holder = $this->getHolder();
        if(!($holder instanceof Player)){
            throw new LogicException("Cannot send creative inventory contents to non-player inventory holder");
        }

        $nextEntryId = 1;
        $holder->sendDataPacket(CreativeContentPacket::create(array_map(function(Item $item) use (&$nextEntryId) : CreativeContentEntry{
            return new CreativeContentEntry($nextEntryId++, clone $item);
        }, $holder->isSpectator() ? [] : Item::getCreativeItems()))); //fill it for all gamemodes except spectator
    }

    /**
     * This override is here for documentation and code completion purposes only.
     * @return Human|Player
     */
    public function getHolder(){
        return $this->holder;
    }
}
