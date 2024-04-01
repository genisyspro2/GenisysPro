<?php

declare(strict_types=1);

namespace pmmp\TesterPlugin\tests;

use pmmp\TesterPlugin\Test;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\MainLogger;
use function ob_end_flush;
use function ob_get_contents;
use function ob_start;
use function strpos;

class AsyncTaskMainLoggerTest extends Test{

	public function run() : void{
		$this->getPlugin()->getServer()->getAsyncPool()->submitTask(new class($this) extends AsyncTask{

			/** @var bool */
			protected $success = false;

			public function __construct(AsyncTaskMainLoggerTest $testObject){
				$this->storeLocal($testObject);
			}

			public function onRun(){
				ob_start();
				MainLogger::getLogger()->info("Testing");
				$contents = ob_get_contents();
				if($contents === false) throw new AssumptionFailedError("ob_get_contents() should not return false here");
				if(strpos($contents, "Testing") !== false){
					$this->success = true;
				}
				ob_end_flush();
			}

			public function onCompletion(Server $server){
				/** @var AsyncTaskMainLoggerTest $test */
				$test = $this->fetchLocal();
				$test->setResult($this->success ? Test::RESULT_OK : Test::RESULT_FAILED);
			}
		});
	}

	public function getName() : string{
		return "MainLogger::getLogger() works in AsyncTasks";
	}

	public function getDescription() : string{
		return "Verifies that the MainLogger is accessible by MainLogger::getLogger() in an AsyncTask";
	}

}
