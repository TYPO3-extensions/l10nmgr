<?php
namespace Localizationteam\L10nmgr\View;

/***************************************************************
 *  Copyright notice
 *  (c) 2006 Kasper Skårhøj <kasperYYYY@typo3.com>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * l10nHTMLListView:
 *  renders accumulated informations for the browser:
 *  - Table with inline editing / links  etc...
 *
 * @author  Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author  Daniel Pötzinger <development@aoemedia.de>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class L10nHtmlListView extends AbstractExportView
{
    var $l10ncfgObj; //
    var $sysLang; // Internal array (=datarow of config record)
    //internal flags:
    var $modeWithInlineEdit = false;
    var $modeShowEditLinks = false;
    protected $module;
    protected $l10ncfg;
    
    function __construct($l10ncfgObj, $sysLang)
    {
        global $BACK_PATH;
        $this->module = GeneralUtility::makeInstance(DocumentTemplate::class);
        $this->module->backPath = $BACK_PATH;
        parent::__construct($l10ncfgObj, $sysLang);
    }
    
    function setModeWithInlineEdit()
    {
        $this->modeWithInlineEdit = true;
    }
    
    function setModeShowEditLinks()
    {
        $this->modeShowEditLinks = true;
    }
    
    /**
     * Render the module content in HTML
     *
     * @return  string    HTML content
     */
    function renderOverview()
    {
        global $LANG;
        $sysLang = $this->sysLang;
        $accumObj = $this->l10ncfgObj->getL10nAccumulatedInformationsObjectForLanguage($sysLang);
        $accum = $accumObj->getInfoArray();
        $l10ncfg = $this->l10ncfg;
        $output = '';
        $showSingle = GeneralUtility::_GET('showSingle');
        $noAnalysis = false;
        if ($l10ncfg['displaymode'] > 0) {
            $showSingle = $showSingle ? $showSingle : 'NONE';
            if ($l10ncfg['displaymode'] == 2) {
                $noAnalysis = true;
            }
        }
        // Traverse the structure and generate HTML output:
        foreach ($accum as $pId => $page) {
            $output .= '<h3>' . $page['header']['icon'] . htmlspecialchars($page['header']['title']) . ' [' . $pId . ']</h3>';
            $tableRows = array();
            foreach ($accum[$pId]['items'] as $table => $elements) {
                foreach ($elements as $elementUid => $data) {
                    if (is_array($data['fields'])) {
                        $FtableRows = array();
                        $flags = array();
                        if (!$noAnalysis || $showSingle === $table . ':' . $elementUid) {
                            foreach ($data['fields'] as $key => $tData) {
                                if (is_array($tData)) {
                                    list(, $uidString, $fieldName) = explode(':', $key);
                                    list($uidValue) = explode('/', $uidString);
                                    $edit = true;
                                    $noChangeFlag = !strcmp(trim($tData['diffDefaultValue']),
                                        trim($tData['defaultValue']));
                                    if ($uidValue === 'NEW') {
                                        $diff = '<em>' . $LANG->getLL('render_overview.new.message') . '</em>';
                                        $flags['new']++;
                                    } elseif (!isset($tData['diffDefaultValue'])) {
                                        $diff = '<em>' . $LANG->getLL('render_overview.nodiff.message') . '</em>';
                                        $flags['unknown']++;
                                    } elseif ($noChangeFlag) {
                                        $diff = $LANG->getLL('render_overview.nochange.message');
                                        $edit = true;
                                        $flags['noChange']++;
                                    } else {
                                        $diff = $this->diffCMP($tData['diffDefaultValue'], $tData['defaultValue']);
                                        $flags['update']++;
                                    }
                                    if (!$this->modeOnlyChanged || !$noChangeFlag) {
                                        $fieldCells = array();
                                        $fieldCells[] = '<b>' . htmlspecialchars($fieldName) . '</b>' . ($tData['msg'] ? '<br/><em>' . htmlspecialchars($tData['msg']) . '</em>' : '');
                                        $fieldCells[] = nl2br(htmlspecialchars($tData['defaultValue']));
                                        $fieldCells[] = $edit && $this->modeWithInlineEdit ? ($tData['fieldType'] === 'text' ? '<textarea name="' . htmlspecialchars('translation[' . $table . '][' . $elementUid . '][' . $key . ']') . '" cols="60" rows="5">' . LF . htmlspecialchars($tData['translationValue']) . '</textarea>' : '<input name="' . htmlspecialchars('translation[' . $table . '][' . $elementUid . '][' . $key . ']') . '" value="' . htmlspecialchars($tData['translationValue']) . '" size="60" />') : nl2br(htmlspecialchars($tData['translationValue']));
                                        $fieldCells[] = $diff;
                                        if ($page['header']['prevLang'] && is_array($tData['previewLanguageValues'])) {
                                            reset($tData['previewLanguageValues']);
                                            $fieldCells[] = nl2br(htmlspecialchars(current($tData['previewLanguageValues'])));
                                        }
                                        $FtableRows[] = '<tr><td>' . implode('</td><td>',
                                                $fieldCells) . '</td></tr>';
                                    }
                                }
                            }
                        }
                        if (count($FtableRows) || $noAnalysis) {
                            // Link:
                            if ($this->modeShowEditLinks) {
                                $uidString = '';
                                if (is_array($data['fields'])) {
                                    reset($data['fields']);
                                    list(, $uidString) = explode(':', key($data['fields']));
                                }
                                if (substr($uidString, 0, 3) !== 'NEW') {
                                    $editId = is_array($data['translationInfo']['translations'][$sysLang]) ? $data['translationInfo']['translations'][$sysLang]['uid'] : $data['translationInfo']['uid'];
                                    $editLink = ' - <a href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick('&edit[' . $data['translationInfo']['translation_table'] . '][' . $editId . ']=edit',
                                            $this->module->backPath)) . '"><em>[' . $LANG->getLL('render_overview.clickedit.message') . ']</em></a>';
                                } else {
                                    $editLink = ' - <a href="' . htmlspecialchars($this->module->issueCommand('&cmd[' . $table . '][' . $data['translationInfo']['uid'] . '][localize]=' . $sysLang)) . '"><em>[' . $LANG->getLL('render_overview.clicklocalize.message') . ']</em></a>';
                                }
                            } else {
                                $editLink = '';
                            }
                            $tableRows[] = '<tr class="info">
								<th colspan="2"><a href="' . htmlspecialchars('index.php?id=' . GeneralUtility::_GET('id') . '&showSingle=' . rawurlencode($table . ':' . $elementUid)) . '">' . htmlspecialchars($table . ':' . $elementUid) . '</a>' . $editLink . '</th>
								<th colspan="3">' . htmlspecialchars(GeneralUtility::arrayToLogString($flags)) . '</th>
							</tr>';
                            if (!$showSingle || $showSingle === $table . ':' . $elementUid) {
                                $tableRows[] = '<tr>
									<th>Fieldname</th>
									<th width="25%">Default</th>
									<th width="25%">Translation</th>
									<th width="25%">Diff</th>
									' . ($page['header']['prevLang'] ? '<th width="25%">PrevLang</th>' : '') . '
								</tr>';
                                $tableRows = array_merge($tableRows, $FtableRows);
                            }
                        }
                    }
                }
            }
            if (count($tableRows)) {
                $output .= '<table class="table table-striped table-hover">' . implode('', $tableRows) . '</table>';
            }
        }
        return $output;
    }
}