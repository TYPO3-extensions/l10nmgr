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
 * This controller can be extended to realize controllers which need
 * to progress a task with multiple calls to an ajax url.
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
	 * @var string initial progress label
	 */
	protected $initalProgressLabel = 'Initializing...';

	/**
	 * Sets the initial progress label
	 *
	 * @param string initial progress label
	 * @return void
	 */
	protected function setInitialProgressLabel($initalProgressLabel) {
		$this->initalProgressLabel = $initalProgressLabel;
	}

	/**
	 * Holds the received warning messages during the import.
	 *
	 * @var array
	 */
	public static $warningMessages;

	/**
	 * (non-PHPdoc)
	 * @see mvc/controller/tx_mvc_controller_action#initializeArguments()
	 */
	protected function initializeArguments(){
		if(!isset($this->arguments['warningCount'] )){
			$this->arguments['warningCount'] = 0;
		}
		parent::initializeArguments();
	}

	/**
	 * Show progress action
	 *
	 * @param void
	 * @return void
	 */
	public function showProgressAction() {
		//this view is used in both controllers
		$this->view = new tx_l10nmgr_view_showProgress();
		$this->initializeView($this->view);

		$progressView = new tx_mvc_view_widget_progress();
		$this->initializeView($progressView );
		$progressView->setProgress(0);
		$progressView->setProgressLabel($this->initalProgressLabel); // TODO: move to locallang
		$progressView->setAjaxEnabled(true);
		$progressView->setProgressUrl($this->getViewHelper('tx_mvc_viewHelper_linkCreator')->getAjaxActionLink('ajaxPerformRun')->useOverruledParameters()->makeUrl());
		$progressView->setRedirectOnCompletedUrl($this->getRedirectUrlOnCompletion());
		$progressView->setRedirectOnAbortUrl($this->getRedirectUrlOnAbort());

		$this->view->setProgressableSubjectView($this->getProgressableSubjectView());
		$this->view->setProgressView($progressView);
		$this->view->addBackendStylesHeaderData();
	}

	/**
	 * Get progressable subject view
	 *
	 * @param void
	 * @return
	 */
	abstract protected function getProgressableSubjectView();

	/**
	 * This method is used to return the redirect url on completion of the export process.
	 * overwrite it to change it in a sub-controller.
	 *
	 * @author Timo Schmidt <schmidt@aoemedia.de>
 	 * @return string
	 */
	protected function getRedirectUrlOnCompletion() {
		return '../mod1/index.php';
	}

	/**

	 * @author Timo Schmidt <schmidt@aoemedia.de>
 	 * @return string
	 */
	protected function getRedirectUrlOnAbort() {
		return '../mod1/index.php';
	}

	/**
	 * Perform run action (via AJAX call)
	 *
	 * @author Timo Schmidt
	 * @param void
	 * @return void
	 */
	public function ajaxPerformRunAction() {
		$progressView = new tx_mvc_view_widget_progressAjax();
		$this->initializeView($progressView);

		try {
			tx_mvc_validator_factory::getIntValidator()->isValid($this->arguments['warningCount'],true);
			$subject = $this->getProgressableSubject();

			/**
			 * To handle errors during the import process we setup an error handing for user warnings.
			 * In addition any output will cause a user warning, to get any warning output into the
			 * progress bar.
			 *
			 * To trigger own errors use trigger_error('your message',E_USER_WARNING)
			 */
			set_error_handler(array(get_class($this),'warningHandler'), E_WARNING | E_USER_WARNING);
				ob_start();
					$completed = $this->performProgressableRun($subject);
					$output = ob_get_contents();
				ob_end_clean();

				if(!empty($output)){ trigger_error('Output during process: '.$output,E_USER_WARNING);}
			restore_error_handler();

			$percent = $subject->getProgressPercentage();
			$progressView->setProgress($percent);

			if(is_array(self::$warningMessages)) {
				$warningMessage = implode('<br/><br/>',self::$warningMessages);
				$progressView->setWarningMessage($warningMessage);
				$this->arguments['warningCount']++;
			}

			if ($completed) {
				$progressView->setProgressLabel('Completed');
				$progressView->setCompleted(true);
				if($this->arguments['warningCount'] > 0){
					$progressView->setCompleteMessage('Task has been finished with '.$this->arguments['warningCount'].' warnings. Click "Ok" to be redirected to the overview.');
				}
			} else {
				$progressView->setProgressLabel($subject->getProgressOutput());
			}

		}catch(Exception $e) {
			tx_mvc_common_debug::logException($e);
			$progressView->setAborted(true);

			$message = $e->getMessage()."\n\n";
			if($this->configuration->get('show_debugging_information') == 1){
				$message .= 'File: '.$e->getFile()."\n\n".
							'Line: '.$e->getLine()."\n\n".
							'Trace: '.$e->getTraceAsString();
			}

			$progressView->setAbortMessage($message);
		}

		//update the progress url, maybe the number of warnings has changed
		$progressView->setProgressUrl($this->getViewHelper('tx_mvc_viewHelper_linkCreator')->getAjaxActionLink('ajaxPerformRun')->useOverruledParameters()->makeUrl());

		echo $progressView->render();
		exit();
	}

	/**
	* Custom error handler writes error to the dev log and adds error messages to an
	* internal error message array.
	*
	* @author Timo Schmidt <timo.schmidt@aoemedia.de>
	* @param int error code
 	* @param string error description
	* @param string filename
	* @param int line of code
	*/
	public function warningHandler($errno,$errstr,$file,$line) {
		$message = 'Warning: '.$errstr."\n\n";

		if($this->configuration->get('show_debugging_information') == 1){
			$message .= 'Error: '.$errno."\n\n".
						'File: '.$file."\n\n".
						'Line: '.$line."\n\n".
						'Debug Backtrace: '.var_export(debug_backtrace(),true);
		}
		self::$warningMessages[] = $message;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extensionkey/path/class.tx_l10nmgr_controller_abstractProgressable.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extensionkey/path/class.tx_l10nmgr_controller_abstractProgressable.php']);
}
?>