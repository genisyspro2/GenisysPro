<?php

declare(strict_types=1);

namespace pocketmine\level\generator\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;
use function rand;

class Cactus extends Populator{
	/** @var ChunkManager */
	private $level;
	private $randomAmount;
	private $baseAmount;

	public function setRandomAmount($amount){
		$this->randomAmount = $amount;
	}

	public function setBaseAmount($amount){
		$this->baseAmount = $amount;
	}

	public function populate(ChunkManager $level, int $chunkX, int $chunkZ, Random $random){
		$this->level = $level;
		$amount = $random->nextRange(0, $this->randomAmount + 1) + $this->baseAmount;
		for($i = 0; $i < $amount; ++$i){
			$x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 15);
			$z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 15);
			$y = $this->getHighestWorkableBlock($x, $z);
			if($y === -1){
				continue;
			}

			if(rand(1, 2) === 1){
				$this->level->setBlockIdAt($x, $y, $z, Block::CACTUS);
				$this->level->setBlockIdAt($x, $y + 1, $z, Block::CACTUS);
			}else{
				$this->level->setBlockIdAt($x, $y, $z, Block::CACTUS);
				$this->level->setBlockIdAt($x, $y + 1, $z, Block::CACTUS);
				$this->level->setBlockIdAt($x, $y + 2, $z, Block::CACTUS);
			}
		}
	}

	private function getHighestWorkableBlock(int $x, int $z) : int{
		for($y = 127; $y > 0; --$y){
			$b = $this->level->getBlockIdAt($x, $y, $z);
			if($b === Block::SAND){
				break;
			}elseif($b !== Block::AIR){
				return -1;
			}
		}

		return ++$y;
	}
}
