<?php

declare(strict_types=1);

namespace pocketmine\event\entity;

use pocketmine\entity\object\ItemEntity;
use pocketmine\event\Cancellable;

/**
 * @phpstan-extends EntityEvent<ItemEntity>
 */
class ItemDespawnEvent extends EntityEvent implements Cancellable{

	public function __construct(ItemEntity $item){
		$this->entity = $item;

	}

	/**
	 * @return ItemEntity
	 */
	public function getEntity(){
		return $this->entity;
	}
}
