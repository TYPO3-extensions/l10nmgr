<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2008 Daniel Zielinski (d.zielinski@l10ntech.de)
*  All rights reserved
*
*  [...]
*
*/

if (!defined('TYPO3_cliMode'))  die('You cannot run this script directly!');

// Include basis cli class
require_once(PATH_t3lib.'class.t3lib_admin.php');
require_once(PATH_t3lib.'class.t3lib_cli.php');

require_once(PATH_typo3.'init.php');
require_once(PATH_typo3.'template.php');

require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nConfiguration.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_l10nBaseService.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_translationData.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_translationDataFactory.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_CATXMLImportManager.php');
require_once(t3lib_extMgm::extPath('l10nmgr').'models/class.tx_l10nmgr_mkPreviewLinkService.php');

$LANG->includeLLFile('EXT:l10nmgr/cm1/locallang.xml');
require_once (PATH_t3lib.'class.t3lib_scbase.php');

class tx_cliimport_cli extends t3lib_cli {
	
	/**
	 * Constructor
	 */
    function tx_cliimport_cli () {

        // Running parent class constructor
        parent::t3lib_cli();

        // Setting help texts:
        $this->cli_help['name'] = 'Localization Manager importer';        
        $this->cli_help['synopsis'] = '###OPTIONS###';
        $this->cli_help['description'] = 'Class with import functionality for l10nmgr';
        $this->cli_help['examples'] = '/.../cli_dispatch.phpsh l10nmgr import|importPreview|preview CATXML serverlink';
        $this->cli_help['author'] = 'Daniel Zielinski - L10Ntech.de, (c) 2008';
    }

    /**
     * CLI engine
     *
     * @param    array        Command line arguments
     * @return    string
     */
    function cli_main($argv) {

	// Performance measuring
	$time_start = microtime(true);

        // get task (function)
        $task = (string)$this->cli_args['_DEFAULT'][1];
        $xml = (string)$this->cli_args['_DEFAULT'][2];
	$xml = stripslashes($xml);
	$serverlink = (string)$this->cli_args['_DEFAULT'][3];
	$msg = "";
    	//$this->cli_echo($xml);
	//exit;

        if (!$task){
            $this->cli_validateArgs();
            $this->cli_help();
            exit;
        }

	// Get workspace id from CATXML
	$wsId = $this->getWsIdFromCATXML($xml);
	if ($wsId === FALSE) {
		$this->cli_echo('No workspace ID from CATXML');
		exit;
	}

        // Force user to admin state 
        $GLOBALS['BE_USER']->user['admin'] = 1;

        // Set workspace to the required workspace ID from CATXML:
       	$GLOBALS['BE_USER']->setWorkspace($wsId);

        if ($task == 'import') {          
            $msg.= $this->importCATXML($xml,$preview=0,$serverlink);            
        } elseif ($task == 'importPreview') {
            $msg.= $this->importCATXML($xml,$preview=1,$serverlink);            
        } elseif ($task == 'preview') {
            $msg.= $this->previewSource($xml);            
	}

	$time_end = microtime(true);
	$time = $time_end - $time_start;
	$this->cli_echo($msg.'<br>'.$time);
    }
    
