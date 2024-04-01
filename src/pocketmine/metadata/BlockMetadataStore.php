<?php

declare(strict_types=1);

namespace pocketmine\metadata;

use InvalidStateException;
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\plugin\Plugin;

class BlockMetadataStore extends MetadataStore{
	/** @var Level */
	private $owningLevel;

	public function __construct(Level $owningLevel){
		$this->owningLevel = $owningLevel;
	}

	private function disambiguate(Block $block, string $metadataKey) : string{
		if($block->getLevel() !== $this->owningLevel){
			throw new InvalidStateException("Block does not belong to world " . $this->owningLevel->getName());
		}
		return $block->x . ":" . $block->y . ":" . $block->z . ":" . $metadataKey;
	}

	/**
	 * @return MetadataValue[]
	 */
	public function getMetadata(Block $subject, string $metadataKey){
		return $this->getMetadataInternal($this->disambiguate($subject, $metadataKey));
	}

	public function hasMetadata(Block $subject, string $metadataKey) : bool{
		return $this->hasMetadataInternal($this->disambiguate($subject, $metadataKey));
	}

	/**
	 * @return void
	 */
	public function removeMetadata(Block $subject, string $metadataKey, Plugin $owningPlugin){
		$this->removeMetadataInternal($this->disambiguate($subject, $metadataKey), $owningPlugin);
	}

	/**
	 * @return void
	 */
	public function setMetadata(Block $subject, string $metadataKey, MetadataValue $newMetadataValue){
		$this->setMetadataInternal($this->disambiguate($subject, $metadataKey), $newMetadataValue);
	}
}
