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
require_once(t3lib_extMgm::extPath('l10nmgr').'class.tx_l10nmgr_zip.php');

$LANG->includeLLFile('EXT:l10nmgr/cm1/locallang.xml');
require_once (PATH_t3lib.'class.t3lib_scbase.php');

class tx_cliimport_cli extends t3lib_cli {
	
	/**
	 * Constructor
	 */
    function tx_cliimport_cli () {

        // Running parent class constructor
        parent::t3lib_cli();

	$this->cli_options[] = array('--silent', 'Silent mode', "Minimum output\n");

        // Setting help texts:
        $this->cli_help['name'] = 'Localization Manager importer';        
        $this->cli_help['synopsis'] = '###OPTIONS###';
        $this->cli_help['description'] = 'Class with import functionality for l10nmgr';
        $this->cli_help['examples'] = '/.../cli_dispatch.phpsh l10nmgr_import import|importPreview|preview CATXML serverlink';
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
	$start = microtime(true);

        // get task (function)
        $task = (string)$this->cli_args['_DEFAULT'][1]; 	// 1 = import XML string, 2 = import XML file, 3 = generate source preview
        $preview = (string)$this->cli_args['_DEFAULT'][2]; 	// 0 or 1
        $xml = (string)$this->cli_args['_DEFAULT'][3];		// XML string for import
	$xml = stripslashes($xml);
	$filepath = (string)$this->cli_args['_DEFAULT'][4];	// Path to XML or ZIP file to import
	$serverlink = (string)$this->cli_args['_DEFAULT'][5];	// Serverlink from calling application

	$msg = "";
    	//$this->cli_echo($xml);
	//exit;

        if (!$task){
            $this->cli_validateArgs();
            $this->cli_help();
            exit;
        }

        // Force user to admin state 
       	$GLOBALS['BE_USER']->user['admin'] = 1;

	if ($task == '1' || $task == '3') {
		// Get workspace id from CATXML
		$wsId = $this->getWsIdFromCATXML($xml);
		if ($wsId === FALSE) {
			$this->cli_echo('No workspace ID from CATXML');
			exit;
		}

	        // Set workspace to the required workspace ID from CATXML:
       		$GLOBALS['BE_USER']->setWorkspace($wsId);

	        if ($task == '1') {          
        	    $msg.= $this->importCATXML($xml,$preview,$serverlink);            
	        } elseif ($task == '3') {
        	    $msg.= $this->previewSource($xml);            
		}
	} elseif ($task == '2') {
		$msg.= $this->importXMLFile($filepath,$preview,$serverlink);
	}

	$end = microtime(true);
	$time = $end - $start;
	$ret = "$msg".'<br>'."$time";
	$this->cli_echo($ret);
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
			$pageIds=array();
			if (empty($importManager->headerData['t3_previewId'])) {
				$pageIds = $importManager->getPidsFromCATXMLNodes($importManager->xmlNodes);
			} else {
				$pageIds[0]=$importManager->headerData['t3_previewId'];
			}
			$mkPreviewLinksClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_mkPreviewLinkService');
			$mkPreviewLinks=new $mkPreviewLinksClassName($t3_workspaceId=$importManager->headerData['t3_workspaceId'], $t3_sysLang=$importManager->headerData['t3_sysLang'], $pageIds);
			$previewLink=$mkPreviewLinks->mkSinglePreviewLink($importManager->headerData['t3_baseURL'],$serverlink);
			$out.= $previewLink;
		}

		$translationData=$factory->getTranslationDataFromCATXMLNodes($importManager->getXMLNodes());
		$translationData->setLanguage($this->sysLanguage);
		unset($importManager);

		$service->saveTranslation($l10ncfgObj,$translationData);
		if (empty($out)) { $out = 1; } //Means OK if preview = 0
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

