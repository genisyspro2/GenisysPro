<?php

declare(strict_types=1);

namespace pocketmine\entity\pathfinder;

use pocketmine\math\Vector3;
use function array_slice;

class Path{

	/* @var PathPoint[] */
	protected $points = [];

	protected $currentIndex = 0;

	public function __construct(array $points = []){
		$this->points = $points;
	}

	public function havePath() : bool{
		return !empty($this->points) and $this->currentIndex < count($this->points) - 1;
	}

	public function getVectorByIndex(int $index) : ?Vector3{
		$point = $this->getPointByIndex($index);
		if($point === null) return null;

		return new Vector3($point->x, $point->height, $point->y);
	}

	public function getFinalPathPoint() : ?PathPoint{
		return end($this->points);
	}

	public function getPointByIndex(int $index) : ?PathPoint{
		return $this->points[$index] ?? null;
	}

	public function removePoint(int $index) : void{
		unset($this->points[$index]);
	}

	/**
	 * @return PathPoint[]
	 */
	public function getPoints() : array{
		return $this->points;
	}

	/**
	 * @return int
	 */
	public function getCurrentIndex() : int{
		return $this->currentIndex;
	}

	/**
	 * @param int $currentIndex
	 */
	public function setCurrentIndex(int $currentIndex) : void{
		$this->currentIndex = $currentIndex;
	}

	public function limitPath(int $maxLength) : void{
		$this->points = array_slice($this->points, 0, $maxLength + 1);
	}
}
