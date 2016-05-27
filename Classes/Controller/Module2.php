<?php
namespace Localizationteam\L10nmgr\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Kasper Skårhøj <kasperYYYY@typo3.com>
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
 * Module 'Workspace Tasks' for the 'l10nmgr' extension.
 *
 * @author  Kasper Skårhøj <kasperYYYY@typo3.com>
 */

// DEFAULT initialization of a module [BEGIN]
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Script Class for rendering the frameset
 *
 * @author     Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package    TYPO3
 * @subpackage tx_l10nmgr
 */
class Module2
{

    // Internal, static:
    var $defaultWidth = 300; // Default width of the navigation frame. Can be overridden from $TBE_STYLES['dims']['navFrameWidth'] (alternative default value) AND from User TSconfig

    // Internal, dynamic:
    var $content; // Content accumulation.

    /**
     * Creates the header and frameset for the module/submodules
     *
     * @return  void
     */
    function main()
    {
        global $BE_USER, $TBE_TEMPLATE, $TBE_STYLES;

        // Setting frame width:
        $width = $this->defaultWidth;

        // THis onload handler is a bug-fix for a possible bug in Safari browser for Mac. Posted by Jack COLE. Should not influence other browsers negatively.
        $onLoadHandler = ' onload="if (top.content.nav_frame.location.href.length == 1) {top.content.nav_frame.location=\'' . $URL_nav . '\';};"';

        $this->content .= '
			<frameset cols="' . $width . ',*"' . $onLoadHandler . '>
				<frame name="nav_frame" src="' . BackendUtility::getModuleUrl('xMOD_Module2List') . '" marginwidth="0" marginheight="0" scrolling="auto" />
				<frame name="list_frame" src="" marginwidth="0" marginheight="0" scrolling="auto" />
			</frameset>

			</html>
		';
    }

    /**
     * Outputting the accumulated content to screen
     *
     * @return  void
     */
    function printContent()
    {
        echo $this->content;
    }
}