    /**
    * importQueue which is called over cli
    *
    */
    function importXMLFile($filepath,$preview,$serverlink){
	global $LANG;
	$out ="";
	//$out.="Importiere Datei ".$filepath;

	//Unzip file if *.zip
	$info = pathinfo($filepath);
	if ($info['extension'] == "zip") {
		$unzip = new tx_l10nmgr_zip();
		$unzipRes=$unzip->extractFile($filepath);
		// unlink $filepath
		//t3lib_div::unlink_tempfile($filepath);
		// Process extracted files if ftype = xml => IMPORT
		$xmlFilesArr = $this->checkFileType($unzipRes['fileArr'],'xml');
	} elseif ($info['extension'] == "xml") {
		$xmlFilesArr[0]=$filepath;
	}

	if (!empty($xmlFilesArr)) {
	foreach ($xmlFilesArr as $xmlFile) {
		$xmlFileHead = $this->getXMLFileHead($xmlFile);
       		// Set workspace to the required workspace ID from CATXML:
		$GLOBALS['BE_USER']->setWorkspace($xmlFileHead['t3_workspaceId'][0][XMLvalue]);
		$this->sysLanguage = $xmlFileHead['t3_sysLang'][0][XMLvalue]; //set import language to t3_sysLang from XML

		$service=t3lib_div::makeInstance('tx_l10nmgr_l10nBaseService');
		$factory=t3lib_div::makeInstance('tx_l10nmgr_translationDataFactory');

		// Relevant processing of XML Import with the help of the Importmanager
		$importManagerClass=t3lib_div::makeInstanceClassName('tx_l10nmgr_CATXMLImportManager');
		$importManager=new $importManagerClass($xmlFile,$this->sysLanguage,$xml);
		if ($importManager->parseAndCheckXMLFile()===false) {
			$out.='<br/><br/>'.$importManager->getErrorMessages();
		} else {
			// Find l10n configuration record:
			$l10ncfgObj=t3lib_div::makeInstance('tx_l10nmgr_l10nConfiguration');
			$l10ncfgObj->load($importManager->headerData['t3_l10ncfg']);
			$status = $l10ncfgObj->isLoaded();
			if ($status === false) {
				$this->cli_echo("l10ncfg not loaded! Exiting...\n");
				exit;
			}
			//Delete previous translations
			$importManager->delL10N($importManager->getDelL10NDataFromCATXMLNodes($importManager->xmlNodes));

			//Make preview links
			if ($preview == 1){
				$pageIds=array();
				if (empty($importManager->headerData['t3_previewId'])) {
					$pageIds = $importManager->getPidsFromCATXMLNodes($importManager->xmlNodes);
				} else {
					$pageIds[0]=$importManager->headerData['t3_previewId'];
				}
				$mkPreviewLinksClassName=t3lib_div::makeInstanceClassName('tx_l10nmgr_mkPreviewLinkService');
				$mkPreviewLinks=new $mkPreviewLinksClassName($t3_workspaceId=$importManager->headerData['t3_workspaceId'], $t3_sysLang=$importManager->headerData['t3_sysLang'], $pageIds);
				$previewLink=$mkPreviewLinks->mkSinglePreviewLink($importManager->headerData['t3_baseURL'],$serverlink);
				$out.= $previewLink;
			}

			$translationData=$factory->getTranslationDataFromCATXMLNodes($importManager->getXMLNodes());
			$translationData->setLanguage($this->sysLanguage);
			unset($importManager);
			$service->saveTranslation($l10ncfgObj,$translationData);
		}
	}
	} else {
		$out.="2<br/>2<br/>No files to import!";
	}
	if (empty($out)) { $out = 1; } //means OK

    	// Output
	return($out);
    }

    function getXMLFileHead($filepath) {
	$fileContent    = t3lib_div::getUrl($filepath);
	$this->xmlNodes = t3lib_div::xml2tree(str_replace('&nbsp;',' ',$fileContent),3);	// For some reason PHP chokes on incoming &nbsp; in XML!

	if (!is_array($this->xmlNodes)) {
		$this->_errorMsg[] = $LANG->getLL('import.manager.error.parsing.xml2tree.message') . $this->xmlNodes;
		return false;
	}

	$headerInformationNodes = $this->xmlNodes['TYPO3L10N'][0]['ch']['head'][0]['ch'];
	if (!is_array($headerInformationNodes)) {
		$this->_errorMsg[] = $LANG->getLL('import.manager.error.missing.head.message');
		return false;
	}
	return($headerInformationNodes);
    }
    
    /**
    * Check filetype
    *
    * @param	array		$fileArr	Files to be checked
    * @param	string	$ext	File extension
    * @return	array		$passed	Files that passed test
    */
    function checkFileType($unzippedFiles,$ext) {
	foreach ($unzippedFiles as $file) {
		if (preg_match('/'.$ext.'$/',$file))  {
			$passed[].=$file;
		}
	}
	return $passed;
    }
}

// Call the functionality
$cleanerObj = t3lib_div::makeInstance('tx_cliimport_cli');
$cleanerObj->cli_main($_SERVER['argv']);

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/cli/cli.import.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/cli/cli.import.php']);
}

?>
