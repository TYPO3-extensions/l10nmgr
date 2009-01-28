<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2009 Daniel Zielinski (d.zielinski@l10ntech.de)
*  All rights reserved
*
*  [...]
*
*/

if (!defined('TYPO3_cliMode'))  die('You cannot run this script directly!');

// Include basis cli class
require_once(PATH_t3lib.'class.t3lib_admin.php');
require_once(PATH_t3lib.'class.t3lib_cli.php');

//require_once(PATH_typo3.'init.php');
require_once(PATH_typo3.'template.php');

$extPath = t3lib_extMgm::extPath('l10nmgr');

require_once($extPath.'views/class.tx_l10nmgr_l10ncfgDetailView.php');
require_once($extPath.'views/class.tx_l10nmgr_l10nHTMLListView.php');
require_once($extPath.'views/excelXML/class.tx_l10nmgr_excelXMLView.php');
require_once($extPath.'views/CATXML/class.tx_l10nmgr_CATXMLView.php');
require_once($extPath.'views/class.tx_l10nmgr_abstractExportView.php');

require_once($extPath.'models/class.tx_l10nmgr_l10nConfiguration.php');
require_once($extPath.'models/class.tx_l10nmgr_l10nBaseService.php');
require_once($extPath.'models/class.tx_l10nmgr_translationData.php');
require_once($extPath.'models/class.tx_l10nmgr_translationDataFactory.php');
require_once($extPath.'models/class.tx_l10nmgr_l10nBaseService.php');

require_once(PATH_t3lib.'class.t3lib_parsehtml_proc.php');

class tx_cliexport_cli extends t3lib_cli {
	
	/**
	 * Constructor
	 */
    function tx_cliexport_cli () {

        // Running parent class constructor
        parent::t3lib_cli();

	// Adding options to help archive:
	$this->cli_options[] = array('--format', 'Format for export of tranlatable data', "The value of level can be:\n  CATXML = XML for translation tools (DEFAULT)\n  EXCEL = Microsoft XML format \n");
	$this->cli_options[] = array('--config', 'Localization Manager configurations', "UIDs of the localization manager configurations to be used for export. Comma seperated values, no spaces.\nDefault is EXTCONF which means values are taken from extension configuration.\n");
	$this->cli_options[] = array('--target', 'Target languages', "UIDs for the target languages used during export. Comma seperated values, no spaces. Default is 0. In that case UIDs are taken from extension configuration.\n");
	$this->cli_options[] = array('--workspace', 'Workspace ID', "UID of the workspace used during export. Default = 0\n");
	$this->cli_options[] = array('--hidden', 'Do not export hidden contents', "The values can be: \n TRUE = Hidden content is skipped\n FALSE = Hidden content is exported. Default is FALSE.\n");
	$this->cli_options[] = array('--updated', 'Export only new/updated contents', "The values can be: \n TRUE = Only new/updated content is exported\n FALSE = All content is exported (default)\n");

        // Setting help texts:
        $this->cli_help['name'] = 'Localization Manager exporter';        
        $this->cli_help['synopsis'] = '###OPTIONS###';
        $this->cli_help['description'] = 'Class with export functionality for l10nmgr';
        $this->cli_help['examples'] = '/.../cli_dispatch.phpsh l10nmgr_export --format=CATXML --config=l10ncfg --target=tlangs --workspace=wsid --hidden=TRUE --updated=FALSE';
        $this->cli_help['author'] = 'Daniel Zielinski, (c) 2009';
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
	
	// Load the configuration
	$this->loadExtConf();

        // get format (CATXML,EXCEL)
        //$format = (string)$this->cli_args['_DEFAULT'][1];
	$format = isset($this->cli_args['--format']) ? $this->cli_args['--format'][0] : 'CATXML';

	// get l10ncfg command line takes precedance over extConf
        //$l10ncfg = (string)$this->cli_args['_DEFAULT'][2];
	$l10ncfg = isset($this->cli_args['--config']) ? $this->cli_args['--config'][0] : 'EXTCONF';
	if ($l10ncfg !== "EXTCONF") {
		//export single
		$l10ncfgs = split(",",$l10ncfg);
	} elseif (!empty($this->lConf['l10nmgr_cfg'])) {
		//export multiple
		$l10ncfgs = split(",",$this->lConf['l10nmgr_cfg']);
	} else {
		$this->cli_echo('No localization configuration given!'."\n");
		exit;
	}
	
	// get traget languages
	//$tlang = (string)$this->cli_args['_DEFAULT'][3]; //extend to list of target languages!
	$tlang = isset($this->cli_args['--target']) ? $this->cli_args['--target'][0] : '0';
	if ($tlang !== "0") {
		//export single
		$tlangs = split(",",$tlang);
	} elseif (!empty($this->lConf['l10nmgr_tlangs'])) {
		//export multiple
		$tlangs = split(",",$this->lConf['l10nmgr_tlangs']);
	} else {
		$this->cli_echo('No target language ID given!'."\n");
		exit;
	}

	// get workspace ID
	//$wsId = (string)$this->cli_args['_DEFAULT'][4];
	$wsId = isset($this->cli_args['--workspace']) ? $this->cli_args['--workspace'][0] : '0';
	if (t3lib_div::testInt($wsId)===FALSE) {
		$this->cli_echo('Workspace ID is not an integer!'."\n"); 
		exit;
	}
	$msg = "";

        if (!$format){
            $this->cli_validateArgs();
            $this->cli_help();
            exit;
        }

        // Force user to admin state 
        $GLOBALS['BE_USER']->user['admin'] = 1;

        // Set workspace to the required workspace ID from CATXML:
       	$GLOBALS['BE_USER']->setWorkspace($wsId);

        if ($format == 'CATXML') {          
		foreach ($l10ncfgs as $l10ncfg){
			if (t3lib_div::testInt($l10ncfg)===FALSE) {
				$this->cli_echo('Localization Manager ID is not an integer!'."\n"); 
				exit;
			}
			foreach ($tlangs as $tlang){
				if (t3lib_div::testInt($tlang)===FALSE) {
					$this->cli_echo('Target language ID is not an integer!'."\n"); 
					exit;
				}
            			$msg.= $this->exportCATXML($l10ncfg,$tlang);            
			}
		}
        } elseif ($format == 'EXCEL') {
            $msg.= "Not yet implemented!";
        } 

	$time_end = microtime(true);
	$time = $time_end - $time_start;
	$this->cli_echo($msg."\n".$time."\n");
    }
    
