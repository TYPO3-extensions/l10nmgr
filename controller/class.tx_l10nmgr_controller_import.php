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

	// autoload the mvc
t3lib_extMgm::isLoaded('mvc', true);
tx_mvc_common_classloader::loadAll();

require_once(t3lib_extMgm::extPath('l10nmgr').'models/importer/class.tx_l10nmgr_models_importer_importData.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/importer/class.tx_l10nmgr_models_importer_importDataRepository.php');

/**
 * Controller to import different formats of translations back into the TYPO3 environment
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_controller_import.php
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @HeadURL $HeadURL$
 * @version $Id$
 * @date $LastChangedDate$
 * @since 23.04.2009 - 14:52:35
 * @see tx_mvc_controller_action
 * @category controller
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_controller_import extends tx_mvc_controller_action {

	/**
	 * @var string
	 */
	protected $extensionKey = 'l10nmgr';

	/**
	 * @var string
	 */
	protected $defaultActionMethodName = 'generateImportAction';

	/**
	 * @var string
	 */
	protected $argumentsNamespace = 'tx_l10nmgrimport';

	/**
	 * Called before processing - used to initialise the arguments
	 *
	 * @access protected
	 * @return void
	 */
	protected function initializeArguments() {
		//!TODO implement function "initializeArguments"
	}

	/**
	 * Retrieves the uid of the currently created exportdata record
	 * 
	 * @todo this is the same as in the export controller maybe it can be done in a more generall way?
	 * 
	 * @param void
	 * @return bool true if the record was created
	 */
	protected function mapReturnEditConftoArguments($table, $mapTo) {

		$serializedReturnEditConf = t3lib_div::_GET('returnEditConf');

		if (!empty($serializedReturnEditConf)) {
			$returnEditConf = unserialize(t3lib_div::_GET('returnEditConf'));
			$editedRecord = array_keys($returnEditConf[$table]);

			if ($returnEditConf[$table][$editedRecord[0]] == 'edit') {
				$this->arguments[$mapTo] = $editedRecord[0];
				return true;
			} elseif ($returnEditConf[$table][$editedRecord[0]] == 'new') {
				return false;
			} else {
				throw new Exception('Unkown return value');
			}

		}
	}
		
	
	/**
	 * Show the controll panel to give the user the options what he can do
	 *
	 * @access public
	 * @author Timo Schmidt
	 * @return string HTML formated output
	 */
	public function generateImportAction() {
		$dataWasFound = $this->mapReturnEditConftoArguments('tx_l10nmgr_importdata', 'importDataId');

		$importDataRepository = new tx_l10nmgr_models_importer_importDataRepository();
		$importData = $importDataRepository->findById($this->arguments['importDataId']);
		
		echo 'Debug in '.__FILE__.' at line '.__LINE__;
		print('<pre>');
		print_r($importData);
		print('</pre>');
	}

	/**
	 * Custom error method called automaticly when not available action is called
	 *
	 * @todo Reconsider error handling
	 * @see tx_l10nmgr_controller_import::controllPanelAction()
	 * @access public
	 * @return string
	 */
	public function errorAction () {
		return $this->routeToAction('controllPanelAction');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/controller/class.tx_l10nmgr_controller_import.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/controller/class.tx_l10nmgr_controller_import.php']);
}

?>