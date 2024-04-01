<?php

declare(strict_types=1);

namespace pocketmine\level\particle;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class GenericParticle extends Particle{
	/** @var int */
	protected $id;
	/** @var int */
	protected $data;

	public function __construct(Vector3 $pos, int $id, int $data = 0){
		parent::__construct($pos->x, $pos->y, $pos->z);
		$this->id = $id & 0xFFF;
		$this->data = $data;
	}

	public function encode(){
		$pk = new LevelEventPacket;
		$pk->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | $this->id;
		$pk->position = $this->asVector3();
		$pk->data = $this->data;

		return $pk;
	}
}
