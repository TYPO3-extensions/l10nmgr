<?php
namespace Localizationteam\L10nmgr\Model;

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
use TYPO3\CMS\Version\Hook\PreviewHook;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Function for generating preview links during import
 *
 * @author  Daniel Zielinski <d.zielinski@L10Ntech.de>
 *
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class MkPreviewLinkService
{

    var $_errorMsg = array();

    function __construct($t3_workspaceId, $t3_sysLang, $pageIds)
    {
        $this->sysLang = $t3_sysLang;
        $this->pageIds = $pageIds;
        $this->workspaceId = $t3_workspaceId;
    }

    // Generate single source preview link for service
    function mkSingleSrcPreviewLink($baseUrl, $srcLang)
    {

        $ttlHours = (int)$GLOBALS['BE_USER']->getTSConfigVal('options.workspaces.previewLinkTTLHours');
        $ttlHours = ($ttlHours ? $ttlHours : 24 * 2);
        $params = 'id=' . $this->pageIds[0] . '&L=' . $srcLang . '&ADMCMD_previewWS=' . $this->workspaceId;
        $previewUrl = $baseUrl . 'index.php?ADMCMD_prev=' . PreviewHook::compilePreviewKeyword($params,
                $GLOBALS['BE_USER']->user['uid'], 60 * 60 * $ttlHours);

        return $previewUrl;
    }

    // Generate single target preview link for CLI
    function mkSinglePreviewLink($baseUrl, $serverlink)
    {

        $ttlHours = (int)$GLOBALS['BE_USER']->getTSConfigVal('options.workspaces.previewLinkTTLHours');
        $ttlHours = ($ttlHours ? $ttlHours : 24 * 2);
        //no_cache=1 ???
        $params = 'id=' . $this->pageIds[0] . '&L=' . $this->sysLang . '&ADMCMD_previewWS=' . $this->workspaceId . '&serverlink=' . $serverlink;
        $previewUrl = $baseUrl . 'index.php?ADMCMD_prev=' . PreviewHook::compilePreviewKeyword($params,
                $GLOBALS['BE_USER']->user['uid'], 60 * 60 * $ttlHours);

        return $previewUrl;
    }

    // Generate list of preview links for backend or email
    function mkPreviewLinks()
    {

        $previewUrls = array();
        foreach ($this->pageIds as $pageId) {
            $ttlHours = (int)$GLOBALS['BE_USER']->getTSConfigVal('options.workspaces.previewLinkTTLHours');
            $ttlHours = ($ttlHours ? $ttlHours : 24 * 2);
            $params = 'id=' . $pageId . '&L=' . $this->sysLang . '&ADMCMD_previewWS=' . $this->workspaceId;
            $previewUrls[$pageId] = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'index.php?ADMCMD_prev=' . PreviewHook::compilePreviewKeyword($params,
                    $GLOBALS['BE_USER']->user['uid'], 60 * 60 * $ttlHours);
        }

        return $previewUrls;
    }

    function renderPreviewLinks($previewLinks)
    {
        $out = '<ol>';
        foreach ($previewLinks as $key => $previewLink) {
            $out .= '<li>' . $key . ': <a href="' . $previewLink . '" target="_new">' . $previewLink . '</a></li>';
        }
        $out .= '</ol>';

        return $out;
    }
}