<?php
interface tx_l10nmgr_interface_progressable{

	/**
	 * Implementations should return the progress of the subject in a percentage value.
	 *
	 * @return float progress in percentage
	 */
	public function getProgressPercentage();

	/**
	 * Should return a output string for the progress bar.
	 *
	 *  @return string
	 */
	public function getProgressOutput();

	/**
	 * Should be implemented to log warning message during the progress
	 *
	 * @param string
	 */
	public function addWarningMessage($warningMessage);
}
?>