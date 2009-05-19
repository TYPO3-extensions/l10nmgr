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

/**
 * documenation
 *
 * {@inheritdoc}
 *
 * class.tx_l10nmgr_view_list_outputErrorMessage.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @controller controller
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_view_list_outputErrorMessage.php $
 * @date 12.05.2009 16:06:59
 * @see tx_mvc_view_widget_phpTemplateListView
 * @category database
 * @package TYPO3
 * @subpackage l10nmgr
 * @access public
 */
class tx_l10nmgr_view_list_outputErrorMessage extends tx_mvc_view_backendModule {

	/**
	 * The default template is used if o template is set
	 *
	 * @var        string
	 */
	protected $defaultTemplate = 'EXT:l10nmgr/templates/list/error.php';

	/**
	 *
	 * @access public
	 * @return void
	 */
	public function preRenderProcessing() {
	}

	/**
	 * This method is used to set an error message for the error view.
	 *
	 * @author Timo Schmidt
	 * @param string
	 * @return void
	 */
	public function setErrorMessage($message){
		$this->errorMessage = $message;
	}

	/**
	 * Returns the configured errorMessage
	 *
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return string
	 */
	protected function getErrorMessage(){
		return $this->errorMessage;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/view/list/class.tx_l10nmgr_view_list_outputErrorMessageAction.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/view/list/class.tx_l10nmgr_view_list_outputErrorMessageAction.php']);
}
?>