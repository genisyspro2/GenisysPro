<?php

declare(strict_types=1);

namespace pocketmine\level\generator\biome;

use pocketmine\level\biome\Biome;
use pocketmine\level\biome\UnknownBiome;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\utils\Random;
use RuntimeException;
use SplFixedArray;

abstract class BiomeSelector{
	/** @var Simplex */
	private $temperature;
	/** @var Simplex */
	private $rainfall;

	/**
	 * @var Biome[]|SplFixedArray
	 * @phpstan-var SplFixedArray<Biome>
	 */
	private $map = null;

	public function __construct(Random $random){
		$this->temperature = new Simplex($random, 2, 1 / 16, 1 / 512);
		$this->rainfall = new Simplex($random, 2, 1 / 16, 1 / 512);
	}

	/**
	 * Lookup function called by recalculate() to determine the biome to use for this temperature and rainfall.
	 *
	 * @return int biome ID 0-255
	 */
	abstract protected function lookup(float $temperature, float $rainfall) : int;

	/**
	 * @return void
	 */
	public function recalculate(){
		$this->map = new SplFixedArray(64 * 64);

		for($i = 0; $i < 64; ++$i){
			for($j = 0; $j < 64; ++$j){
				$biome = Biome::getBiome($this->lookup($i / 63, $j / 63));
				if($biome instanceof UnknownBiome){
					throw new RuntimeException("Unknown biome returned by selector with ID " . $biome->getId());
				}
				$this->map[$i + ($j << 6)] = $biome;
			}
		}
	}

	/**
	 * @param float $x
	 * @param float $z
	 *
	 * @return float
	 */
	public function getTemperature($x, $z){
		return ($this->temperature->noise2D($x, $z, true) + 1) / 2;
	}

	/**
	 * @param float $x
	 * @param float $z
	 *
	 * @return float
	 */
	public function getRainfall($x, $z){
		return ($this->rainfall->noise2D($x, $z, true) + 1) / 2;
	}

	/**
	 * @param int $x
	 * @param int $z
	 */
	public function pickBiome($x, $z) : Biome{
		$temperature = (int) ($this->getTemperature($x, $z) * 63);
		$rainfall = (int) ($this->getRainfall($x, $z) * 63);

		return $this->map[$temperature + ($rainfall << 6)];
	}
}
