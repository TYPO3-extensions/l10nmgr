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
	 * Should be implemented to log messages during the progress
	 *
	 * @param string $type
	 * @param string $message
	 */
	public function addMessage($type,$message);
}
?>