    /**
    * exportCATXML which is called over cli
    *
    */
    function exportCATXML($l10ncfg,$tlang){
        
	$error = "";

	// Load the configuration
	$this->loadExtConf();
	
	$l10nmgrCfgObj = t3lib_div::makeInstance( 'tx_l10nmgr_l10nConfiguration' );
	$l10nmgrCfgObj->load($l10ncfg);
	if ($l10nmgrCfgObj->isLoaded()) {

		$l10nmgrXML = t3lib_div::makeInstanceClassName( 'tx_l10nmgr_CATXMLView' );
		$l10nmgrGetXML=new $l10nmgrXML($l10nmgrCfgObj,$tlang);

		$onlyChanged = isset($this->cli_args['--updated']) ? $this->cli_args['--updated'][0] : 'FALSE';
		if ($onlyChanged === "TRUE") {
			$l10nmgrGetXML->setModeOnlyChanged();
	        }
		$hidden = isset($this->cli_args['--hidden']) ? $this->cli_args['--hidden'][0] : 'FALSE';
		if ($hidden === "TRUE") {
			$GLOBALS['BE_USER']->uc['moduleData']['xMOD_tx_l10nmgr_cm1']['noHidden']=TRUE;
			$l10nmgrGetXML->setModeNoHidden();
		}
		//Check the export
		//if ((t3lib_div::_POST('check_exports')=='1') && ($viewClass->checkExports() == FALSE)) {
		//	$info .= '<br />'.$this->doc->icons(2).$LANG->getLL('export.process.duplicate.message');
		//	$info .= $viewClass->renderExports();
		//} else {
			//$viewClass->saveExportInformation();
		//}
		$xmlFileName = PATH_site . 'uploads/tx_l10nmgr/jobs/' . $l10nmgrGetXML->getFileName();
		$xmlContent = $l10nmgrGetXML->render();
		$writeXmlFile = t3lib_div::writeFile($xmlFileName, $xmlContent );

		// If FTP option is set upload files to remote server
		if (file_exists($xmlFileName)) {
			$error.= $this->ftpUpload($xmlFileName,$l10nmgrGetXML->getFileName());
		} else {
			$error.= "FTP upload error: File does not exist";
		}

		//$removeXmlFile = t3lib_div::unlink_tempfile($xmlFileName);
	} else {
		$error.= 'Localization Manager object not loaded!'."\n";
	}

    	return($error);
    }

    /**
     * The function loadExtConf loads the extension configuration.
     * @return      void
     *
     */
    function ftpUpload($xmlFileName,$filename) {

	$connection = ftp_connect($this->lConf['ftp_server']) or die("Connection failed");
	if ($connection) {
		if (@ftp_login($connection, $this->lConf['ftp_server_username'], $this->lConf['ftp_server_password'])) { 
			if(ftp_put($connection, $this->lConf['ftp_server_path'].$filename, $xmlFileName, FTP_BINARY) or die("Transfer failed")) {
				ftp_close($connection) or die("Couldn't close connection");
			} else {
				$error.= "FTP upload error: Couldn't upload file".$this->lConf['ftp_server_path'].$filename."\n";
			}
		} else {
			$error.= "FTP error: Couldn't connect as ".$this->lConf['ftp_server_username']."\n";
			ftp_close($connection) or die("Couldn't close connection");
		}
	} else {
			$error.= "FTP error: Connection failed!";
	}
	return $error;
    }

    /**
     * The function loadExtConf loads the extension configuration.
     * @return      void
     *
     */
    function loadExtConf() {
          // Load the configuration
         $this->lConf = unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['l10nmgr'] );
    }

    
}

// Call the functionality
$cleanerObj = t3lib_div::makeInstance('tx_cliexport_cli');
$cleanerObj->cli_main($_SERVER['argv']);

?>
