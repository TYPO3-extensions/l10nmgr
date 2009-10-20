<?php

require_once t3lib_extMgm::extPath('l10nmgr') . '/domain/tools/class.tx_l10nmgr_tools.php';

class tx_l10nmgr_service_autoload {

	/**
	 * @var string
	 */
	protected static $extPath;

	/**
	 * Retrieves the cached extension path;
	 *
	 * @param void
	 * @return string
	 *
	 * @author Timo Schmidt
	 */
	protected static function getExtPath(){
		if(!isset(self::$extPath)){
			self::$extPath =  t3lib_extMgm::extPath('l10nmgr');
		}

		return self::$extPath;
	}

	/**
	 * Autoload method
	 *
	 * @param string class name
	 * @return bool
	 *
	 * @author Timo Schmidt
	 */
	public static function autoLoad($className) {
		if (t3lib_div::isFirstPartOfStr($className, 'tx_l10nmgr')) {
			$classNameParts = t3lib_div::trimExplode('_', $className);
			$classNameParts = array_slice($classNameParts, 2, -1);
			$fileName = self::getExtPath(). implode('/', $classNameParts) .'/class.' . $className . '.php';
			if (is_file($fileName)) {
				require_once $fileName;
			}
		}
	}
}
?>