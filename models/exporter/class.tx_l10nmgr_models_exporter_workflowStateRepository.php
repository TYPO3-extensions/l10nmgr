<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Fabrizio Branca (fabrizio.branca@aoemedia.de)
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

require_once t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_workflowState.php';


class tx_l10nmgr_models_exporter_workflowStateRepository extends tx_mvc_ddd_typo3_abstractTCAObjectRepository {

	/**
	 * @var string The name of the objectclass for that this repository is responsible
	 */
	protected $objectClassName = 'tx_l10nmgr_models_exporter_workflowState';

	public function findAllWhereLatestState($state,$add_enable_fields = true){
		//SELECT * FROM tx_l10nmgr_workflowstates s1 WHERE tstamp = ( SELECT MAX( s2.tstamp ) FROM `tx_l10nmgr_workflowstates` s2 WHERE s1.exportdata_id = s2.exportdata_id )
		$queryParts = array ();

		$select = '*';
		$from   = $this->tableName.' s1';
		$where  = 	'tstamp=(SELECT MAX(s2.tstamp)
						FROM `tx_l10nmgr_workflowstates` s2
						WHERE s1.exportdata_id = s2.exportdata_id) '.
					' AND state ='.tx_mvc_common_typo3::fullQuoteString($state);

		$queryParts = tx_mvc_system_dbtools::buildQueryPartsArray (
			$select,
			$from,
			$where
		);

		if ($add_enable_fields) {
			$queryParts ['WHERE'] .= $this->getEnableFieldsWhere();
		}

		$res = $this->getDatabase ()->exec_SELECT_queryArray($queryParts);

		return $this->getCollectionFromRs($res);
	}

	public function findByExportdDataNewestFirst($exportdata_id) {
		return $this->findBy('exportdata_id', $exportdata_id, true, 'tstamp desc');
	}



}
?>