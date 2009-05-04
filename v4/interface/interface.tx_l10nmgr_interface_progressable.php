<?php
interface tx_l10nmgr_interface_progressable{
	
	/**
	 * Implementations should return the progress of the subject in a percentage value.
	 * 
	 * @return float progress in percentage
	 */
	public function getProgressPercentage();
}
?>