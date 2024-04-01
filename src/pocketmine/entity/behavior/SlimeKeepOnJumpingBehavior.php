<?php

declare(strict_types=1);

namespace pocketmine\entity\behavior;

use pocketmine\entity\hostile\Slime;

class SlimeKeepOnJumpingBehavior extends Behavior{
	/** @var Slime */
	protected $mob;

	public function __construct(Slime $slime){
		parent::__construct($slime);

		$this->setMutexBits(5);
	}

	public function canStart() : bool{
		return true;
	}

	public function onTick() : void{
		$this->mob->getMoveHelper()->setSpeed(1.0);
	}
}
