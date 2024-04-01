<?php

declare(strict_types=1);

namespace pocketmine\event;

/**
 * Events that can be cancelled must use the interface Cancellable
 */
interface Cancellable{
	public function isCancelled() : bool;

	/**
	 * @return void
	 */
	public function setCancelled(bool $value = true);
}
