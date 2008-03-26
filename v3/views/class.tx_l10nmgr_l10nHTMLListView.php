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



/**
 * l10nHTMLListView:
 * 	renders accumulated informations for the browser: 
 *	- Table with inline editing / links  etc...
 *	
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	Daniel Pötzinger <development@aoemedia.de>
 *
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_l10nHTMLListView {

	
	var $l10ncfgObj;	// 
	var $sysLang;	// Internal array (=datarow of config record)
	

	//internal flags:
	var $modeOnlyChanged=FALSE;
	var $modeWithInlineEdit=FALSE;
	var $modeShowEditLinks=FALSE;
	
	function tx_l10nmgr_l10nHTMLListView($l10ncfgObj, $sysLang) {
		global $BACK_PATH;
		$this->sysLang=$sysLang;
		$this->l10ncfgObj=$l10ncfgObj;
		
		$this->doc = t3lib_div::makeInstance('noDoc');
		$this->doc->backPath = $BACK_PATH;
		
	}
	
	function setModeOnlyChanged() {
		$this->modeOnlyChanged=TRUE;
	}
	
	function setModeWithInlineEdit() {
		$this->modeWithInlineEdit=TRUE;		
	}
	function setModeShowEditLinks() {
		$this->modeShowEditLinks=TRUE;
	}
	
	/**
	 * Render the module content in HTML
	 *
	 * @param	array		Translation data for configuration
	 * @param	integer		Sys language uid
	 * @param	array		Configuration record
	 * @return	string		HTML content
	 */
	function renderOverview()	{
		
		$sysLang=$this->sysLang;
		$accumObj=$this->l10ncfgObj->getL10nAccumulatedInformationsObjectForLanguage($sysLang);
		$accum=$accumObj->getInfoArray();				
		$l10ncfg=$this->l10ncfg;
		

		$output = '';
		
		$showSingle = t3lib_div::_GET('showSingle');

		if ($l10ncfg['displaymode']>0)	{
			$showSingle = $showSingle ? $showSingle : 'NONE';
			if ($l10ncfg['displaymode']==2)	{ $noAnalysis = TRUE;}
		} else $noAnalysis = FALSE;

			// Traverse the structure and generate HTML output:
		foreach($accum as $pId => $page)	{
			$output.= '<h3>'.$page['header']['icon'].htmlspecialchars($page['header']['title']).' ['.$pId.']</h3>';

			$tableRows = array();

			foreach($accum[$pId]['items'] as $table => $elements)	{
				foreach($elements as $elementUid => $data)	{
					if (is_array($data['fields']))	{

						$FtableRows = array();
						$flags = array();
						
						if (!$noAnalysis || $showSingle===$table.':'.$elementUid)	{
							foreach($data['fields'] as $key => $tData)	{
								if (is_array($tData))	{
									list(,$uidString,$fieldName) = explode(':',$key);
									list($uidValue) = explode('/',$uidString);

									$diff = '';
									$edit = TRUE;
									$noChangeFlag = !strcmp(trim($tData['diffDefaultValue']),trim($tData['defaultValue']));
									if ($uidValue==='NEW')	{
										$diff = '<em>New value</em>';
										$flags['new']++;
									} elseif (!isset($tData['diffDefaultValue'])) {
										$diff = '<em>No diff available</em>';
										$flags['unknown']++;
									} elseif ($noChangeFlag)	{
										$diff = 'No change.';
										$edit = TRUE;
										$flags['noChange']++;
									} else {
										$diff = $this->diffCMP($tData['diffDefaultValue'],$tData['defaultValue']);
										$flags['update']++;
									}

									if (!$this->modeOnlyChanged || !$noChangeFlag)	{
										$fieldCells = array();
										$fieldCells[] = '<b>'.htmlspecialchars($fieldName).'</b>'.($tData['msg']?'<br/><em>'.htmlspecialchars($tData['msg']).'</em>':'');
										$fieldCells[] = nl2br(htmlspecialchars($tData['defaultValue']));
										$fieldCells[] = $edit && $this->modeWithInlineEdit ? ($tData['fieldType']==='text' ? '<textarea name="'.htmlspecialchars('translation['.$table.']['.$elementUid.']['.$key.']').'" cols="60" rows="5">'.t3lib_div::formatForTextarea($tData['translationValue']).'</textarea>' : '<input name="'.htmlspecialchars('translation['.$table.']['.$elementUid.']['.$key.']').'" value="'.htmlspecialchars($tData['translationValue']).'" size="60" />') : nl2br(htmlspecialchars($tData['translationValue']));
										$fieldCells[] = $diff;
									
										reset($tData['previewLanguageValues']);
										if ($page['header']['prevLang']) $fieldCells[] = nl2br(htmlspecialchars(current($tData['previewLanguageValues'])));

										$FtableRows[] = '<tr><td>'.implode('</td><td>',$fieldCells).'</td></tr>';
									}
								}
							}
						}
						
						if (count($FtableRows) || $noAnalysis)	{
							
								// Link:
							if ($this->modeShowEditLinks)	{
								reset($data['fields']);
								list(,$uidString) = explode(':',key($data['fields']));
								if (substr($uidString,0,3)!=='NEW')	{
									$editId = is_array($data['translationInfo']['translations'][$sysLang]) ? $data['translationInfo']['translations'][$sysLang]['uid'] : $data['translationInfo']['uid'];
									$editLink = ' - <a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit['.$data['translationInfo']['translation_table'].']['.$editId.']=edit',$this->doc->backPath)).'"><em>[Click to edit]</em></a>';
								} else {
									$editLink = ' - <a href="'.htmlspecialchars($this->doc->issueCommand(
													'&cmd['.$table.']['.$data['translationInfo']['uid'].'][localize]='.$sysLang
													)).'"><em>[Click to localize]</em></a>';
								}
							} else $editLink = '';

							$tableRows[] = '<tr class="bgColor3">
								<td colspan="2" style="width:300px;"><a href="'.htmlspecialchars('index.php?id='.t3lib_div::_GET('id').'&showSingle='.rawurlencode($table.':'.$elementUid)).'">'.htmlspecialchars($table.':'.$elementUid).'</a>'.$editLink.'</td>
								<td colspan="3" style="width:200px;">'.htmlspecialchars(t3lib_div::arrayToLogString($flags)).'</td>
							</tr>';

							if (!$showSingle || $showSingle===$table.':'.$elementUid)	{
								$tableRows[] = '<tr class="bgColor5 tableheader">
									<td>Fieldname:</td>
									<td width="25%">Default:</td>
									<td width="25%">Translation:</td>
									<td width="25%">Diff:</td>
									'.($page['header']['prevLang'] ? '<td width="25%">PrevLang:</td>' : '').'
								</tr>';

								$tableRows = array_merge($tableRows, $FtableRows);
							}
						}
					}
				}
			}

			if (count($tableRows))	{
				$output.= '<table border="1" cellpadding="1" cellspacing="1" class="bgColor2" style="border: 1px solid #999999;">'.implode('',$tableRows).'</table>';
			}
		}

		return $output;
	}
	
	
	/**
	 * Diff-compare markup
	 *
	 * @param	string		Old content
	 * @param	string		New content
	 * @return	string		Marked up string.
	 */
	function diffCMP($old, $new)	{
			// Create diff-result:
		$t3lib_diff_Obj = t3lib_div::makeInstance('t3lib_diff');
		return $t3lib_diff_Obj->makeDiffDisplay($old,$new);
	}
	
}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/tx_l10nmgr_l10nmgrconfiguration_detail.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/views/tx_l10nmgr_l10nmgrconfiguration_detail.php']);
}


?>
