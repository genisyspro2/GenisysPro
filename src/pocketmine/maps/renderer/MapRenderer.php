<?php

namespace pocketmine\maps\renderer;

use pocketmine\maps\MapData;
use pocketmine\Player;

abstract class MapRenderer{

	public function initialize(MapData $mapData) : void{

	}

	/**
	 * Renders a map
	 *
	 * @param MapData $mapData
	 * @param Player  $player
	 */
	public abstract function render(MapData $mapData, Player $player) : void;

	public function onMapCreated(Player $player, MapData $mapData) : void{

	}
}
