<?php

declare(strict_types=1);

namespace pocketmine\scheduler;

use ClassLoader;
use Closure;
use InvalidArgumentException;
use pocketmine\Server;
use pocketmine\utils\Utils;
use ReflectionClass;
use ReflectionException;
use Threaded;
use ThreadedLogger;
use function array_keys;
use function assert;
use function count;
use function spl_object_hash;
use function time;
use const PHP_INT_MAX;
use const PTHREADS_INHERIT_CONSTANTS;
use const PTHREADS_INHERIT_INI;

/**
 * Manages general-purpose worker threads used for processing asynchronous tasks, and the tasks submitted to those
 * workers.
 */
class AsyncPool{
	private const WORKER_START_OPTIONS = PTHREADS_INHERIT_INI | PTHREADS_INHERIT_CONSTANTS;

	/** @var Server */
	private $server;

	/** @var ClassLoader */
	private $classLoader;
	/** @var ThreadedLogger */
	private $logger;
	/** @var int */
	protected $size;
	/** @var int */
	private $workerMemoryLimit;

	/**
	 * @var AsyncTask[]
	 * @phpstan-var array<int, AsyncTask>
	 */
	private $tasks = [];
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private $taskWorkers = [];
	/** @var int */
	private $nextTaskId = 1;

	/**
	 * @var AsyncWorker[]
	 * @phpstan-var array<int, AsyncWorker>
	 */
	private $workers = [];
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private $workerUsage = [];
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private $workerLastUsed = [];

	/**
	 * @var Closure[]
	 * @phpstan-var (\Closure(int $workerId) : void)[]
	 */
	private $workerStartHooks = [];

	public function __construct(Server $server, int $size, int $workerMemoryLimit, ClassLoader $classLoader, ThreadedLogger $logger){
		$this->server = $server;
		$this->size = $size;
		$this->workerMemoryLimit = $workerMemoryLimit;
		$this->classLoader = $classLoader;
		$this->logger = $logger;
	}

	/**
	 * Returns the maximum size of the pool. Note that there may be less active workers than this number.
	 */
	public function getSize() : int{
		return $this->size;
	}

	/**
	 * Increases the maximum size of the pool to the specified amount. This does not immediately start new workers.
	 */
	public function increaseSize(int $newSize) : void{
		if($newSize > $this->size){
			$this->size = $newSize;
		}
	}

	/**
	 * Registers a Closure callback to be fired whenever a new worker is started by the pool.
	 * The signature should be `function(int $worker) : void`
	 *
	 * This function will call the hook for every already-running worker.
	 *
	 * @phpstan-param Closure(int $workerId) : void $hook
	 */
	public function addWorkerStartHook(Closure $hook) : void{
		Utils::validateCallableSignature(function(int $worker) : void{ }, $hook);
		$this->workerStartHooks[spl_object_hash($hook)] = $hook;
		foreach($this->workers as $i => $worker){
			$hook($i);
		}
	}

	/**
	 * Removes a previously-registered callback listening for workers being started.
	 *
	 * @phpstan-param Closure(int $workerId) : void $hook
	 */
	public function removeWorkerStartHook(Closure $hook) : void{
		unset($this->workerStartHooks[spl_object_hash($hook)]);
	}

	/**
	 * Returns an array of IDs of currently running workers.
	 *
	 * @return int[]
	 */
	public function getRunningWorkers() : array{
		return array_keys($this->workers);
	}

	/**
	 * Fetches the worker with the specified ID, starting it if it does not exist, and firing any registered worker
	 * start hooks.
	 */
	private function getWorker(int $worker) : AsyncWorker{
		if(!isset($this->workers[$worker])){
			$this->workerUsage[$worker] = 0;
			$this->workers[$worker] = new AsyncWorker($this->logger, $worker, $this->workerMemoryLimit);
			$this->workers[$worker]->setClassLoader($this->classLoader);
			$this->workers[$worker]->start(self::WORKER_START_OPTIONS);

			foreach($this->workerStartHooks as $hook){
				$hook($worker);
			}
		}

		return $this->workers[$worker];
	}

	/**
	 * Submits an AsyncTask to an arbitrary worker.
	 */
	public function submitTaskToWorker(AsyncTask $task, int $worker) : void{
		if($worker < 0 or $worker >= $this->size){
			throw new InvalidArgumentException("Invalid worker $worker");
		}
		if($task->getTaskId() !== null){
			throw new InvalidArgumentException("Cannot submit the same AsyncTask instance more than once");
		}

		$task->progressUpdates = new Threaded;
		$taskId = $this->nextTaskId++;
		$task->setTaskId($taskId);

		$this->tasks[$taskId] = $task;

		$this->getWorker($worker)->stack($task);
		$this->workerUsage[$worker]++;
		$this->taskWorkers[$taskId] = $worker;
		$this->workerLastUsed[$worker] = time();
	}

