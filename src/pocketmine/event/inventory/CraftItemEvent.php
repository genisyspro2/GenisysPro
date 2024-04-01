<?php

declare(strict_types=1);

namespace pocketmine\event\inventory;

use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\inventory\CraftingRecipe;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\item\Item;
use pocketmine\Player;

class CraftItemEvent extends Event implements Cancellable{
	/** @var CraftingTransaction */
	private $transaction;
	/** @var CraftingRecipe */
	private $recipe;
	/** @var int */
	private $repetitions;
	/** @var Item[] */
	private $inputs;
	/** @var Item[] */
	private $outputs;

	/**
	 * @param Item[] $inputs
	 * @param Item[] $outputs
	 */
	public function __construct(CraftingTransaction $transaction, CraftingRecipe $recipe, int $repetitions, array $inputs, array $outputs){
		$this->transaction = $transaction;
		$this->recipe = $recipe;
		$this->repetitions = $repetitions;
		$this->inputs = $inputs;
		$this->outputs = $outputs;
	}

	/**
	 * Returns the inventory transaction involved in this crafting event.
	 */
	public function getTransaction() : CraftingTransaction{
		return $this->transaction;
	}

	/**
	 * Returns the recipe crafted.
	 */
	public function getRecipe() : CraftingRecipe{
		return $this->recipe;
	}

	/**
	 * Returns the number of times the recipe was crafted. This is usually 1, but might be more in the case of recipe
	 * book shift-clicks (which craft lots of items in a batch).
	 */
	public function getRepetitions() : int{
		return $this->repetitions;
	}

	/**
	 * Returns a list of items destroyed as ingredients of the recipe.
	 *
	 * @return Item[]
	 */
	public function getInputs() : array{
		return $this->inputs;
	}

	/**
	 * Returns a list of items created by crafting the recipe.
	 *
	 * @return Item[]
	 */
	public function getOutputs() : array{
		return $this->outputs;
	}

	public function getPlayer() : Player{
		return $this->transaction->getSource();
	}
}
