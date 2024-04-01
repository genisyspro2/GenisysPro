<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe;

use PHPUnit\Framework\TestCase;

class StupidJsonDecodeTest extends TestCase{
	/**
	 * @var \Closure
	 * @phpstan-var \Closure(string $json, bool $assoc=) : mixed
	 */
	private $stupidJsonDecodeFunc;

	public function setUp() : void{
		$this->stupidJsonDecodeFunc = (new \ReflectionMethod(PlayerNetworkSessionAdapter::class, 'stupid_json_decode'))->getClosure();
	}

	/**
	 * @return mixed[][]
	 * @phpstan-return list<array{string,mixed}>
	 */
	public function stupidJsonDecodeProvider() : array{
		return [
			["[\n   \"a\",\"b,c,d,e\\\"   \",,0,1,2, false, 0.001]", ['a', 'b,c,d,e"   ', '', 0, 1, 2, false, 0.001]],
			["0", 0],
			["false", false],
			["NULL", null],
			['["\",,\"word","a\",,\"word2",]', ['",,"word', 'a",,"word2', '']],
			['["\",,\"word","a\",,\"word2",""]', ['",,"word', 'a",,"word2', '']],
			['["Hello,, PocketMine"]', ['Hello,, PocketMine']],
			['[,]', ['', '']],
			['[]', []]
		];
	}

	/**
	 * @dataProvider stupidJsonDecodeProvider
	 *
	 * @param mixed  $expect
	 *
	 * @throws \ReflectionException
	 */
	public function testStupidJsonDecode(string $brokenJson, $expect) : void{
		$decoded = ($this->stupidJsonDecodeFunc)($brokenJson, true);
		self::assertEquals($expect, $decoded);
	}
}
