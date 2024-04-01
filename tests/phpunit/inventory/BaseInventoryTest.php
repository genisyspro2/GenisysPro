<?php

declare(strict_types=1);

namespace pocketmine\inventory;

use PHPUnit\Framework\TestCase;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

class BaseInventoryTest extends TestCase{

	public static function setUpBeforeClass() : void{
		ItemFactory::init();
	}

	public function testAddItemDifferentUserData() : void{
		$inv = new class extends BaseInventory{
			public function getDefaultSize() : int{
				return 1;
			}
			public function getName() : string{
				return "";
			}
		};
		$item1 = ItemFactory::get(Item::ARROW, 0, 1);
		$item2 = ItemFactory::get(Item::ARROW, 0, 1)->setCustomName("TEST");

		$inv->addItem(clone $item1);
		self::assertFalse($inv->canAddItem($item2), "Item WITHOUT userdata should not stack with item WITH userdata");
		self::assertNotEmpty($inv->addItem($item2));

		$inv->clearAll();
		self::assertEmpty($inv->getContents());

		$inv->addItem(clone $item2);
		self::assertFalse($inv->canAddItem($item1), "Item WITH userdata should not stack with item WITHOUT userdata");
		self::assertNotEmpty($inv->addItem($item1));
	}
}
