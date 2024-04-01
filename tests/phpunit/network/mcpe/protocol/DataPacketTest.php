<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

use PHPUnit\Framework\TestCase;

class DataPacketTest extends TestCase{

	public function testHeaderFidelity() : void{
		$pk = new TestPacket();
		$pk->senderSubId = 3;
		$pk->recipientSubId = 2;
		$pk->encode();

		$pk2 = new TestPacket();
		$pk2->setBuffer($pk->getBuffer());
		$pk2->decode();
		self::assertSame($pk2->senderSubId, 3);
		self::assertSame($pk2->recipientSubId, 2);
	}
}
