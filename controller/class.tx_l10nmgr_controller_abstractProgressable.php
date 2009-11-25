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
 * @subpackage l10nmgr
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
	 *
	 * @var boolean
	 */
	protected static $showDebuggingInfo;

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
	public static $messages;

	/**
	 * (non-PHPdoc)
	 * @see mvc/controller/tx_mvc_controller_action#initializeArguments()
	 */
	protected function initializeArguments(){
		if(!isset($this->arguments['messageCount'] )){
			$this->arguments['messageCount'] = 0;
		}

		//we need to set this here because we can not read the information
		//in the warning handler
		if($this->configuration->get('show_debugging_information') == 1){
			self::$showDebuggingInfo = true;
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
	 * Returns the progressable subject.
	 *
	 * @param void
	 * @return tx_l10nmgr_interface_progressable
	 *
	 */
	abstract protected function getProgressableSubject();

	/**
	 * The implementation of this method is responsible to
	 * save the subject for the next run.
	 *
	 * @param tx_l10nmgr_interface_progressable
	 * @return void
	 */
	abstract protected function saveProgressableSubject(tx_l10nmgr_interface_progressable $subject);

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
	 *
	 * @author Timo Schmidt <schmidt@aoemedia.de>
 	 * @return string
	 */
	protected function getRedirectUrlOnAbort() {
		return '../mod1/index.php';
	}

	/**
	 * This method is used to call the performProgressableRun method. Usually this
	 * method is overwritten by the import or export controller.
	 *
	 * @author Timo Schmidt
	 * @param tx_l10nmgr_interface_progressable
	 * @return boolean
	 */
	private function performProgressableRunOnSubject($subject){
		/**
		 * To handle errors during the import process we setup an error handing for user warnings.
		 * In addition any output will cause a user warning, to get any warning output into the
		 * progress bar.
		 *
		 * To trigger own errors use trigger_error('your message',E_USER_WARNING)
		 */
		set_error_handler(array(get_class($this),'errorHandler'), E_WARNING | E_USER_WARNING | E_USER_NOTICE);
			ob_start();
				$completed = $this->performProgressableRun($subject);
				$output = ob_get_contents();
			ob_end_clean();
			//if there is any output during the progress we trigger our own user warning
			if(!empty($output)){ trigger_error('Output during process: '.$output,E_USER_WARNING);}
		restore_error_handler();

		return $completed;
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
			tx_mvc_validator_factory::getIntValidator()->isValid($this->arguments['messageCount'],true);
			$subject = $this->getProgressableSubject();
			$completed = $this->performProgressableRunOnSubject($subject);

			$percent = $subject->getProgressPercentage();
			$progressView->setProgress($percent);

			if(is_array(self::$messages)) {
				foreach(self::$messages as $type => $items){
					$message = implode('<br/><br/>',$items);

					//add the warning or notice to the progress bar
					if($type == E_USER_WARNING || $type == E_WARNING){
						$progressView->setWarningMessage($message);
					}elseif($type == E_USER_NOTICE){
						$progressView->setNoticeMessage($message);
					}

					//attach the message to the subject
					$subject->addMessage($type,$message);
					$this->arguments['messageCount']++;
				}
			}

			$this->saveProgressableSubject($subject);

			if ($completed) {
				$progressView->setProgressLabel('Completed');
				$progressView->setCompleted(true);
				if($this->arguments['messageCount'] > 0){
					$progressView->setCompleteMessage('Task has been finished with '.$this->arguments['messageCount'].' messages. Click "Ok" to be redirected to the overview.');
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
	public function errorHandler($errno,$errstr,$file,$line) {
		$message = 'Message: '.$errstr."\n\n";

		if(self::$showDebuggingInfo == 1){
			$message .= 'Error: '.$errno."\n\n".
						'File: '.$file."\n\n".
						'Line: '.$line."\n\n";
					//	'Debug Backtrace: '.var_export(debug_backtrace(),true);
		}
		self::$messages[$errno][] = $message;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extensionkey/path/class.tx_l10nmgr_controller_abstractProgressable.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extensionkey/path/class.tx_l10nmgr_controller_abstractProgressable.php']);
}
?>