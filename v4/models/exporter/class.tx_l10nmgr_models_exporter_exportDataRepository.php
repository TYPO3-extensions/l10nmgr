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

require_once t3lib_extMgm::extPath('l10nmgr').'models/exporter/class.tx_l10nmgr_models_exporter_exportData.php';


class tx_l10nmgr_models_exporter_exportDataRepository extends tx_mvc_ddd_typo3_abstractTCAObjectRepository {

	/**
	 * @var string The name of the objectclass for that this repository s responsible
	 */
	protected $objectClassName = 'tx_l10nmgr_models_exporter_exportData';

	/**
	 * 
	 */
	protected function findByWhere($where,$groupby=false,$orderby=false,$limit=false,$add_enable_fields=true) {
		$queryParts = array ();

		$select = '*';
		$from   = $this->tableName;

		$queryParts = tx_mvc_system_dbtools::buildQueryPartsArray (
			$select,
			$from,
			$where,
			$groupby,
			$orderby,
			$limit
		);

		if ($add_enable_fields) {
			$queryParts ['WHERE'] .= $this->getEnableFieldsWhere();
		}

		$res = $this->getDatabase ()->exec_SELECT_queryArray($queryParts);

		return $this->getCollectionFromRs($res);
	}
	/**
	 * Returns a exportData objects with this stage in the history
	 *
	 * @param ArrayObject $state
	 * @return ArrayObject
	 */
	public function findAllWithStateInHistory($state,$add_enable_fields = true) {
		//SELECT * FROM tx_l10nmgr_workflowstates s1 WHERE tstamp = ( SELECT MAX( s2.tstamp ) FROM `tx_l10nmgr_workflowstates` s2 WHERE s1.exportdata_id = s2.exportdata_id )

		$where  = 'uid IN( SELECT DISTINCT exportdata_id FROM tx_l10nmgr_workflowstates WHERE state ='.tx_mvc_common_typo3::fullQuoteString($state).')';
		return $this->findByWhere($where,false,false,false,$add_enable_fields);
	}

	/**
	 * Returns all exportData without this state in history
	 *
	 * @param string $state
	 * @param boolean $add_enable_fields
	 * @return ArrayObject
	 */
	public function findAllWithoutStateInHistory($state,$add_enable_fields = true) {
		$where  = 'uid NOT IN( SELECT DISTINCT exportdata_id FROM tx_l10nmgr_workflowstates WHERE state ='.tx_mvc_common_typo3::fullQuoteString($state).')';
		return $this->findByWhere($where,false,false,false,$add_enable_fields);
	}

	/**
	 * This method is used to find all exportData objects withour a given state in history.
	 * In addition they need to have a given configurationId and a targetLanguageId.
	 * 
	 * @param string $state the workflow state.
	 * @param int $configurationId uid of the configuration record.
	 * @param int $targetLanguageId uid of the target language record.
	 *
	 */
	public function findAllWithoutStateInHistoryByAssigendConfigurationAndTargetLanguage($state, $configurationId, $targetLanguageId, $add_enable_fields = true) {

		$where  = 	'uid NOT IN( SELECT DISTINCT exportdata_id FROM tx_l10nmgr_workflowstates WHERE state ='.tx_mvc_common_typo3::fullQuoteString($state).') '.
					' AND l10ncfg_id = '.intval($configurationId).
					' AND translation_lang = '.intval($targetLanguageId);

		return $this->findByWhere($where, false, false, false, $add_enable_fields);
	}
}

?>