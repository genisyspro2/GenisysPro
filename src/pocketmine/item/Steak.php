<?php

declare(strict_types=1);

namespace pocketmine\item;

class Steak extends Food{
	public function __construct(int $meta = 0){
		parent::__construct(self::STEAK, $meta, "Steak");
	}

	public function getFoodRestore() : int{
		return 8;
	}

	public function getSaturationRestore() : float{
		return 12.8;
	}
}
