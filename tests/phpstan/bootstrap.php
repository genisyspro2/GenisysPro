<?php

declare(strict_types=1);

if(!defined('LEVELDB_ZLIB_RAW_COMPRESSION')){
	//leveldb might not be loaded
	define('LEVELDB_ZLIB_RAW_COMPRESSION', 4);
}

//TODO: these need to be defined properly or removed
define('pocketmine\COMPOSER_AUTOLOADER_PATH', dirname(__DIR__, 2) . '/vendor/autoload.php');
define('pocketmine\DATA', '');
define('pocketmine\GIT_COMMIT', str_repeat('00', 20));
define('pocketmine\BUILD_NUMBER', 0);
define('pocketmine\PLUGIN_PATH', '');
define('pocketmine\START_TIME', microtime(true));
define('pocketmine\VERSION', '9.9.9');

//opcache breaks PHPStan when dynamic reflection is used - see https://github.com/phpstan/phpstan-src/pull/801#issuecomment-978431013
ini_set('opcache.enable', 'off');
