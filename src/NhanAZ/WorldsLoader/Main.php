<?php

declare(strict_types=1);

namespace NhanAZ\WorldsLoader;

use GlobalLogger;
use pocketmine\plugin\PluginBase;
use pocketmine\world\format\io\exception\UnsupportedWorldFormatException;
use pocketmine\world\format\io\FormatConverter;
use pocketmine\world\format\io\WorldProviderManager;
use pocketmine\world\format\io\WorldProviderManagerEntry;
use pocketmine\world\format\io\WritableWorldProviderManagerEntry;

class Main extends PluginBase {

	protected function onEnable(): void {
		$worlds = array_diff(scandir($this->getServer()->getDataPath() . "worlds"), ["..", "."]);
		$lodedWorld = [];
		foreach ($worlds as $worldName) {
            try {
                if ($this->getServer()->getWorldManager()->loadWorld($worldName)) {
                    $lodedWorld[] = $worldName;
                }
            } catch (UnsupportedWorldFormatException) {
                $providerManager = new WorldProviderManager();
                $writableFormats = array_filter(
                    $providerManager->getAvailableProviders(),
                    fn(WorldProviderManagerEntry $class) => $class instanceof WritableWorldProviderManagerEntry
                );
                $worldPath = $this->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $worldName;
                $backupPath = $this->getDataFolder() . $worldName;
                $toFormat = $this->getServer()->getConfigGroup()->getProperty("level-settings.default-format", "leveldb");
                $oldProviderClasses = $providerManager->getMatchingProviders($worldPath);
                if(count($oldProviderClasses) === 0){
                    $this->getLogger()->warning("{$worldName} world format unknown!");
                }
                if (count($oldProviderClasses) > 1) {
                    $this->getLogger()->warning("Ambiguous world format: matched " . count($oldProviderClasses) . " (" . implode(array_keys($oldProviderClasses)) . ")");
                }
                $oldProviderClass = array_shift($oldProviderClasses);
                $oldProvider = $oldProviderClass->fromPath($worldPath, new \PrefixedLogger(\GlobalLogger::get(), "Old World Provider"));
                $converter = new FormatConverter($oldProvider, $writableFormats[$toFormat], $backupPath, GlobalLogger::get());
                $converter->execute();
                $lodedWorld[] = $worldName;
            }
		}
        $this->getLogger()->info("Worlds loaded successfully: [" . implode(", ", $lodedWorld) . "]");
	}
}
