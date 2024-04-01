<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\StructureEditorData;

class StructureBlockUpdatePacket extends DataPacket/* implements ServerboundPacket*/
{
	public const NETWORK_ID = ProtocolInfo::STRUCTURE_BLOCK_UPDATE_PACKET;

	public int $x;
	public int $y;
	public int $z;
	public StructureEditorData $structureEditorData;
	public bool $isPowered;
	public bool $waterlogged;

	protected function decodePayload(){
		$this->getBlockPosition($this->x, $this->y, $this->z);
		$this->structureEditorData = $this->getStructureEditorData();
		$this->isPowered = $this->getBool();
		$this->waterlogged = $this->getBool();
	}

	protected function encodePayload(){
		$this->putBlockPosition($this->x, $this->y, $this->z);
		$this->putStructureEditorData($this->structureEditorData);
		$this->putBool($this->isPowered);
		$this->putBool($this->waterlogged);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleStructureBlockUpdate($this);
	}
}
