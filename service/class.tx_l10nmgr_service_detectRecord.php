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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This is a service who allows to generate a new
 * identity_key which is used to import records into the database.
 *
 * class.tx_l10nmgr_service_detectRecord.php
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_domain_translateable_translateableField.php $
 * @date 16.09.2009 - 10:10:00
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_service_detectRecord {
	
	/**
	 * @var int
	 */
	protected $workspaceId = 0;

	/**
	 * @static
	 * @var array
	 */
	static protected $cachedParentRecordArray = array();

	/**
	 * @param $workspaceId the $workspaceId to set
	 */
	public function setWorkspaceId($workspaceId) {
		$this->workspaceId = $workspaceId;
	}

	/**
	 * @return the $workspaceId
	 */
	public function getWorkspaceId() {
		return $this->workspaceId;
	}	
	
	/**
	 * Generate a new identity_key based on the $forcedTargetLanguageUid.
	 *
	 * With that method you can force an import to another language.
	 *
	 * @todo Find a better name for this method
	 *
	 * @param string $currentIdentityKey
	 * @param integer $forceTargetLanguageUid DEFAULT 0
	 * @param integer $localisationParentRecord DEFAULT 0
	 *
	 * @throws tx_mvc_exception_skipped
	 *
	 * @access public
	 * @return string The full identity_key
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function verifyIdentityKey($currentIdentityKey, $forceTargetLanguageUid = 0, $localisationParentRecord = 0) {
		$forceLanguageUid         = t3lib_div::intval_positive($forceTargetLanguageUid);
		$localisationParentRecord = t3lib_div::intval_positive($localisationParentRecord);
		$currentIdentityKey       = (string)$currentIdentityKey;
		$identityKey              = '';


		if ($forceLanguageUid == 0) {
			throw new tx_mvc_exception_skipped('FORCE TARGET LANGUAGE: Process skipped - No valid "forceLanguageUid" given!');
		}

			// explode the identityKey
		list ($cmdTableName, $cmdProcessString, $cmdFieldName, $cmdFieldFlexformPath) = explode(':', $currentIdentityKey);
		list ($cmdForceCreateNew, , )  = explode('/', $cmdProcessString);

		if ($cmdForceCreateNew !== 'NEW' && t3lib_div::intval_positive($cmdForceCreateNew) === 0) {
			throw new tx_mvc_exception_skipped('FORCE TARGET LANGUAGE: Process skipped - The cmdProcessingString can not handled correct. The requested identity_key: "' . var_export($currentIdentityKey, true) . '"');
		}

		$key = $this->buildIdentityKey($cmdTableName, $cmdFieldName, $cmdFieldFlexformPath, $forceLanguageUid, $localisationParentRecord);

		return $key;
	}

	/**
	 * @param string $recordTableName
	 * @param integer $recordUid
	 *
	 * @throws tx_mvc_exception_notImplemented
	 * @throws tx_mvc_exception_notSupported
	 *
	 * @access protected
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	protected function getFceTranslationModus($recordTableName, $recordUid) {
		throw tx_mvc_exception_notImplemented('This ' . __METHOD__ . ' is currently not implemented');

		require_once t3lib_extMgm::extPath('templavoila') . 'class.tx_templavoila_api.php';
		$recordArray = t3lib_BEfunc::getRecord($recordTableName, $recordUid);

		if ( is_array($recordArray) ) {
			$datastrucure = tx_templavoila_api::ds_getExpandedDataStructure ($recordTableName, $recordArray);
		}
	}
	
	/**
	 * Build a fresh identity_key wich can be used
	 * to create a new record with the TCEmain commands.
	 *
	 * The generated key looks like:
	 * - tt_content:NEW/1/540806:bodytext
	 * - pages_language_overlay:46384:title
	 *
	 * @param string $cmdTableName
	 * @param string $cmdFieldName
	 * @param string $cmdFieldFlexformPath
	 * @param integer $forceLanguageUid
	 * @param integer $localisationParentRecordUid
	 *
	 * @throws tx_mvc_exception_skipped
	 *
	 * @access protected
	 * @return string
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	protected function buildIdentityKey($cmdTableName, $cmdFieldName, $cmdFieldFlexformPath, $forceLanguageUid, $localisationParentRecordUid) {
		$translationTableName = tx_mvc_common_typo3::getTranslationTableName($cmdTableName);

		$parentRecordArray    = $this->getParentRecord($translationTableName, $localisationParentRecordUid);
		$cmdProcessingString  = $this->getProcessingString($translationTableName, $parentRecordArray['uid'], $parentRecordArray['pid'], $forceLanguageUid);

		$identityKey  = $cmdTableName;
		$identityKey .= ':' . $cmdProcessingString;
		$identityKey .= ':' . $cmdFieldName;
		$identityKey .= ( (count($cmdFieldFlexformPath) > 0)
			? ':' . $cmdFieldFlexformPath
			: ''
		);

		return $identityKey;
	}

	/**
	 * Retrieve the requested record as array,
	 * if the record is not available then an exception will thrown.
	 *
	 * @param string $tableName The table where the record could select from
	 * @param integer $recordUid The UID of desired record
	 *
	 * @throws tx_mvc_exception_skipped
	 *
	 * @access protected
	 * @return array
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	protected function getParentRecord($tableName, $recordUid) {
		$index = $tableName . ':' . $recordUid;

		if (! array_key_exists($index, self::$cachedParentRecordArray)) {
			$parentRecord = t3lib_BEfunc::getRecord($tableName, $recordUid);
			self::$cachedParentRecordArray[$index] = $parentRecord;
		}

		$parentRecord = self::$cachedParentRecordArray[$index];

		if (! is_array($parentRecord) || count($parentRecord) <= 1) {
			throw new tx_mvc_exception_skipped('FORCE TARGET LANGUAGE: Process skipped - The parent record (formally known as "l18n_parent") are not available."');
		}

		return $parentRecord;
	}

	/**
	 * Build a fresh identity_key wich can be used
	 * to create a new record with the TCEmain commands.
	 *
	 * The generated key looks like:
	 * - tt_content:NEW/1/540806:bodytext
	 * - pages_language_overlay:46384:title
	 *
	 * @deprecated
	 *
	 * @param string $cmdTableName
	 * @param string $cmdProcessingString
	 * @param string $cmdFieldName
	 * @param string $cmdFieldFlexformPath
	 *
	 * @access protected
	 * @return string
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	protected function buildIdentityKeyOrig($cmdTableName, $cmdProcessingString, $cmdFieldName, $cmdFieldFlexformPath) {

		$identityKey  =    $cmdTableName;
		$identityKey .= ':' . $cmdProcessingString;
		$identityKey .= ':' . $cmdFieldName;
		$identityKey .= ( (count($cmdFieldFlexformPath) > 0)
			? ':' . $cmdFieldFlexformPath
			: ''
		);

		return $identityKey;
	}


	
	/**
	 * Build up the cmdProcessingString.
	 *
	 * This can be for example:
	 * - NEW/1/2222
	 * - 22222
	 *
	 * @param string $translationTableName
	 * @param integer $parentRecordUid
	 * @param integer $parentRecordPid
	 * @param integer $forceLanguageUid
	 *
	 * @access protected
	 * @return string The new build cmdProcessingString
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	protected function getProcessingString($translationTableName, $parentRecordUid, $parentRecordPid, $forceLanguageUid) {
		$translationRecordArray = $this->getRecordTranslation($translationTableName, $parentRecordUid, $parentRecordPid, $forceLanguageUid);
		$cmdProcessingString    = '';
		
		if($this->getWorkspaceId() > 0){
			$translationRecordArray = t3lib_BEfunc::getWorkspaceVersionOfRecord($this->getWorkspaceId(), tx_mvc_common_typo3::getTranslationTargetTable($translationTableName), $translationRecordArray['uid']);
		}
				
		if (! is_array($translationRecordArray) || count($translationRecordArray) == 0) {
			$cmdProcessingString = 'NEW/' . $forceLanguageUid . '/' . $parentRecordUid;
		} else {
			$cmdProcessingString = $translationRecordArray['uid'];
		}

		return $cmdProcessingString;
	}

	/**
	 * Indicate that an record is ready to translate.
	 *
	 * @param string $translationTableName The table of the record to translate
	 * @param integer $parentRecordUid The UID of the parent record
	 * @param array $parentRecordArray
	 * @param integer $forceLanguageUid
	 *
	 * @access protected
	 * @return array
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	protected function getRecordTranslation($translationTableName, $parentRecordUid, $parentRecordPid, $forceLanguageUid) {
		$translationRecordArray = false;

		if ($translationTableName === 'pages') {
			if ($GLOBALS['TCA'][$translationTableName]['ctrl']['transForeignTable'] === 'pages_language_overlay') {
				$translationRecordArray = t3lib_BEfunc::getRecordsByField('pages_language_overlay', 'pid', $parentRecordUid, ' AND ' . $GLOBALS['TCA']['pages_language_overlay']['ctrl']['languageField'] . '=' . $forceLanguageUid);
			}
		} else {
			$translationRecordArray = t3lib_BEfunc::getRecordLocalization($translationTableName, $parentRecordUid, $forceLanguageUid, 'AND pid='.intval($parentRecordPid));
		}

		return (is_array($translationRecordArray) ? current($translationRecordArray) : array());
	}
}

?>