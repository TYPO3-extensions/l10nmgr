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


class tx_l10nmgr_models_exporter_workflowState extends tx_mvc_ddd_abstractDbObject {

	/**
	 * Constants representing the states
	 *
	 * Convention for own states: <extensionkey>_<state>
	 */
	const WORKFLOWSTATE_EXPORTING = 'l10nmgr_exporting';
	const WORKFLOWSTATE_EXPORTED = 'l10nmgr_exported';

	const WORKFLOWSTATE_IMPORTING = 'l10nmgr_importing';
	const WORKFLOWSTATE_IMPORTED = 'l10nmgr_imported';

	// TODO: feld muss varchar sein

	/**
	 * Initialisize the database object with
	 * the table name of current object
	 *
	 * @access     public
	 * @return     string
	 */
	public static function getTableName() {
		return 'tx_l10nmgr_workflowstates';
	}

}

?>