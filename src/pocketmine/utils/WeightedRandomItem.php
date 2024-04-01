<?php

declare(strict_types=1);

namespace pocketmine\utils;

class WeightedRandomItem{
	/** @var mixed */
	public $item = null;

	/** @var int */
	public $itemWeight = 0;

	public function __construct(int $itemWeight, $item = null){
		$this->itemWeight = $itemWeight;
		$this->item = $item;
	}

	/**
	 * @param WeightedRandomItem[] $weightedItems
	 *
	 * @return int
	 */
	public static function getTotalWeight(array $weightedItems) : int{
		$total = 0;
		foreach($weightedItems as $weightedItem){
			$total += $weightedItem->itemWeight;
		}

		return $total;
	}

	/**
	 * @param Random               $random
	 * @param WeightedRandomItem[] $items
	 * @param int                  $totalWeight
	 *
	 * @return null|WeightedRandomItem
	 */
	public static function getRandomItem(Random $random, array $items, int $totalWeight) : ?WeightedRandomItem{
		return self::getRandomItemFromCollection($items, $random->nextBoundedInt($totalWeight));
	}

	/**
	 * @param WeightedRandomItem[] $collection
	 * @param int                  $weight
	 *
	 * @return null|WeightedRandomItem
	 */
	public static function getRandomItemFromCollection(array $collection, int $weight) : ?WeightedRandomItem{
		foreach($collection as $item){
			$weight -= $item->itemWeight;

			if($weight < 0){
				return $item;
			}
		}

		return null;
	}
}
