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

require_once(t3lib_extMgm::extPath('l10nmgr').'controller/class.tx_l10nmgr_controller_abstractProgressable.php');


require_once(t3lib_extMgm::extPath('l10nmgr').'models/importer/class.tx_l10nmgr_models_importer_importer.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/importer/class.tx_l10nmgr_models_importer_importData.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/importer/class.tx_l10nmgr_models_importer_importDataRepository.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'view/import/class.tx_l10nmgr_view_importer_detail.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'view/class.tx_l10nmgr_view_showProgress.php');

require_once(t3lib_extMgm::extPath('mvc').'mvc/view/widget/class.tx_mvc_view_widget_progress.php');
require_once(t3lib_extMgm::extPath('mvc').'mvc/view/widget/class.tx_mvc_view_widget_progressAjax.php');

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
class tx_l10nmgr_controller_import extends tx_l10nmgr_controller_abstractProgressable {

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
	 * @var array mapping external parameters to arguments
	 */
	protected $mapParametersToArguments = array(
		'createdRecord' => 'returnEditConf',
	);

	/**
	 * These arguments should be kept since they are needed in the ajax polling action
	 *
	 * @var array
	 */
	protected $keepArgumentKeys = array('importDataId');

	/**
	 * Show the controll panel to give the user the options what he can do
	 *
	 * @access public
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return string HTML formated output
	 */
	public function generateImportAction() {
		//retrieve importdata record
		$importDataId = tx_mvc_common_typo3::parseReturnEditConf($this->arguments['createdRecord'],'tx_l10nmgr_importdata');

		if(!empty($importDataId)){
			$this->arguments['importDataId'] = $importDataId;
		}

		tx_mvc_validator_factory::getIntValidator()->isValid($this->arguments['importDataId'],true);

		$importDataRepository = new tx_l10nmgr_models_importer_importDataRepository();
		$importData = $importDataRepository->findById($this->arguments['importDataId']);

		/* Ensure, that all files are unzipped */
		$importData->extractAllZipContent();

		$this->routeToAction('showProgressAction');
	}


	/**
	 * This method returns the detail view for the importData that is currently processed-
	 *
	 * @param void
	 * @return
	 *
	 * @author Timo Schmidt
	 */
	protected function getProgressableSubjectView(){
		$view = new tx_l10nmgr_view_importer_detail();
		$this->initializeView($view);
		$view->setImportData($this->getProgressableSubject());

		return $view;
	}

	/**
	 * Returns the importData which should be progressed in the ajax function.
	 *
	 * @author Timo Schmidt
	 * @return tx_l10nmgr_interface_progressable
	 */
	protected function getProgressableSubject(){
		tx_mvc_validator_factory::getIntValidator()->isValid($this->arguments['importDataId'],true);

		$importDataRepository 	= new tx_l10nmgr_models_importer_importDataRepository();
		$importData 			= $importDataRepository->findById($this->arguments['importDataId']);

		return $importData;
	}

	/**
	 * Worker method called by ajaxPerformRunAction.
	 *
	 * @see ajaxPerformRunAction
	 */
	protected function performProgressableRun($importData){
		tx_l10nmgr_models_importer_importer::performImportRun($importData);

	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/controller/class.tx_l10nmgr_controller_import.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/controller/class.tx_l10nmgr_controller_import.php']);
}

?>