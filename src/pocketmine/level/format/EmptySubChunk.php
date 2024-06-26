<?php

declare(strict_types=1);

namespace pocketmine\level\format;

use function str_repeat;

class EmptySubChunk implements SubChunkInterface{
	/** @var EmptySubChunk */
	private static $instance;

	public static function getInstance() : self{
		if(self::$instance === null){
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function isEmpty(bool $checkLight = true) : bool{
		return true;
	}

	public function getBlockId(int $x, int $y, int $z) : int{
		return 0;
	}

	public function setBlockId(int $x, int $y, int $z, int $id) : bool{
		return false;
	}

	public function getBlockData(int $x, int $y, int $z) : int{
		return 0;
	}

	public function setBlockData(int $x, int $y, int $z, int $data) : bool{
		return false;
	}

	public function getFullBlock(int $x, int $y, int $z) : int{
		return 0;
	}

	public function setBlock(int $x, int $y, int $z, ?int $id = null, ?int $data = null) : bool{
		return false;
	}

	public function getBlockLight(int $x, int $y, int $z) : int{
		return 0;
	}

	public function setBlockLight(int $x, int $y, int $z, int $level) : bool{
		return false;
	}

	public function getBlockSkyLight(int $x, int $y, int $z) : int{
		return 15;
	}

	public function setBlockSkyLight(int $x, int $y, int $z, int $level) : bool{
		return false;
	}

	public function getHighestBlockAt(int $x, int $z) : int{
		return -1;
	}

	public function getBlockIdColumn(int $x, int $z) : string{
		return "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
	}

	public function getBlockDataColumn(int $x, int $z) : string{
		return "\x00\x00\x00\x00\x00\x00\x00\x00";
	}

	public function getBlockLightColumn(int $x, int $z) : string{
		return "\x00\x00\x00\x00\x00\x00\x00\x00";
	}

	public function getBlockSkyLightColumn(int $x, int $z) : string{
		return "\xff\xff\xff\xff\xff\xff\xff\xff";
	}

	public function getBlockIdArray() : string{
		return str_repeat("\x00", 4096);
	}

	public function getBlockDataArray() : string{
		return str_repeat("\x00", 2048);
	}

	public function getBlockLightArray() : string{
		return str_repeat("\x00", 2048);
	}

	public function setBlockLightArray(string $data){

	}

	public function getBlockSkyLightArray() : string{
		return str_repeat("\xff", 2048);
	}

	public function setBlockSkyLightArray(string $data){

	}

	public function networkSerialize() : string{
		return "\x00" . str_repeat("\x00", 6144);
	}
}