    /**
    * importCATXML which is called over cli
    *
    */
    function importCATXML($xml,$preview,$serverlink){
        
	global $LANG;
	$out = "";

	$service=t3lib_div::makeInstance('tx_l10nmgr_l10nBaseService');
	$factory=t3lib_div::makeInstance('tx_l10nmgr_translationDataFactory');

	$importManagerClass=t3lib_div::makeInstanceClassName('tx_l10nmgr_CATXMLImportManager');
	$importManager=new $importManagerClass($uploadedTempFile,$this->sysLanguage,$xml);

	//Parse and check XML, load header data
	if ($importManager->parseAndCheckXMLString()===false) {
		$tmp= var_export($importManager->headerData,true);
		$tmp = str_replace("\n", "", $tmp);
		$error.= $tmp;
        	$error.= $LANG->getLL('import.manager.error.parsing.xmlstring.message');
    		$this->cli_echo($error);
		exit;
        } else {

		// Find l10n configuration record:
		$l10ncfgObj=t3lib_div::makeInstance('tx_l10nmgr_l10nConfiguration');
		$l10ncfgObj->load($importManager->headerData['t3_l10ncfg']);
		$status = $l10ncfgObj->isLoaded();
		if ($status === false) {
			$this->cli_echo("l10ncfg not loaded! Exiting...\n");
			exit;
		}

		//Do import...
		//$this->cli_echo("Doing import now\n");

		$this->sysLanguage = $importManager->headerData['t3_sysLang']; //set import language to t3_sysLang from XML

		//Delete previous translations
		$importManager->delL10N($importManager->getDelL10NDataFromCATXMLNodes($importManager->xmlNodes));

		//Make preview links
		if ($preview == 1){
			$pageIds = $importManager->getPidsFromCATXMLNodes($importManager->xmlNodes);
			$mkPreviewLinksClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_mkPreviewLinkService');
			$mkPreviewLinks=new $mkPreviewLinksClassName($t3_workspaceId=$importManager->headerData['t3_workspaceId'], $t3_sysLang=$importManager->headerData['t3_sysLang'], $pageIds);
			$previewLink=$mkPreviewLinks->mkSinglePreviewLink($importManager->headerData['t3_baseURL'],$serverlink);
			$out.= $previewLink;
		}

		$translationData=$factory->getTranslationDataFromCATXMLNodes($importManager->getXMLNodes());
		$translationData->setLanguage($this->sysLanguage);
		unset($importManager);

		$service->saveTranslation($l10ncfgObj,$translationData);
		if (empty($out)) { $out = 1; }
    		return($out);
	}
    }
    
    /**
    * Get workspace ID from XML (quick & dirty)
    *
    */
    function getWsIdFromCATXML($xml){
	preg_match('/<t3_workspaceId>([^<]+)/',$xml,$matches);
	if (!empty($matches)) {
		return $matches[1];
	} else {
		return FALSE;
	}
    }

    /**
    * previewSource which is called over cli
    *
    */
    function previewSource($xml){

	global $LANG;
	$out = "";

	$service=t3lib_div::makeInstance('tx_l10nmgr_l10nBaseService');
	$factory=t3lib_div::makeInstance('tx_l10nmgr_translationDataFactory');

	$importManagerClass=t3lib_div::makeInstanceClassName('tx_l10nmgr_CATXMLImportManager');
	$importManager=new $importManagerClass($uploadedTempFile,$this->sysLanguage,$xml);

	//Parse and check XML, load header data
	if ($importManager->parseAndCheckXMLString()===false) {
		$tmp= var_export($importManager->headerData,true);
		$tmp = str_replace("\n", "", $tmp);
		$error.= $tmp;
        	$error.= $LANG->getLL('import.manager.error.parsing.xmlstring.message');
    		$this->cli_echo($error);
        } else {
		$pageIds = $importManager->getPidsFromCATXMLNodes($importManager->xmlNodes);
		$mkPreviewLinksClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_mkPreviewLinkService');
		$mkPreviewLinks=new $mkPreviewLinksClassName($t3_workspaceId=$importManager->headerData['t3_workspaceId'], $t3_sysLang=$importManager->headerData['t3_sysLang'], $pageIds);
		//Only valid if source language = default language (id=0)
		$previewLink=$mkPreviewLinks->mkSingleSrcPreviewLink($importManager->headerData['t3_baseURL'],$srcLang=0);
		$out.= $previewLink;
	}
    	
    	// Output
	return($out);
    }
}

// Call the functionality
$cleanerObj = t3lib_div::makeInstance('tx_cliimport_cli');
$cleanerObj->cli_main($_SERVER['argv']);

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/cli/cli.import.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/cli/cli.import.php']);
}

?>
