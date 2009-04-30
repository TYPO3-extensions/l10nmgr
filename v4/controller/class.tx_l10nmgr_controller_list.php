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


require_once(t3lib_extMgm::extPath('l10nmgr').'view/list/class.tx_l10nmgr_view_list_showConfigurations.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/configuration/class.tx_l10nmgr_models_configuration_configuration.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/configuration/class.tx_l10nmgr_models_configuration_configurationRepository.php');
require_once(t3lib_extMgm::extPath('mvc').'mvc/view/widget/class.tx_mvc_view_widget_pagination.php');

class tx_l10nmgr_controller_list extends tx_mvc_controller_action {

	/**
	 * @var string
	 */
	protected $extensionKey = 'l10nmgr';

	/**
	 * @var string
	 */
	protected $defaultActionMethodName = 'showConfigurationsAction';

	/**
	 * @var string
	 */
	protected $argumentsNamespace = 'l10nmgr';


	public function showConfigurationsAction() {

		$configurationsRepository = new tx_l10nmgr_models_configuration_configurationRepository();

		$paginationSubView = new tx_mvc_view_widget_pagination();
		$this->initializeView($paginationSubView);

		$paginationSubView->setCount($configurationsRepository->countAll());
		$paginationSubView->setItemsPerPage(10);
		$paginationSubView->setCurrentOffset($this->arguments['offset']);
		$paginationSubView->setShowPages(5);

		$this->view->pagination = $paginationSubView;

		$configurationsRepository = new tx_l10nmgr_models_configuration_configurationRepository();
		$this->view->configurations = $configurationsRepository->findAll(true, 'crdate DESC', false, 10, $this->arguments['offset']);
		$this->view->addBackendStylesHeaderData();

	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/controller/class.tx_l10nmgr_controller_import.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/controller/class.tx_l10nmgr_controller_import.php']);
}

?>