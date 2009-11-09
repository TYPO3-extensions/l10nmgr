<?php
/***************************************************************
 *  Copyright notice
 *
 *  Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'] = array();
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_l10nmgr'] = 'EXT:l10nmgr/class.l10nmgr_tcemain_hook.php:&tx_l10nmgr_tcemain_hook';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks']['tx_l10nmgr'] = 'EXT:l10nmgr/class.l10nmgr_tcemain_hook.php:&tx_l10nmgr_tcemain_hook->stat';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');
PHPUnit_Util_Filter::addDirectoryToFilter (
	PATH_site, '.php'
);
PHPUnit_Util_Filter::addDirectoryToFilter (
	PATH_site . TYPO3_mainDir, '.php'
);
PHPUnit_Util_Filter::addDirectoryToFilter (
	PATH_t3lib, '.php'
);
PHPUnit_Util_Filter::removeDirectoryFromFilter(
	t3lib_extMgm::extPath('l10nmgr'), '.php'
);
PHPUnit_Util_Filter::addDirectoryToFilter(
	t3lib_extMgm::extPath('l10nmgr') . 'templates', '.php'
);
PHPUnit_Util_Filter::addDirectoryToFilter(
	t3lib_extMgm::extPath('l10nmgr') . 'interface', '.php'
);

PHPUnit_Util_Filter::addFileToFilter(t3lib_extMgm::extPath('l10nmgr') . 'class.l10nmgr_tcemain_hook.php');

/**
 * This supports the l10nmgr database tests and provide some helper.
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_tests_databaseTestcase.php
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @version $Id$
 * @date $Date$
 * @since 15.10.2009 - 22:07:23
 * @category tests
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 * @abstract
 */
abstract class tx_l10nmgr_tests_databaseTestcase extends tx_phpunit_database_testcase {

	/**
	 * Temporary store for the indexed_search registered HOOKS.
	 *
	 * The hooks must be reset because they produce an side effect on the tests which is not desired.
	 *
	 * @var array
	 */
	private $indexedSearchHook = array();

	/**
	 * This method unregister the indexed_search hooks on TCEmain processCmdmapClass & processDatamapClass
	 * This is in some database tests required because the indexed_search will increase the requirements on the database tables.
	 *
	 * @access protected
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	protected function unregisterIndexedSearchHooks() {

			// unset the indexed_search hooks
		if (t3lib_extMgm::isLoaded('indexed_search')) {
			$this->indexedSearchHook['processCmdmapClass']  = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx_indexedsearch'];
			$this->indexedSearchHook['processDatamapClass'] = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_indexedsearch'];
			unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx_indexedsearch']);
			unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_indexedsearch']);
		}
	}

	/**
	 * This method is used to check if an testcase runs in the
	 * correct workspace context.
	 *
	 * @param $wsId
	 * @return void
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	protected function skipInWrongWorkspaceContext($wsId = 0){
		global $BE_USER;
		if($BE_USER->user['workspace_id'] != $wsId){
			$this->markTestSkipped('Run this test only in the workspace '.$wsId);
		}
	}

	/**
	 * Restore the indexed_search hooks.
	 *
	 * @access protected
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	protected function restoreIndexedSearchHooks() {

			// restore the indexed_search hooks
		if (t3lib_extMgm::isLoaded('indexed_search')) {
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx_indexedsearch']  = $this->indexedSearchHook['processCmdmapClass'];
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_indexedsearch'] = $this->indexedSearchHook['processDatamapClass'];
		}
	}

	/**
	 * Import dataset into test database
	 *
	 * This will only work if the fixture locate at the same directory level as the testcase.
	 *
	 * @example $this->importDataSet('/fixtures/__FILENAME__.xml');
	 *
	 * @param string $pathToFile The path beginning from the current location of the testcase
	 *
	 * @uses tx_phpunit_database_testcase::importDataSet
	 *
	 * @access protected
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	protected function importDataSet($pathToFile) {
		parent::importDataSet(dirname ( __FILE__ ) . $pathToFile);
	}

	/**
	 * Creates a proxy class of the specified class which allows
	 * for calling even protected methods and access of protected properties.
	 *
	 * @param protected $className Full qualified name of the original class
	 *
	 * @access protected
	 * @return string Full qualified name of the built class
	 *
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function buildAccessibleProxy($className) {
		$accessibleClassName = uniqid('AccessibleTestProxy');
		$class = new ReflectionClass($className);
		$abstractModifier = $class->isAbstract() ? 'abstract ' : '';

		eval('
			' . $abstractModifier . 'class ' . $accessibleClassName . ' extends ' . $className . ' {
				public function _call($methodName) {
					$functionParameters = func_get_args();
					return call_user_func_array(array($this, $methodName), array_slice($functionParameters, 1));
				}
				public function _callRef($methodName, &$arg1 = NULL, &$arg2 = NULL, &$arg3 = NULL, &$arg4 = NULL, &$arg5= NULL, &$arg6 = NULL, &$arg7 = NULL, &$arg8 = NULL, &$arg9 = NULL) {
					switch (func_num_args()) {
						case 0 : return $this->$methodName();
						case 1 : return $this->$methodName($arg1);
						case 2 : return $this->$methodName($arg1, $arg2);
						case 3 : return $this->$methodName($arg1, $arg2, $arg3);
						case 4 : return $this->$methodName($arg1, $arg2, $arg3, $arg4);
						case 5 : return $this->$methodName($arg1, $arg2, $arg3, $arg4, $arg5);
						case 6 : return $this->$methodName($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
						case 7 : return $this->$methodName($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7);
						case 8 : return $this->$methodName($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8);
						case 9 : return $this->$methodName($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9);
					}
				}
				public function _set($propertyName, $value) {
					$this->$propertyName = $value;
				}
				public function _setRef($propertyName, &$value) {
					$this->$propertyName = $value;
				}
				public function _get($propertyName) {
					return $this->$propertyName;
				}
			}
		');
		return $accessibleClassName;
	}
}

?>