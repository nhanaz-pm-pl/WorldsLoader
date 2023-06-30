<?php

declare(strict_types=1);

namespace NhanAZ\WorldsLoader;

use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

	protected function onEnable(): void {
		$worlds = array_diff(scandir($this->getServer()->getDataPath() . "worlds"), ["..", "."]);
		foreach ($worlds as $worldName) {
			if ($this->getServer()->getWorldManager()->loadWorld($worldName)) {
				$this->getLogger()->info("Successfully loaded $worldName");
			}
		}
	}
}
