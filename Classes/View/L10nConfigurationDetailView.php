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
use Localizationteam\L10nmgr\Model\L10nConfiguration;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * l10nmgr detail view:
 *  renders information for a l10ncfg record.
 *
 * @author  Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author  Daniel Pötzinger <development@aoemedia.de>
 * @package TYPO3
 * @subpackage tx_l10nmgr
 */
class L10nConfigurationDetailView
{
    
    /**
     * @var LanguageService
     */
    protected $languageService;
    
    /**
     * @var L10nConfiguration
     */
    var $l10ncfgObj; // Internal array (=datarow of config record)
    
    /**
     * @var $module
     */
    var $module = null;
    
    /**
     * constructor. Set the internal required objects as parameter in constructor (kind of dependency injection, and communicate the dependencies)
     *
     * @param L10nConfiguration $l10ncfgObj
     * @param DocumentTemplate $module Reference to the calling template object
     */
    function __construct($l10ncfgObj, $module)
    {
        $this->l10ncfgObj = $l10ncfgObj;
        $this->module = $module;
    }
    
    /**
     * returns HTML table with infos for the l10nmgr config.
     *  (needs valid configuration to be set)
     *
     * @return string HTML to display
     **/
    function render()
    {
        $content = '';
        
        if (!$this->_hasValidConfig()) {
            return $this->getLanguageService()->getLL('general.export.configuration.error.title');
        }
        
        $configurationSettings = '
				<table class="table table-striped table-hover">
					<tr class="t3-row-header">
						<th colspan="4">' . htmlspecialchars($this->l10ncfgObj->getData('title')) . ' [' . $this->l10ncfgObj->getData('uid') . ']</th>
					</tr>
					<tr class="db_list_normal">
						<th>' . $this->getLanguageService()->getLL('general.list.headline.depth.title') . ':</h>
						<td>' . htmlspecialchars($this->l10ncfgObj->getData('depth')) . '&nbsp;</td>
						<th>' . $this->getLanguageService()->getLL('general.list.headline.tables') . ':</th>
						<td>' . htmlspecialchars($this->l10ncfgObj->getData('tablelist')) . '&nbsp;</td>
					</tr>
					<tr class="db_list_normal">
						<th>' . $this->getLanguageService()->getLL('general.list.headline.exclude.title') . ':</th>
						<td>' . htmlspecialchars($this->l10ncfgObj->getData('exclude')) . '&nbsp;</td>
						<th>' . $this->getLanguageService()->getLL('general.list.headline.include.title') . ':</th>
						<td>' . htmlspecialchars($this->l10ncfgObj->getData('include')) . '&nbsp;</td>
					</tr>
				</table>';
        
        $content .= '<div><h2 class="uppercase">' . $this->getLanguageService()->getLL('general.export.configuration.title') . '</h2>' . $configurationSettings;
        
        return $content;
    }
    
    /**
     * checks if the internal L10nConfiguration object is valid
     *
     * @return bool
     **/
    function _hasValidConfig()
    {
        if (is_object($this->l10ncfgObj) && $this->l10ncfgObj->isLoaded()) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * getter/setter for LanguageService object
     *
     * @return LanguageService $languageService
     */
    protected function getLanguageService()
    {
        if (!$this->languageService instanceof LanguageService) {
            $this->languageService = GeneralUtility::makeInstance(LanguageService::class);
        }
        if ($this->getBackendUser()) {
            $this->languageService->init($this->getBackendUser()->uc['lang']);
        }
        
        return $this->languageService;
    }
    
    /**
     * Returns the Backend User
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
    
}