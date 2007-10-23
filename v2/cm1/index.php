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
 * l10nmgr module cm1
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   68: class tx_l10nmgr_cm1 extends t3lib_SCbase
 *   75:     function menuConfig()
 *   89:     function main()
 *  101:     function jumpToUrl(URL)
 *  142:     function printContent()
 *  154:     function moduleContent($l10ncfg)
 *  203:     function render_HTMLOverview($accum)
 *  265:     function diffCMP($old, $new)
 *  278:     function submitContent($accum,$inputArray)
 *  376:     function getAccumulated($tree, $l10ncfg, $sysLang)
 *
 * TOTAL FUNCTIONS: 9
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:l10nmgr/cm1/locallang.xml');
require_once (PATH_t3lib.'class.t3lib_scbase.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'cm1/class.tx_l10nmgr_tools.php');


/**
 * Translation management tool
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class tx_l10nmgr_cm1 extends t3lib_SCbase {

	var $flexFormDiffArray = array();	// Internal

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'action' => array(
				'link' => 'Overview with links',
				'inlineEdit' => 'Inline Edit',
				'export_excel' => 'ImpExp: Excel',
			),
			'lang' => array(),
			'onlyChangedContent' => ''
		);
		
			// Load system languages into menu:
		$t8Tools = t3lib_div::makeInstance('t3lib_transl8tools');
		$sysL = $t8Tools->getSystemLanguages();
		foreach($sysL as $sL)	{
			if ($sL['uid']>0 && $GLOBALS['BE_USER']->checkLanguageAccess($sL['uid']))	{
				$this->MOD_MENU['lang'][$sL['uid']] = $sL['title'];
			}
		}
		
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to
	 *
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

			// Draw the header.
		$this->doc = t3lib_div::makeInstance('noDoc');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form='<form action="" method="post" enctype="'.$TYPO3_CONF_VARS['SYS']['form_enctype'].'">';

			// JavaScript
		$this->doc->JScode = '
			<script language="javascript" type="text/javascript">
				script_ended = 0;
				function jumpToUrl(URL)	{
					document.location = URL;
				}
			</script>
		';


			// Find l10n configuration record:
		$l10ncfg = t3lib_BEfunc::getRecord('tx_l10nmgr_cfg', $this->id);
		if (is_array($l10ncfg))	{

				// Setting page id
			$this->id = $l10ncfg['pid'];
			$this->perms_clause = $GLOBALS['BE_USER']->getPagePermsClause(1);
			$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
			$access = is_array($this->pageinfo) ? 1 : 0;
			if ($this->id && $access)	{

					// Header:
				$this->content.=$this->doc->startPage($LANG->getLL('title'));
				$this->content.=$this->doc->header($LANG->getLL('title'));
				
				$this->content.=$this->doc->section('','
				<table border="1" cellpadding="1" cellspacing="0" width="400">
					<tr class="bgColor5 tableheader">
						<td colspan="4">Configuration: <strong>'.htmlspecialchars($l10ncfg['title']).' ['.$l10ncfg['uid'].']</strong></td>
					</tr>
					<tr class="bgColor3">
						<td><strong>Depth:</strong></td>
						<td>'.htmlspecialchars($l10ncfg['depth']).'</td>
						<td><strong>Tables:</strong></td>
						<td>'.htmlspecialchars($l10ncfg['tablelist']).'</td>
					</tr>
					<tr class="bgColor3">
						<td><strong>Exclude:</strong></td>
						<td>'.htmlspecialchars($l10ncfg['exclude']).'</td>
						<td><strong>Include:</strong></td>
						<td>'.htmlspecialchars($l10ncfg['include']).'</td>
					</tr>
				</table>
				
				');
				
				
				$this->content.=$this->doc->divider(5);
				$this->content.=$this->doc->section('',
						t3lib_BEfunc::getFuncMenu($l10ncfg['uid'],"SET[lang]",$this->MOD_SETTINGS["lang"],$this->MOD_MENU["lang"],'','&srcPID='.rawurlencode(t3lib_div::_GET('srcPID'))).
						t3lib_BEfunc::getFuncMenu($l10ncfg['uid'],"SET[action]",$this->MOD_SETTINGS["action"],$this->MOD_MENU["action"],'','&srcPID='.rawurlencode(t3lib_div::_GET('srcPID'))).
						t3lib_BEfunc::getFuncCheck($l10ncfg['uid'],"SET[onlyChangedContent]",$this->MOD_SETTINGS["onlyChangedContent"],'','&srcPID='.rawurlencode(t3lib_div::_GET('srcPID'))).' New/Changed content only</br>'
					);

					// Render content:
				if (!count($this->MOD_MENU['lang']))	{
					$this->content.= $this->doc->section('ERROR','User has no access to edit any translations');
				} else {
					$this->moduleContent($l10ncfg);
				}

				// ShortCut
				if ($BE_USER->mayMakeShortcut())	{
					$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
				}
			}
		}

		$this->content.=$this->doc->spacer(10);
	}

	/**
	 * Printing output content
	 *
	 * @return	void
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Creating module content
	 *
	 * @param	array		Localization Configuration record
	 * @return	void
	 */
	function moduleContent($l10ncfg)	{
		global $TCA;

			// Get language to export here:
		$sysLang = $this->MOD_SETTINGS["lang"];

			// Showing the tree:
			// Initialize starting point of page tree:
		$treeStartingPoint = intval($l10ncfg['depth']==-1 ? t3lib_div::_GET('srcPID') : $l10ncfg['pid']);
		$treeStartingRecord = t3lib_BEfunc::getRecordWSOL('pages', $treeStartingPoint);
		$depth = $l10ncfg['depth'];

			// Initialize tree object:
		$tree = t3lib_div::makeInstance('t3lib_pageTree');
		$tree->init('AND '.$GLOBALS['BE_USER']->getPagePermsClause(1));
		$tree->addField('l18n_cfg');

			// Creating top icon; the current page
		$HTML = t3lib_iconWorks::getIconImage('pages', $treeStartingRecord, $GLOBALS['BACK_PATH'],'align="top"');
		$tree->tree[] = array(
			'row' => $treeStartingRecord,
			'HTML'=> $HTML
		);

			// Create the tree from starting point:
		if ($depth>0)	$tree->getTree($treeStartingPoint, $depth, '');


			// Generate array with data for the translation export
		$accum = $this->getAccumulated($tree, $l10ncfg, $sysLang);




			// Based on the "action" we show a set of buttons and execute some functionality:
		if ($this->MOD_SETTINGS["action"]=='inlineEdit')	{	// Inline editing:
			
				// Buttons:
			$info.= '<input type="submit" value="Save" name="saveInline" onclick="return confirm(\'You are about to create/update ALL localizations in this form? Continue?\');" />';
			$info.= '<input type="submit" value="Cancel" name="_" onclick="return confirm(\'You are about to discard any changes you made. Continue?\');" />';

				// See, if incoming translation is available, if so, submit it and re-generate array with data
			if (t3lib_div::_POST('saveInline') && $this->submitContent($accum,t3lib_div::_POST('translation')))	{
				$this->updateFlexFormDiff($l10ncfg, $sysLang);

					// reloading if submitting stuff...
				$accum = $this->getAccumulated($tree, $l10ncfg, $sysLang);	
			}

		} elseif ($this->MOD_SETTINGS["action"]=='export_excel')	{		// Excel import/export:
			
				// Buttons:
			$info.= '<input type="submit" value="Refresh" name="_" />';
			$info.= '<input type="submit" value="Export" name="export_excel" />';
			$info.= '<input type="submit" value="Import" name="import_excel" /><input type="file" size="60" name="uploaded_import_file" />';

				// Read uploaded file:
			if (t3lib_div::_POST('import_excel') && $_FILES['uploaded_import_file']['tmp_name'] && is_uploaded_file($_FILES['uploaded_import_file']['tmp_name']))	{
				$uploadedTempFile = t3lib_div::upload_to_tempfile($_FILES['uploaded_import_file']['tmp_name']);
				$fileContent = t3lib_div::getUrl($uploadedTempFile);
				t3lib_div::unlink_tempfile($uploadedTempFile);

					// Parse XML
				$translation = $this->parseXML( $fileContent );
				
				if (count($translation) && $this->submitContent($accum,$translation)) {
					$info.='<br/><br/>'.$this->doc->icons(1).'Import done<br/><br/>';
					$this->updateFlexFormDiff($l10ncfg, $sysLang);
					
						// reloading if submitting stuff...
					$accum = $this->getAccumulated($tree, $l10ncfg, $sysLang);	
				}
			}

				// If export of XML is asked for, do that (this will exit and push a file for download)
			if (t3lib_div::_POST('export_excel'))	{
				$this->render_excelXML($accum, $sysLang);
			}
		} else {	// Default display:
			$info.= '<input type="submit" value="Refresh" name="_" />';
		}

			// Render the module content (for all modes):
		$this->content.=$this->doc->section('',$info.$this->render_HTMLOverview($accum, $sysLang, $l10ncfg));
	}

	/**
	 * Render the module content in HTML
	 *
	 * @param	array		Translation data for configuration
	 * @param	integer		Sys language uid
	 * @param	array		Configuration record
	 * @return	string		HTML content
	 */
	function render_HTMLOverview($accum, $sysLang, $l10ncfg)	{

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

									if (!$this->MOD_SETTINGS["onlyChangedContent"] || !$noChangeFlag)	{
										$fieldCells = array();
										$fieldCells[] = '<b>'.htmlspecialchars($fieldName).'</b>'.($tData['msg']?'<br/><em>'.htmlspecialchars($tData['msg']).'</em>':'');
										$fieldCells[] = nl2br(htmlspecialchars($tData['defaultValue']));
										$fieldCells[] = $edit && $this->MOD_SETTINGS["action"]=='inlineEdit' ? ($tData['fieldType']==='text' ? '<textarea name="'.htmlspecialchars('translation['.$table.']['.$elementUid.']['.$key.']').'" cols="60" rows="5">'.t3lib_div::formatForTextarea($tData['translationValue']).'</textarea>' : '<input name="'.htmlspecialchars('translation['.$table.']['.$elementUid.']['.$key.']').'" value="'.htmlspecialchars($tData['translationValue']).'" size="60" />') : nl2br(htmlspecialchars($tData['translationValue']));
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
							if ($this->MOD_SETTINGS["action"]=='link')	{
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
	 * Render the excel XML export
	 *
	 * @param	array		Translation data for configuration
	 * @return	string		HTML content
	 */
	function render_excelXML($accum, $sysLang)	{

		$output = array();

			// Traverse the structure and generate HTML output:
		foreach($accum as $pId => $page)	{
			$output[]= '
			<!-- Page header -->
		   <Row>
		    <Cell ss:Index="2" ss:StyleID="s35"><Data ss:Type="String">'.htmlspecialchars($page['header']['title'].' ['.$pId.']').'</Data></Cell>
		    <Cell ss:StyleID="s35"></Cell>
		    <Cell ss:StyleID="s35"></Cell>
		    <Cell ss:StyleID="s35"></Cell>
		    '.($page['header']['prevLang'] ? '<Cell ss:StyleID="s35"></Cell>' : '').'
		   </Row>';

			$output[]= '
			<!-- Field list header -->
		   <Row>
		    <Cell ss:Index="2" ss:StyleID="s38"><Data ss:Type="String">Fieldname:</Data></Cell>
		    <Cell ss:StyleID="s38"><Data ss:Type="String">Original Value:</Data></Cell>
		    <Cell ss:StyleID="s38"><Data ss:Type="String">Translation:</Data></Cell>
		    <Cell ss:StyleID="s38"><Data ss:Type="String">Difference since last tr.:</Data></Cell>
		    '.($page['header']['prevLang'] ? '<Cell ss:StyleID="s38"><Data ss:Type="String">Preview Language:</Data></Cell>' : '').'
		   </Row>';

			foreach($accum[$pId]['items'] as $table => $elements)	{
				foreach($elements as $elementUid => $data)	{
					if (is_array($data['fields']))	{

						$fieldsForRecord = array();
						foreach($data['fields'] as $key => $tData)	{
							if (is_array($tData))	{
								list(,$uidString,$fieldName) = explode(':',$key);
								list($uidValue) = explode('/',$uidString);

								$diff = '';
								$noChangeFlag = !strcmp(trim($tData['diffDefaultValue']),trim($tData['defaultValue']));
								if ($uidValue==='NEW')	{
									$diff = htmlspecialchars('[New value]');
								} elseif (!$tData['diffDefaultValue']) {
									$diff = htmlspecialchars('[No diff available]');
								} elseif ($noChangeFlag)	{
									$diff = htmlspecialchars('[No change]');
								} else {
									$diff = $this->diffCMP($tData['diffDefaultValue'],$tData['defaultValue']);
									$diff = str_replace('<span class="diff-r">','<Font html:Color="#FF0000" xmlns="http://www.w3.org/TR/REC-html40">',$diff);
									$diff = str_replace('<span class="diff-g">','<Font html:Color="#00FF00" xmlns="http://www.w3.org/TR/REC-html40">',$diff);
									$diff = str_replace('</span>','</Font>',$diff);
								}
								$diff.= ($tData['msg']?'[NOTE: '.htmlspecialchars($tData['msg']).']':'');
								
								if (!$this->MOD_SETTINGS["onlyChangedContent"] || !$noChangeFlag)	{
									reset($tData['previewLanguageValues']);
									$fieldsForRecord[]= '
								<!-- Translation row: -->
								   <Row ss:StyleID="s25">
								    <Cell><Data ss:Type="String">'.htmlspecialchars('translation['.$table.']['.$elementUid.']['.$key.']').'</Data></Cell>
								    <Cell ss:StyleID="s26"><Data ss:Type="String">'.htmlspecialchars($fieldName).'</Data></Cell>
								    <Cell ss:StyleID="s27"><Data ss:Type="String">'.str_replace(chr(10),'&#10;',htmlspecialchars($tData['defaultValue'])).'</Data></Cell>
								    <Cell ss:StyleID="s39"><Data ss:Type="String">'.str_replace(chr(10),'&#10;',htmlspecialchars($tData['translationValue'])).'</Data></Cell>
								    <Cell ss:StyleID="s27"><Data ss:Type="String">'.$diff.'</Data></Cell>
								    '.($page['header']['prevLang'] ? '<Cell ss:StyleID="s27"><Data ss:Type="String">'.str_replace(chr(10),'&#10;',htmlspecialchars(current($tData['previewLanguageValues']))).'</Data></Cell>' : '').'
								   </Row>
									';
								}
							}
						}

						if (count($fieldsForRecord))	{
							$output[]= '
							<!-- Element header -->
						   <Row>
						    <Cell ss:Index="2" ss:StyleID="s37"><Data ss:Type="String">Element: '.htmlspecialchars($table.':'.$elementUid).'</Data></Cell>
						    <Cell ss:StyleID="s37"></Cell>
						    <Cell ss:StyleID="s37"></Cell>
						    <Cell ss:StyleID="s37"></Cell>
						    '.($page['header']['prevLang'] ? '<Cell ss:StyleID="s37"></Cell>' : '').'
						   </Row>
							';
							
							$output = array_merge($output, $fieldsForRecord);
						}
					}
				}
			}

				$output[]= '
				<!-- Spacer row -->
			   <Row>
			    <Cell ss:Index="2"><Data ss:Type="String"></Data></Cell>
			   </Row>
				';
		}

		$excelXML = t3lib_div::getUrl('./excel_template.xml');
		$excelXML = str_replace('###INSERT_ROWS###',implode('', $output), $excelXML);
		$excelXML = str_replace('###INSERT_ROW_COUNT###',count($output), $excelXML);

			// Setting filename:
		$filename = 'excel_export_'.$sysLang.'_'.date('dmy-Hi').'.xml';

			// Creating output header:
		$mimeType = 'text/xml';
		Header('Charset: utf-8');
		Header('Content-Type: '.$mimeType);
		Header('Content-Disposition: attachment; filename='.$filename);
		echo $excelXML;
		exit;
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

	/**
	 * Submit incoming content to database. Must match what is available in $accum.
	 *
	 * @param	array		Translation configuration
	 * @param	array		Array with incoming translation. Must match what is found in $accum
	 * @return	boolean		TRUE, if $inputArray was an array and processing was performed.
	 */
	function submitContent($accum,$inputArray)	{

		if (is_array($inputArray))	{

				// Initialize:
			$flexToolObj = t3lib_div::makeInstance('t3lib_flexformtools');
			$TCEmain_data = array();
			$TCEmain_cmd = array();
			
			$this->flexFormDiffArray = array();

				// Traverse:
			foreach($accum as $pId => $page)	{
				foreach($accum[$pId]['items'] as $table => $elements)	{
					foreach($elements as $elementUid => $data)	{
						if (is_array($data['fields']))	{
							foreach($data['fields'] as $key => $tData)	{
								if (is_array($tData) && isset($inputArray[$table][$elementUid][$key]))	{
									list($Ttable,$TuidString,$Tfield,$Tpath) = explode(':',$key);
									list($Tuid,$Tlang,$TdefRecord) = explode('/',$TuidString);

										// If new element is required, we prepare for localization
									if ($Tuid==='NEW')	{
										$TCEmain_cmd[$table][$elementUid]['localize'] = $Tlang;
									}

										// If FlexForm, we set value in special way:
									if ($Tpath)	{
										if (!is_array($TCEmain_data[$Ttable][$TuidString][$Tfield]))	{
											$TCEmain_data[$Ttable][$TuidString][$Tfield] = array();
										}
										$flexToolObj->setArrayValueByPath($Tpath,$TCEmain_data[$Ttable][$TuidString][$Tfield],$inputArray[$table][$elementUid][$key]);
										$this->flexFormDiffArray[$key] = array('translated' => $inputArray[$table][$elementUid][$key], 'default' => $tData['defaultValue']);
									} else {
										$TCEmain_data[$Ttable][$TuidString][$Tfield] = $inputArray[$table][$elementUid][$key];
									}
									unset($inputArray[$table][$elementUid][$key]);	// Unsetting so in the end we can see if $inputArray was fully processed.
								}
							}
							if (is_array($inputArray[$table][$elementUid]) && !count($inputArray[$table][$elementUid]))	{
								unset($inputArray[$table][$elementUid]);	// Unsetting so in the end we can see if $inputArray was fully processed.
							}
						}
					}
					if (is_array($inputArray[$table]) && !count($inputArray[$table]))	{
						unset($inputArray[$table]);	// Unsetting so in the end we can see if $inputArray was fully processed.
					}
				}
			}
#debug($TCEmain_cmd,'$TCEmain_cmd');
#debug($TCEmain_data,'$TCEmain_data');

				// Execute CMD array: Localizing records:
			$tce = t3lib_div::makeInstance('t3lib_TCEmain');
			$tce->stripslashes_values = FALSE;
			if (count($TCEmain_cmd))	{
				$tce->start(array(),$TCEmain_cmd);
				$tce->process_cmdmap();
				if (count($tce->errorLog))	{
					debug($tce->errorLog,'TCEmain localization errors:');
				}
			}
#debug($tce->copyMappingArray_merged,'$tce->copyMappingArray_merged');
				// Remapping those elements which are new:
			foreach($TCEmain_data as $table => $items)	{
				foreach($TCEmain_data[$table] as $TuidString => $fields)	{
					list($Tuid,$Tlang,$TdefRecord) = explode('/',$TuidString);
					if ($Tuid === 'NEW')	{
						if ($tce->copyMappingArray_merged[$table][$TdefRecord])	{
							$TCEmain_data[$table][t3lib_BEfunc::wsMapId($table,$tce->copyMappingArray_merged[$table][$TdefRecord])] = $fields;
						} else {
							debug('Record "'.$table.':'.$TdefRecord.'" was NOT localized as it should have been!');
						}
						unset($TCEmain_data[$table][$TuidString]);
					}
				}
			}
#debug($TCEmain_data,'$TCEmain_data');

				// Now, submitting translation data:
			$tce = t3lib_div::makeInstance('t3lib_TCEmain');
			$tce->stripslashes_values = FALSE;
			$tce->dontProcessTransformations = TRUE;
			$tce->start($TCEmain_data,array());	// check has been done previously that there is a backend user which is Admin and also in live workspace
			$tce->process_datamap();
			
			if (count($tce->errorLog))	{
				debug($tce->errorLog,'TCEmain update errors:');
			}
			
			if (count($tce->autoVersionIdMap) && count($this->flexFormDiffArray))	{
			#	debug($this->flexFormDiffArray);
				foreach($this->flexFormDiffArray as $key => $value)	{
					list($Ttable,$Tuid,$Trest) = explode(':',$key,3);
					if ($tce->autoVersionIdMap[$Ttable][$Tuid])	{
						$this->flexFormDiffArray[$Ttable.':'.$tce->autoVersionIdMap[$Ttable][$Tuid].':'.$Trest] = $this->flexFormDiffArray[$key];
						unset($this->flexFormDiffArray[$key]);
					}
				}
#				debug($tce->autoVersionIdMap);
#				debug($this->flexFormDiffArray);
			}
			
			
				// Should be empty now - or there were more information in the incoming array than there should be!
			if (count($inputArray))	{
				debug($inputArray,'These fields were ignored since they were not in the configuration:');
			}

			return TRUE;
		}
	}

	/**
	 * Create information array with accumulated information
	 *
	 * @param	array		Page tree
	 * @param	array		Localization configuration record.
	 * @param	integer		sys_language uid
	 * @return	array		Information array
	 */
	function getAccumulated($tree, $l10ncfg, $sysLang) 	{
		global $TCA;

		$accum = array();
		
			// FlexForm Diff data:
		$flexFormDiff = unserialize($l10ncfg['flexformdiff']);
		$flexFormDiff = $flexFormDiff[$sysLang];

		$excludeIndex = array_flip(t3lib_div::trimExplode(',',$l10ncfg['exclude'],1));

			// Init:
		$t8Tools = t3lib_div::makeInstance('tx_l10nmgr_tools');
		$t8Tools->verbose = FALSE;	// Otherwise it will show records which has fields but none editable.

			// Set preview language (only first one in list is supported):
		$previewLanguage = current(t3lib_div::intExplode(',',$GLOBALS['BE_USER']->getTSConfigVal('options.additionalPreviewLanguages')));
		if ($previewLanguage)	{
			$t8Tools->previewLanguages = array($previewLanguage);
		}

			// Traverse tree elements:
		foreach($tree->tree as $treeElement)	{

			$pageId = $treeElement['row']['uid'];
			if (!isset($excludeIndex['pages:'.$pageId]) && ($treeElement['row']['l18n_cfg']&2)!=2 && $treeElement['row']['doktype']<200)	{
			
				$accum[$pageId]['header']['title']	= $treeElement['row']['title'];
				$accum[$pageId]['header']['icon']	= $treeElement['HTML'];
				$accum[$pageId]['header']['prevLang'] = $previewLanguage;
				$accum[$pageId]['items'] = array();

					// Traverse tables:
				foreach($TCA as $table => $cfg)	{

						// Only those tables we want to work on:
					if (t3lib_div::inList($l10ncfg['tablelist'], $table))	{

						if ($table === 'pages')	{
							$accum[$pageId]['items'][$table][$pageId] = $t8Tools->translationDetails('pages',t3lib_BEfunc::getRecordWSOL('pages',$pageId),$sysLang, $flexFormDiff);
						} else {
							$allRows = $t8Tools->getRecordsToTranslateFromTable($table, $pageId);
							if (is_array($allRows))	{
								if (count($allRows))	{
										// Now, for each record, look for localization:
									foreach($allRows as $row)	{
										t3lib_BEfunc::workspaceOL($table,$row);
										if (is_array($row) && !isset($excludeIndex[$table.':'.$row['uid']]))	{
											$accum[$pageId]['items'][$table][$row['uid']] = $t8Tools->translationDetails($table,$row,$sysLang,$flexFormDiff);
										}
									}
								}
							}
						}
					}
				}
			}
		}


		$includeIndex = array_unique(t3lib_div::trimExplode(',',$l10ncfg['include'],1));
		foreach($includeIndex as $recId)	{
			list($table, $uid) = explode(':',$recId);
			$row = t3lib_BEfunc::getRecordWSOL($table, $uid);
			if (count($row))	{
				$accum[-1]['items'][$table][$row['uid']] = $t8Tools->translationDetails($table,$row,$sysLang,$flexFormDiff);
			}
		}

#debug($accum);
		return $accum;
	}
	
	function updateFlexFormDiff(&$l10ncfg, $sysLang)	{

			// Updating diff-data:
			// First, unserialize/initialize:
		$flexFormDiffForAllLanguages = unserialize($l10ncfg['flexformdiff']);
		if (!is_array($flexFormDiffForAllLanguages))	{
			$flexFormDiffForAllLanguages = array();
		}

			// Set the data ($this->flexFormDiffArray should be set inside submitContent())
		$flexFormDiffForAllLanguages[$sysLang] = array_merge((array)$flexFormDiffForAllLanguages[$sysLang],$this->flexFormDiffArray);

			// Serialize back and save it to record:
		$l10ncfg['flexformdiff'] = serialize($flexFormDiffForAllLanguages);
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_l10nmgr_cfg','uid='.intval($l10ncfg['uid']),array('flexformdiff' => $l10ncfg['flexformdiff']));
	}



	function parseXML( $fileContent ) {
			// Parse XML in a rude fashion:
		$xmlNodes = t3lib_div::xml2tree(str_replace('&nbsp;',' ',$fileContent));	// For some reason PHP chokes on incoming &nbsp; in XML!
		$translation = array();

			// At least OpenOfficeOrg Calc changes the worksheet identifier. For now we better check for this, otherwise we cannot import translations edited with OpenOfficeOrg Calc.
		if ( isset( $xmlNodes['Workbook'][0]['ch']['Worksheet'] ) ) {
			$worksheetIdentifier = 'Worksheet';
		}
		if ( isset( $xmlNodes['Workbook'][0]['ch']['ss:Worksheet'] ) ) {
			$worksheetIdentifier = 'ss:Worksheet';
		}

			// OK, this method of parsing the XML really sucks, but it was 4:04 in the night and ... I have no clue to make it better on PHP4. Anyway, this will work for now. But is probably unstable in case a user puts formatting in the content of the translation! (since only the first CData chunk will be found!)
		if (is_array($xmlNodes['Workbook'][0]['ch'][$worksheetIdentifier][0]['ch']['Table'][0]['ch']['Row']))	{
			foreach($xmlNodes['Workbook'][0]['ch'][$worksheetIdentifier][0]['ch']['Table'][0]['ch']['Row'] as $row)	{
				if (!isset($row['ch']['Cell'][0]['attrs']['ss:Index']))	{
					list($Ttable, $Tuid, $Tkey) = explode('][',substr(trim($row['ch']['Cell'][0]['ch']['Data'][0]['values'][0]),12,-1));
					$translation[$Ttable][$Tuid][$Tkey] = $row['ch']['Cell'][3]['ch']['Data'][0]['values'][0];
				}
			}
		}
		return $translation;
	}
}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/cm1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/cm1/index.php']);
}


// Make instance:
$SOBE = t3lib_div::makeInstance('tx_l10nmgr_cm1');
$SOBE->init();

$SOBE->main();
$SOBE->printContent();
?>