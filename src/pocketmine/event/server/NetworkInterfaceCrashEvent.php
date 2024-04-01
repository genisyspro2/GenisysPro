<?php

declare(strict_types=1);

namespace pocketmine\event\server;

use pocketmine\network\SourceInterface;
use Throwable;

/**
 * Never called. Should never have come into this world. Nothing to see here.
 * @deprecated
 */
class NetworkInterfaceCrashEvent extends NetworkInterfaceEvent{
	/** @var Throwable */
	private $exception;

	public function __construct(SourceInterface $interface, Throwable $throwable){
		parent::__construct($interface);
		$this->exception = $throwable;
	}

	public function getCrashInformation() : Throwable{
		return $this->exception;
	}
}