	/**
	 * Selects a worker ID to run a task.
	 *
	 * - if an idle worker is found, it will be selected
	 * - else, if the worker pool is not full, a new worker will be selected
	 * - else, the worker with the smallest backlog is chosen.
	 */
	public function selectWorker() : int{
		$worker = null;
		$minUsage = PHP_INT_MAX;
		foreach($this->workerUsage as $i => $usage){
			if($usage < $minUsage){
				$worker = $i;
				$minUsage = $usage;
				if($usage === 0){
					break;
				}
			}
		}
		if($worker === null or ($minUsage > 0 and count($this->workers) < $this->size)){
			//select a worker to start on the fly
			for($i = 0; $i < $this->size; ++$i){
				if(!isset($this->workers[$i])){
					$worker = $i;
					break;
				}
			}
		}

		assert($worker !== null);
		return $worker;
	}

	/**
	 * Submits an AsyncTask to the worker with the least load. If all workers are busy and the pool is not full, a new
	 * worker may be started.
	 */
	public function submitTask(AsyncTask $task) : int{
		if($task->getTaskId() !== null){
			throw new InvalidArgumentException("Cannot submit the same AsyncTask instance more than once");
		}

		$worker = $this->selectWorker();
		$this->submitTaskToWorker($task, $worker);
		return $worker;
	}

	/**
	 * Removes a completed or crashed task from the pool.
	 */
	private function removeTask(AsyncTask $task, bool $force = false) : void{
		if(isset($this->taskWorkers[$task->getTaskId()])){
			if(!$force and ($task->isRunning() or !$task->isGarbage())){
				return;
			}
			$this->workerUsage[$this->taskWorkers[$task->getTaskId()]]--;
		}

		$task->removeDanglingStoredObjects();
		unset($this->tasks[$task->getTaskId()]);
		unset($this->taskWorkers[$task->getTaskId()]);
	}

	/**
	 * Removes all tasks from the pool, cancelling where possible. This will block until all tasks have been
	 * successfully deleted.
	 */
	public function removeTasks() : void{
		foreach($this->workers as $worker){
			/** @var AsyncTask $task */
			while(($task = $worker->unstack()) !== null){
				//cancelRun() is not strictly necessary here, but it might be used to inform plugins of the task state
				//(i.e. it never executed).
				assert($task instanceof AsyncTask);
				$task->cancelRun();
				$this->removeTask($task, true);
			}
		}
		do{
			foreach($this->tasks as $task){
				$task->cancelRun();
				$this->removeTask($task);
			}

			if(count($this->tasks) > 0){
				Server::microSleep(25000);
			}
		}while(count($this->tasks) > 0);

		for($i = 0; $i < $this->size; ++$i){
			$this->workerUsage[$i] = 0;
		}

		$this->taskWorkers = [];
		$this->tasks = [];

		$this->collectWorkers();
	}

	/**
	 * Collects garbage from running workers.
	 */
	private function collectWorkers() : void{
		foreach($this->workers as $worker){
			$worker->collect();
		}
	}

	/**
	 * Collects finished and/or crashed tasks from the workers, firing their on-completion hooks where appropriate.
	 *
	 * @throws ReflectionException
	 */
	public function collectTasks() : void{
		foreach($this->tasks as $task){
			$task->checkProgressUpdates($this->server);
			if($task->isGarbage() and !$task->isRunning() and !$task->isCrashed()){
				if(!$task->hasCancelledRun()){
					/*
					 * It's possible for a task to submit a progress update and then finish before the progress
					 * update is detected by the parent thread, so here we consume any missed updates.
					 *
					 * When this happens, it's possible for a progress update to arrive between the previous
					 * checkProgressUpdates() call and the next isGarbage() call, causing progress updates to be
					 * lost. Thus, it's necessary to do one last check here to make sure all progress updates have
					 * been consumed before completing.
					 */
					$task->checkProgressUpdates($this->server);
					$task->onCompletion($this->server);
				}

				$this->removeTask($task);
			}elseif($task->isCrashed()){
				$this->logger->critical("Could not execute asynchronous task " . (new ReflectionClass($task))->getShortName() . ": Task crashed");
				$this->removeTask($task, true);
			}
		}

		$this->collectWorkers();
	}

	/**
	 * Returns an array of worker ID => task queue size
	 *
	 * @return int[]
	 * @phpstan-return array<int, int>
	 */
	public function getTaskQueueSizes() : array{
		return $this->workerUsage;
	}

	public function shutdownUnusedWorkers() : int{
		$ret = 0;
		$time = time();
		foreach($this->workerUsage as $i => $usage){
			if($usage === 0 and (!isset($this->workerLastUsed[$i]) or $this->workerLastUsed[$i] + 300 < $time)){
				$this->workers[$i]->quit();
				unset($this->workers[$i], $this->workerUsage[$i], $this->workerLastUsed[$i]);
				$ret++;
			}
		}

		return $ret;
	}

	/**
	 * Cancels all pending tasks and shuts down all the workers in the pool.
	 */
	public function shutdown() : void{
		$this->collectTasks();
		$this->removeTasks();
		foreach($this->workers as $worker){
			$worker->quit();
		}
		$this->workers = [];
		$this->workerLastUsed = [];
	}
}
