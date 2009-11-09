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
 * Factory to build the translation object
 *
 * class.tx_l10nmgr_domain_translationFactory.php
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 11:39:25
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_translationFactory {

	/**
	 * @var tx_l10nmgr_domain_translation_data
	 */
	protected $TranslationData = null;

	/**
	 * Build a translation data object from given XML data structure
	 *
	 * @param string $fullQualifiedFileName
	 * @param integer $forceTargetLanguageUid OPTIONAL If is set the targetLanguageUid will be overwritten with the forced languageUid
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_data
	 */
	public function createFromXMLFile($fullQualifiedFileName, $forceTargetLanguageUid = 0) {

		if (! tx_mvc_validator_factory::getFileValidator()->isValid($fullQualifiedFileName)) {
			throw new tx_mvc_exception_fileNotFound('The given filename: "' . var_export($fullQualifiedFileName, true) . '" not found!');
		}

		$TranslationXML = simplexml_load_file($fullQualifiedFileName, 'SimpleXMLElement', LIBXML_NOCDATA ^ LIBXML_NOERROR ^ LIBXML_NONET ^ LIBXML_XINCLUDE ^ LIBXML_NOEMPTYTAG);
		if (! $TranslationXML instanceof SimpleXMLElement ) {
			throw new tx_mvc_exception_invalidContent('The file : "' . (string)$fullQualifiedFileName . '" contains no valid XML structure!');
		}

		$this->TranslationData = new tx_l10nmgr_domain_translation_data();

			// force the target sys_language_uid
		$this->TranslationData->setForceTargetLanguageUid($forceTargetLanguageUid);

		$this->extractXMLMetaData($TranslationXML->head);
		$this->extractXMLTranslation($TranslationXML->pageGrp);
		unset($TranslationXML);
		return $this->TranslationData;
	}

	/**
	 * Extract the page data from the XML import file into the tx_l10nmgr_domain_translation_pageCollection object
	 *
	 * @param SimpleXMLElement $Page
	 * @access private
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	private function extractXMLTranslation(SimpleXMLElement $Pagerows) {
		$PageCollection = new tx_l10nmgr_domain_translation_pageCollection();
		$TextConverter = new tx_l10nmgr_service_textConverter(); /* @var $test tx_l10nmgr_xmltools */

		foreach ($Pagerows as $pagerow) {
			$Page = new tx_l10nmgr_domain_translation_page();
			$Page->setUid((int)$pagerow['id']);

				// Each page has one element collection
			$ElementCollection = new tx_l10nmgr_domain_translation_elementCollection();

			foreach ($pagerow->children() as $field) {
				$table = (string)$field['table'];
				$uid   = (int)$field['elementUid'];
				$Field = new tx_l10nmgr_domain_translation_field();
				$Field->setFieldPath((string)$field['key']);
				$needsAutoDetection = !$Field->detectTransformationType($field,$this->TranslationData->getFormatVersion());

				switch($Field->getTransformationType($uid,$needsAutoDetection)) {
					case 'html':
							$Field->setContent($TextConverter->getXMLContent($field));
						break;
					case 'text':
							$Field->setTransformation(true);
							$Field->setContent($TextConverter->toText($TextConverter->getXMLContent($field,true)));
						break;
					default:
							$Field->setContent($TextConverter->toText($TextConverter->getXMLContent($field), false, false));
				}

				$Element = $this->createOrGetElementFromElementCollection($ElementCollection, $table, $uid);
				$Element->getFieldCollection()->offsetSet((string)$field['key'], $Field);
			}

			$Page->setElementCollection($ElementCollection);
			$PageCollection->offsetSet((int)$pagerow['id'], $Page);
		}

		$this->TranslationData->setPageCollection($PageCollection);
	}

	/**
	 * If the Element for the current table and uid combination not exists a new instance
	 * of the tx_l10nmgr_domain_translation_element will be create.
	 *
	 * @param string $table
	 * @param int $uid
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_element
	 */
	protected function createOrGetElementFromElementCollection($ElementCollection,$table,$uid){

		if ( $ElementCollection->offsetExists($table . ':' . $uid) ) {

			$Element = $ElementCollection->offsetGet($table . ':' . $uid);
		} else {

			$Element = new tx_l10nmgr_domain_translation_element();
			$Element->setTableName($table);
			$Element->setUid($uid);

			$ElementCollection->offsetSet($table . ':' . $uid, $Element);

			$FieldCollection = new tx_l10nmgr_domain_translation_fieldCollection();
			$Element->setFieldCollection($FieldCollection);
		}

		return $Element;
	}

	/**
	 * Extract the meta information of the import XML file into the tx_l10nmgr_domain_translation_data object
	 *
	 * @param SimpleXMLElement $Head
	 * @access private
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	private function extractXMLMetaData(SimpleXMLElement $Head) {

		foreach ($Head as $metaData) {
			$this->TranslationData->setL10ncfgUid((int)$metaData->t3_l10ncfg);
			$this->TranslationData->setTargetSysLanguageUid((int)$metaData->t3_sysLang);
			$this->TranslationData->setTargetLanguageIsoCode((string)$metaData->t3_targetLang);
			$this->TranslationData->setSourceLanguageISOcode((string)$metaData->t3_sourceLang);
			$this->TranslationData->setBaseUrl((string)$metaData->baseURL);
			$this->TranslationData->setWorkspaceId((int)$metaData->t3_workspaceId);
			$this->TranslationData->setFieldCount((int)$metaData->t3_count);
			$this->TranslationData->setWordCount((int)$metaData->t3_wordCount);
			$this->TranslationData->setFormatVersion((float)$metaData->t3_formatVersion);
			$this->TranslationData->setExportDataRecordUid((int)$metaData->t3_exportDataId);

			foreach ($metaData->t3_internal as $messageIndes => $message) {
				//!TODO redefine the message point (alias "t3_internal")
//				$this->TranslationData->setMessages();
			}
		}
	}

	/**
	 * This method is used to create a translationData from a form submit of the backend side by side translation.
	 *
	 * @param $array
	 * @return tx_l10nmgr_domain_translation_data
	 */
	public function createFromFormSubmit($pageid,$targetLanguageUid,$fields,tx_l10nmgr_domain_configuration_configuration $l10nConfiguration){
		global $BE_USER;

		$TranslationData = new tx_l10nmgr_domain_translation_data();
		$TranslationData->setL10ncfgUid((int)$l10nConfiguration->getUid());
		$TranslationData->setWorkspaceId($BE_USER->user['workspace_id']);
		$TranslationData->setTargetSysLanguageUid((int)$targetLanguageUid);

		$PageCollection = new tx_l10nmgr_domain_translation_pageCollection();

		$Page = new tx_l10nmgr_domain_translation_page();
		$Page->setUid($pageid);

		$TextConverter = new tx_l10nmgr_service_textConverter(); /* @var $test tx_l10nmgr_xmltools */
		$ElementCollection = new tx_l10nmgr_domain_translation_elementCollection();

		$fieldCount = 0;

		if(is_array($fields)){
			foreach($fields as $table => $data){
				foreach($data as $uid => $fields){
					foreach($fields as $identityKey => $translationValue){
						$fieldCount++;

						$Field = new tx_l10nmgr_domain_translation_field();
						$Field->setFieldPath($identityKey);
						$Field->setContent($TextConverter->toText($translationValue));

						$Element = $this->createOrGetElementFromElementCollection($ElementCollection, $table, $uid);
						$Element->getFieldCollection()->offsetSet($identityKey, $Field);
					}
				}
			}
		}

		$Page->setElementCollection($ElementCollection);
		$PageCollection->offsetSet($pageid, $Page);

		$TranslationData->setFieldCount($fieldCount);
		$TranslationData->setPageCollection($PageCollection);
		$TranslationData->setForceTargetLanguageUid((int)$targetLanguageUid);

		return $TranslationData;
	}


	/**
	 * This method is used to create a TranslationData from an excel xml file
	 *
	 * @param string filename to the excel xml file.
	 * @param int forced target language uid.
	 *
	 * @return tx_l10nmgr_domain_translation_data
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 */
	public function createFromExcelFile($fullQualifiedFileName, $forceTargetLanguageUid = 0){
		global $BE_USER;

		if (! tx_mvc_validator_factory::getFileValidator()->isValid($fullQualifiedFileName)) {
			throw new tx_mvc_exception_fileNotFound('The given filename: "' . var_export($fullQualifiedFileName, true) . '" not found!');
		}

		// Parse XML in a rude fashion:
		// Check if &nbsp; has to be substituted -> DOCTYPE -> entity?
		$fileContent = file_get_contents($fullQualifiedFileName);
		$xmlNodes = t3lib_div::xml2tree(str_replace('&nbsp;',' ',$fileContent));	// For some reason PHP chokes on incoming &nbsp; in XML!

		if (!is_array($xmlNodes)) {
			throw new tx_mvc_exception_invalidArgument('The given filename: "' . var_export($fullQualifiedFileName, true) . '" is no valid XML!');
		}

		$this->TranslationData = new tx_l10nmgr_domain_translation_data();
		$this->TranslationData->setForceTargetLanguageUid($forceTargetLanguageUid);

		$TextConverter = new tx_l10nmgr_service_textConverter(); /* @var $test tx_l10nmgr_xmltools */
		$PageCollection = new tx_l10nmgr_domain_translation_pageCollection();

		$metaDataNode = $xmlNodes['Workbook'][0]['ch']['DocumentProperties'][0]['ch']['Author'][0]['values'][0];
		$this->extractExcelMetaData($metaDataNode);

			// At least OpenOfficeOrg Calc changes the worksheet identifier. For now we better check for this, otherwise we cannot import translations edited with OpenOfficeOrg Calc.
		if ( isset( $xmlNodes['Workbook'][0]['ch']['Worksheet'] ) ) {
			$worksheetIdentifier = 'Worksheet';
		}
		if ( isset( $xmlNodes['Workbook'][0]['ch']['ss:Worksheet'] ) ) {
			$worksheetIdentifier = 'ss:Worksheet';
		}

		$fieldCount = 0;

			// OK, this method of parsing the XML really sucks, but it was 4:04 in the night and ... I have no clue to make it better on PHP4. Anyway, this will work for now. But is probably unstable in case a user puts formatting in the content of the translation! (since only the first CData chunk will be found!)
		if (is_array($xmlNodes['Workbook'][0]['ch'][$worksheetIdentifier][0]['ch']['Table'][0]['ch']['Row']))	{
			foreach($xmlNodes['Workbook'][0]['ch'][$worksheetIdentifier][0]['ch']['Table'][0]['ch']['Row'] as $row)	{

				if($row['ch']['Cell'][0]['attrs']['ss:Index'] == '2'){
					$pageRow = $row['ch']['Cell'][0]['ch']['Data'][0]['values'][0];
					$open	 = strpos($pageRow,'[Uid:');
					$close	 = strpos($pageRow,']');
					$length	 = $close - $open;

					if($open > 0 && $close > 0){
						$pageId  = (int)substr($pageRow,$open+5,$length);

						if($pageId > 0){
							$ElementCollection = new tx_l10nmgr_domain_translation_elementCollection();

							$Page = new tx_l10nmgr_domain_translation_page();
							$Page->setElementCollection($ElementCollection);

							$Page->setUid($pageId);
							$PageCollection->offsetSet($pageId, $Page);
						}
					}
				}elseif(!isset($row['ch']['Cell'][0]['attrs']['ss:Index']))	{
					list($table, $uid, $identityKey) = explode('][',substr(trim($row['ch']['Cell'][0]['ch']['Data'][0]['values'][0]),12,-1));

					$translationValue = $row['ch']['Cell'][3]['ch']['Data'][0]['values'][0];

					$Field = new tx_l10nmgr_domain_translation_field();
					$Field->setFieldPath($identityKey);

					switch($Field->getTransformationType($uid,true)) {
						case 'html':
								$Field->setContent($translationValue);
							break;
						case 'text':
								$Field->setTransformation(true);
								$Field->setContent($TextConverter->toText($translationValue));
							break;
						default:
								$Field->setContent($TextConverter->toText($translationValue));
					}

					if($ElementCollection instanceof tx_l10nmgr_domain_translation_elementCollection){
						$Element = $this->createOrGetElementFromElementCollection($ElementCollection, $table, $uid);
					}else{
						throw new tx_mvc_exception_invalidArgument('Invalid excel file structure. No page definition before content rows.');
					}

					$Element->getFieldCollection()->offsetSet($identityKey, $Field);
					$fieldCount++;
				}
			}
		}

		$this->TranslationData->setPageCollection($PageCollection);
		$this->TranslationData->setFieldCount($fieldCount);

		return $this->TranslationData;
	}

	/**
	 * This method is used to extract the meta data from an excel export file.
	 *
	 * @param $metaDataNode string content of the metaData node
	 * @return void
	 */
	protected function extractExcelMetaData($metaDataNode){
		$noteArray = explode('|',$metaDataNode);

		if(is_array($noteArray)){
			$notes = array();
			foreach($noteArray as $note){

				$keyValue = explode(':',$note);

				if(is_array($keyValue)){
					$notes[$keyValue[0]] = $keyValue[1];
				}
			}

			$this->TranslationData->setWorkspaceId($BE_USER->user['workspace_id']);
			$this->TranslationData->setExportDataRecordUid((int)$notes['ExportDataUid']);
			$this->TranslationData->setTargetSysLanguageUid((int)$notes['TargetLanguageUid']);
			$this->TranslationData->setFormatVersion($notes['FormatVersion']);
			$this->TranslationData->setL10ncfgUid((int)$notes['ConfigurationUid']);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translationFactory.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translationFactory.php']);
}

?>