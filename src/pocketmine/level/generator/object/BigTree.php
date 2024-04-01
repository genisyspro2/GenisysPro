<?php

declare(strict_types=1);

namespace pocketmine\level\generator\object;

use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

/**
 * @deprecated
 */
class BigTree extends Tree{

	public function canPlaceObject(ChunkManager $level, int $x, int $y, int $z, Random $random) : bool{
		return false;
	}

	/**
	 * @return void
	 */
	public function placeObject(ChunkManager $level, int $x, int $y, int $z, Random $random){

	}
}
