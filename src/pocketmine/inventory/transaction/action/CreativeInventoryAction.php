<?php

declare(strict_types=1);

namespace pocketmine\inventory\transaction\action;

use pocketmine\item\Item;
use pocketmine\Player;

class CreativeInventoryAction extends InventoryAction{

	/** Player put an item into the creative window to destroy it. */
	public const TYPE_DELETE_ITEM = 0;
	/** Player took an item from the creative window. */
	public const TYPE_CREATE_ITEM = 1;

	/** @var int */
	protected $actionType;

	public function __construct(Item $sourceItem, Item $targetItem, int $actionType){
		parent::__construct($sourceItem, $targetItem);
		$this->actionType = $actionType;
	}

	/**
	 * Checks that the player is in creative, and (if creating an item) that the item exists in the creative inventory.
	 */
	public function isValid(Player $source) : bool{
		return $source->isCreative(true) and
			($this->actionType === self::TYPE_DELETE_ITEM or Item::getCreativeItemIndex($this->sourceItem) !== -1);
	}

	/**
	 * Returns the type of the action.
	 */
	public function getActionType() : int{
		return $this->actionType;
	}

	/**
	 * No need to do anything extra here: this type just provides a place for items to disappear or appear from.
	 */
	public function execute(Player $source) : bool{
		return true;
	}

	public function onExecuteSuccess(Player $source) : void{

	}

	public function onExecuteFail(Player $source) : void{

	}
}
