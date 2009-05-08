<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Kasper Skårhøj <kasperYYYY@typo3.com>
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

require_once(t3lib_extMgm::extPath('l10nmgr').'views/class.tx_l10nmgr_abstractExportView.php');

/**
 * excelXML: Renders the excel XML
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_excelXMLView extends tx_l10nmgr_abstractExportView{

	/**
	 * The default template of the view
	 * @var sting
	 */
	protected $defaultTemplate = 'EXT:l10nmgr/templates/excelXML/excelxml.php';

	/**
	 * @var string
	 */
	protected $export_type = 'xls';


	/**
	 * Holds the number of rows of the excel export
	 *
	 * @var int
	 */
	protected $rowCount;

	/**
	 * Implementation of abstract method
	 *
	 * @return string
	 */
	protected function getExporttypePrefix(){
		return 'excel_export';
	}

	/**
	 * Builds the pageGroup for the view. Implementation of the abstract method.
	 */
	protected function renderPageGroups(){
		global $LANG;
		$translateableInformation = $this->getTranslateableInformation();

		foreach($translateableInformation->getPageGroups() as $pageGroup){
			$content .= '
			<!-- Page header -->
			<Row>
				<Cell ss:Index="2" ss:StyleID="s35"><Data ss:Type="String">'.
					htmlspecialchars($pageGroup->getPageTitle().' ['.$pageGroup->getUid().']').
				'</Data></Cell>
				<Cell ss:StyleID="s35"></Cell>
				<Cell ss:StyleID="s35"></Cell>
				<Cell ss:StyleID="s35"></Cell>
		 		'.($translateableInformation->getSourceLanguageId() ? '<Cell ss:StyleID="s35"></Cell>' : '').'
			</Row>';

			$this->rowCount++;

			$content .= '
			<!-- Field list header -->
			<Row>
				<Cell ss:Index="2" ss:StyleID="s38"><Data ss:Type="String">Fieldname:</Data></Cell>
				<Cell ss:StyleID="s38"><Data ss:Type="String">Original Value:</Data></Cell>
				<Cell ss:StyleID="s38"><Data ss:Type="String">Translation:</Data></Cell>
				<Cell ss:StyleID="s38"><Data ss:Type="String">Difference since last tr.:</Data></Cell>
			'.($translateableInformation->getSourceLanguageId()  ? '<Cell ss:StyleID="s38"><Data ss:Type="String">Preview Language:</Data></Cell>' : '').'
			</Row>';

			$this->rowCount++;

			foreach($pageGroup->getTranslateableElements() as $translateableElement){
				$table 					= $translateableElement->getTableName();
				$uid 					= $translateableElement->getUid();

				if($translateableElement->countFields() > 0){
					$content = '<!-- Element header -->
						<Row>
							<Cell ss:Index="2" ss:StyleID="s37"><Data ss:Type="String">Element: '.htmlspecialchars($table.':'.$uid).'</Data></Cell>
							<Cell ss:StyleID="s37"></Cell>
							<Cell ss:StyleID="s37"></Cell>
						 	<Cell ss:StyleID="s37"></Cell>
							'.($translateableInformation->getSourceLanguageId() ? '<Cell ss:StyleID="s37"></Cell>' : '').'
						</Row>';
					$this->rowCount++;

				}

				foreach($translateableElement->getTranslateableFields() as $translateableField){
					if (!$this->modeOnlyChanged || $translateableField->isChanged()){

						$key 					= $translateableField->getIdentityKey();
						$defaultValue			= $translateableField->getDefaultValue();
						$translationValue		= $translateableField->getTranslationValue();
						$previewLanguageValue	= $translateableField->getPreviewLanguageValueByLanguageId($translateableInformation->getSourceLanguageId());
						$fieldName				= $translateableField->getFieldName();
						$uidValue 				= $translateableField->getUidValue();
						$diffDefaultValue		= $translateableField->getDiffDefaultValue();
						$noChangeFlag			= $translateableField->isChanged();
						$message				= $translateableField->getMessage();

						$diff 					= $this->generateDiff($uidValue,$defaultValue,$diffDefaultValue,$noChangeFlag,$message);

						$content .= '
							<!-- Translation row: -->
								<Row ss:StyleID="s25">
								<Cell><Data ss:Type="String">'.htmlspecialchars('translation['.$table.']['.$uid.']['.$key.']').'</Data></Cell>
								<Cell ss:StyleID="s26"><Data ss:Type="String">'.htmlspecialchars($fieldName).'</Data></Cell>
								<Cell ss:StyleID="s27"><Data ss:Type="String">'.str_replace(chr(10),'&#10;',htmlspecialchars($defaultValue)).'</Data></Cell>
								<Cell ss:StyleID="s39"><Data ss:Type="String">'.str_replace(chr(10),'&#10;',htmlspecialchars($translationValue)).'</Data></Cell>
								<Cell ss:StyleID="s27"><Data ss:Type="String">'.$diff.'</Data></Cell>
								'.($translateableInformation->getSourceLanguageId() ? '<Cell ss:StyleID="s27"><Data ss:Type="String">'.str_replace(chr(10),'&#10;',htmlspecialchars($previewLanguageValue)).'</Data></Cell>' : '').'
						</Row>';

						$this->rowCount++;


					 }
				 }
			 }
			$content .= '
			<!-- Spacer row -->
			<Row>
				<Cell ss:Index="2"><Data ss:Type="String"></Data></Cell>
			</Row>';

			$this->rowCount++;
		}

		$this->setRenderedPageGroups($content);
	}

	/**
	 * This method is used to generate a excel diff view diff between the default and the diffDefault Value.
	 *
	 * @param int the uid of the record
	 * @param string
	 * @param string
	 * @param boolean
	 * @param string
	 */
	protected function generateDiff($uidValue,$defaultValue,$diffDefaultValue,$noChangeFlag,$message=''){
		$diff = '';
		if ($uidValue==='NEW')	{
			$diff = htmlspecialchars('[New value]');
		} elseif (!$diffDefaultValue) {
			$diff = htmlspecialchars('[No diff available]');
		} elseif ($noChangeFlag)	{
			$diff = htmlspecialchars('[No change]');
		} else {
			$diff = $this->diffCMP($diffDefaultValue,$defaultValue);
			$diff = str_replace('<span class="diff-r">','<Font html:Color="#FF0000" xmlns="http://www.w3.org/TR/REC-html40">',$diff);
			$diff = str_replace('<span class="diff-g">','<Font html:Color="#00FF00" xmlns="http://www.w3.org/TR/REC-html40">',$diff);
			$diff = str_replace('</span>','</Font>',$diff);
		}
		$diff.= ($message ? '[NOTE: '.htmlspecialchars($message).']':'');

		return $diff;
	}


	/**
	 * Returns the number of rows in the excel file
	 *
	 * @return int
	 */
	protected function getRowCount(){
		return $this->rowCount;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/excelXML/class.tx_l10nmgr_excelXMLView.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/excelXML/class.tx_l10nmgr_excelXMLView.php']);
}


?>
