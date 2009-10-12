<?php


require_once t3lib_extMgm::extPath('l10nmgr') . '/domain/tools/class.tx_l10nmgr_tools.php';

class tx_l10nmgr_service_autoload {
	
	/**
	 * Autoload method
	 *
	 * @param string class name
	 * @return bool
	 */
	public static function autoLoad($className) {
		if (t3lib_div::isFirstPartOfStr($className, 'tx_l10nmgr')) {
			$classNameParts = t3lib_div::trimExplode('_', $className);
			$classNameParts = array_slice($classNameParts, 2, -1);
			$fileName = t3lib_extMgm::extPath('l10nmgr') . implode('/', $classNameParts) .'/class.' . $className . '.php';
			if (is_file($fileName)) {
				require_once $fileName;
			}
		}
	}
}

?>