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
 * class.tx_l10nmgr_controller_abstractProgressable.php
 *
 * @author Timo Schmidt <schmidt@aoemedia.de>
 * @controller controller
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_controller_abstractProgressable.php $
 * @date 04.05.2009 15:29:14
 * @see tx_mvc_view_widget_phpTemplateListView
 * @category database
 * @package TYPO3
 * @subpackage extensionkey
 * @access public
 */
abstract class tx_l10nmgr_controller_abstractProgressable extends tx_mvc_controller_action {
	/**
	 * @var string
	 */
	protected $extensionKey = 'l10nmgr';


	/**
	 * @var string
	 */
	protected $argumentsNamespace = 'l10nmgr';

	/**
	 * 
	 */
	public function showProgressAction(){
		//this view is used in both controllers 
		$this->view = new tx_l10nmgr_view_showProgress();
		$this->initializeView($this->view);
				
		$progressView = new tx_mvc_view_widget_progress();
		$this->initializeView($progressView );
		$progressView->setProgress(0);
		$progressView->setProgressLabel('Preparing export...'); // TODO: move to locallang
		$progressView->setAjaxEnabled(true);
		$progressView->setProgressUrl($this->getViewHelper('tx_mvc_viewHelper_linkCreator')->getAjaxActionLink('ajaxPerformRun')->useOverruledParameters()->makeUrl());
		$progressView->setRedirectOnCompletedUrl('../mod1/index.php');

		$this->view->setProgressableSubjectView($this->getProgressableSubjectView());
		$this->view->setProgressView($progressView);
		$this->view->addBackendStylesHeaderData();

	}
	
	
	public function ajaxPerformRunAction(){
		
		$subject = $this->getProgressableSubject();
		$this->performProgressableRun($subject);

		$progressView = new tx_mvc_view_widget_progressAjax();
		$this->initializeView($progressView);
		$percent = $subject->getProgressPercentage();
		$progressView->setProgress($percent);
		
		if ($percent < 100) {
			$progressView->setProgressLabel(round($subject->getProgressPercentage()). ' %');
		} else {
			$progressView->setProgressLabel('Completed');
			$progressView->setCompleted(true);
		}

		echo $progressView->render();

		exit();
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extensionkey/path/class.tx_l10nmgr_controller_abstractProgressable.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extensionkey/path/class.tx_l10nmgr_controller_abstractProgressable.php']);
}
?>