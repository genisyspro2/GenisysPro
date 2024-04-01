<?php

declare(strict_types=1);

namespace pocketmine\event\level;

use pocketmine\level\format\Chunk;
use pocketmine\level\Level;

/**
 * Chunk-related events
 */
abstract class ChunkEvent extends LevelEvent{
	/** @var Chunk */
	private $chunk;

	public function __construct(Level $level, Chunk $chunk){
		parent::__construct($level);
		$this->chunk = $chunk;
	}

	public function getChunk() : Chunk{
		return $this->chunk;
	}
}
