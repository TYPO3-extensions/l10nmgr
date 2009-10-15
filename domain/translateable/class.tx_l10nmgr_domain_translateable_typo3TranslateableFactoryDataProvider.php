<?php
require_once(t3lib_extMgm::extPath('l10nmgr').'interface/interface.tx_l10nmgr_interface_translateable_translateableFactoryDataProvider.php');

class tx_l10nmgr_domain_translateable_typo3TranslateableFactoryDataProvider implements tx_l10nmgr_interface_translateable_translateableFactoryDataProvider{

	/**
	 * Internal array with disallowed doctypes
	 *
	 * @var array
	 */
	protected $disallowDoktypes;

	/**
	 * Holds the id of the workspace
	 *
	 * @var unknown_type
	 */
	protected $workspaceId;

	/**
	 * Holds the url of translateable information.
	 *
	 * @var string
	 */
	protected $siteUrl;

	/**
	 * Holds the sourcelanguage of
	 *
	 * @var tx_l10nmgr_domain_language
	 */
	protected $sourceLanguage;

	protected $targetLanguage;

	protected $relevantTables;

	protected $collectionOfRelevantPageIds;

	/**
	 * Holds the exportData object
	 *
	 * @var tx_l10nmgr_domain_export_exportData
	 */
	protected $exportData;

	/**
	 * @var tx_l10nmgr_tools
	 */
	protected $t8Tools;

	/**
	 * @var array
	 */
	protected $recordsToProcess = array();

	/**
	 * @var boolean
	 */
	protected $isFromIncludeList = false;

	/**
	 * Add single records to the export queue.
	 *
	 * @param string $tableName
	 * @param integer $recordUid
	 *
	 * @access public
	 * @return void
	 *
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 */
	public function appendRecordsToProcess($pageId, $tableName, $recordUid) {
		$this->isFromIncludeList = true;
		$this->addPageIdCollectionToRelevantPageIds(new ArrayObject(array($pageId)));
		$this->recordsToProcess[$tableName][$pageId][]['uid'] = $recordUid;
	}

	/**
	 * Constructor
	 *
	 * @param tx_l10nmgr_domain_exporter_exportData exportData object
	 */
	public function __construct(tx_l10nmgr_domain_exporter_exportData $exportData){

		$this->exportData	= $exportData;
		$l10ncfg 			= $exportData->getL10nConfigurationObject();
		$targetLanguage		= $exportData->getTranslationLanguageObject();
		$sourceLanguage		= $exportData->getSourceLanguageObject();

		$this->disallowDoktypes = array('--div--','3','255');

		$this->setWorkspaceId($GLOBALS['BE_USER']->workspace);
		$this->setSiteUrl(t3lib_div::getIndpEnv("TYPO3_SITE_URL"));
		$this->setTargetLanguage($targetLanguage);
		$this->setSourceLanguage($sourceLanguage);

		$this->t8Tools			= $this->getInitializedt8Tools($l10ncfg,$sourceLanguage);
		$this->flexFormDiff		= $this->getFlexFormDiffForTargetLanguage($l10ncfg,$targetLanguage);

		$this->tca_tables		= $this->getTCATablenames();
		$this->relevantTables	= array_intersect($this->tca_tables,$l10ncfg->getTableArray());
		$this->excludeArray		= $l10ncfg->getExcludeArray();
	}

	/**
	 * @return boolean
	 */
	public function getOnlyNewAndChanged(){
		return $this->exportData->getOnlychangedcontent();
	}
	/**
	 * Returns the related exportData object
	 *
	 * @return tx_l10nmgr_domain_exporter_exportDatat
	 */
	public function getExportData(){
		return $this->exportData;
	}

	/**
	 * @return string
	 */
	public function getSiteUrl() {
		return $this->siteUrl;
	}

	/**
	 * @return tx_l10nmgr_domain_language_language
	 */
	public function getSourceLanguage() {
		return $this->sourceLanguage;
	}

	/**
	 * @return tx_l10nmgr_domain_language_language
	 */
	public function getTargetLanguage() {
		return $this->targetLanguage;
	}

	/**
	 * @return int
	 */
	public function getWorkspaceId() {
		return $this->workspaceId;
	}

	/**
	 * @param ArrayObject $relevantTables
	 */
	public function setRelevantTables($relevantTables) {
		$this->relevantTables = $relevantTables;
	}

	/**
	 * @param string $siteUrl
	 */
	public function setSiteUrl($siteUrl) {
		$this->siteUrl = $siteUrl;
	}

	/**
	 * Method to set a Sourcelanguage
	 *
	 * @param tx_l10nmgr_domain_language_language $sourceLanguage
	 */
	public function setSourceLanguage($sourceLanguage) {
		$this->sourceLanguage = $sourceLanguage;
	}

	/**
	 * Method to set a target language for the dataProvider
	 * @param tx_l10nmgr_domain_language_language $targetLanguage
	 */
	public function setTargetLanguage($targetLanguage) {
		$this->targetLanguage = $targetLanguage;
	}

	/**
	 * Method to set a workspace id where this export is relevant for.
	 * @param int $workspaceId
	 */
	public function setWorkspaceId($workspaceId) {
		$this->workspaceId = $workspaceId;
	}

	/**
	 * Returns all relavantTables of the export
	 *
	 * @return ArrayObject
	 */
	public function getRelevantTables(){
		return $this->relevantTables;
	}

	/**
	 * Returns a collection of pageIds
	 *
	 * @return ArrayObject
	 */
	public function getRelevantPageIds(){
		return $this->collectionOfRelevantPageIds;
	}

	/**
	 * This method is a wrapper method for the old translation tool class.
	 * It determines relevant element uids by the tablename and the uid of the page
	 * where the element is stored on.
	 *
	 *  @see tx_l10nmgr_tools
	 *  @param string $tablename
	 *  @param int $pageid
	 *  @return array
	 */
	public function getRelevantElementIdsByTablenameAndPageId($tableName,$pageId){

		if ($this->isFromIncludeList === false) {
			$recordsToProcess = $this->t8Tools->getRecordsToTranslateFromTable($tableName, $pageId);
		} else {
			$recordsToProcess = $this->recordsToProcess[$tableName][$pageId];
		}

		$uids = array();
		if (is_array($recordsToProcess)) {
			foreach($recordsToProcess as $record){
				$uids[] = $record['uid'];
			}
		}

		return $uids;
	}

	/**
	 * This method is a wrapper for the old translationtools. It determines relevant
	 * informations for the translation from the TYPO3 core.
	 *
	 *  @see tx_l10nmgr_tools
	 *  @param string tablename of the element
	 *  @param int uid of the element
	 */
	public function getTranslationDetailsByTablenameAndElementId($tablename,$elementid) {
		if(!self::isArrayValueSet($this->excludeArray,$tablename,$elementid)) {
			// this is need because 'pages' and other tables need to be handled diffrent
			$tablerow = $tablename=='pages' ? t3lib_BEfunc::getRecord($tablename,$elementid) : $this->t8Tools->getSingleRecordToTranslate($tablename,$elementid);

			t3lib_BEfunc::workspaceOL($tablename,$tablerow);
			return $this->t8Tools->translationDetails($tablename,$tablerow,$this->getTargetLanguage()->getUid(),$this->flexFormDiff);
		}
	}


	/**
     * This internal method adds a collection of pageIds to the relevant pageIds
     * for the export. It internally uses the exclude array, to exclude element which should not
     * ne exported
     *
     * @param ArrayObject
	 */
	public function addPageIdCollectionToRelevantPageIds($pageIdCollection){

		if (! $this->collectionOfRelevantPageIds instanceof ArrayObject) {
			$this->collectionOfRelevantPageIds = new ArrayObject();
		}

		/**
		 * old check:
		 *
		 * if(!self::isInIncludeOrExcludeArray($excludeArray,'pages',$pageId)  && ($pageRow['l18n_cfg']&2)!=2 && !in_array($pageRow['doktype'], $this->disallowDoktypes)){
		 *
		 * $pageRow['l18n_cfg']&2)!=2 This check meens that the following options should not be checked in the backend:
		 * "Hide default translation of page" but it should be possible to translate pages where the default translation is hidden, therefore
		 * this check has been removed.
		 */
		foreach($pageIdCollection as $pageId){
			$pageRow = t3lib_BEfunc::getRecordWSOL('pages',$pageId);
			if(!self::isArrayValueSet($this->excludeArray,'pages',$pageId)  && $this->hasAllowedDoctype($pageRow)){
				$this->collectionOfRelevantPageIds->offsetSet($pageId, $pageId);
			}
		}
	}

	/**
	 * Method to check that the doctype of the page is not an disallowed doctype
	 *
	 * @param array $pageRow
	 * @return boolean
	 */
	protected function hasAllowedDoctype($pageRow){
		return !in_array($pageRow['doktype'], $this->disallowDoktypes);
	}

	/**
	 * The factory uses internally the t8tools to collect informations about a translation.
	 * This method is used to get an configured intance of the tools object
	 *
	 * @param tx_l10nmgr_domain_configuration_configuration $l10ncfg
	 * @param tx_l10nmgr_domain_language_Language $previewLanguage
	 * @return tx_l10nmgr_tools
	 */
	protected function getInitializedt8Tools($l10ncfg,$sourceLanguage = NULL){
		// Init:
		$t8Tools = t3lib_div::makeInstance('tx_l10nmgr_tools');
		$t8Tools->verbose = FALSE;	// Otherwise it will show records which has fields but none editable.
		if ($l10ncfg->getIncludeFCEWithDefaultLanguage()) {
			$t8Tools->includeFceWithDefaultLanguage=TRUE;
		}

		if($sourceLanguage instanceof tx_l10nmgr_domain_language_Language ){
			$sourceLanguageIds = $sourceLanguage->getUid();
		}

		if(!$sourceLanguageIds){
			$sourceLanguageIds = current(t3lib_div::intExplode(',',$GLOBALS['BE_USER']->getTSConfigVal('options.additionalPreviewLanguages')));
		}
		if($sourceLanguage){
			$t8Tools->previewLanguages = array($sourceLanguageIds);
		}

		return $t8Tools;
	}

	/**
	 * Helpermethod to get the flexform diff
	 *
	 * @param tx_l10nmgr_domain_configuration_configuration $l10ncfg
	 * @param tx_l10nmgr_domain_language_Language $targetLanguage
	 * @return string
	 */
	protected function getFlexFormDiffForTargetLanguage($l10ncfg,$targetLanguage){
		// FlexForm Diff data:
		if($targetLanguage instanceof tx_l10nmgr_domain_language_language){
			$flexFormDiff = unserialize($l10ncfg->getFlexFormDiff());
			$flexFormDiff = $flexFormDiff[$targetLanguage->getUid()];
		}

		return $flexFormDiff;
	}

	/**
	 * This method is used to determine all tables, configured in the TCA
	 *
	 * @return array
	 */
	protected function getTCATablenames(){
		global $TCA;
		$tca_tables = array_keys($TCA);
		return $tca_tables;
	}

	/**
	 * This method is used to determine if an element is in the list of include or excluded elements.
	 *
	 * @param array $array
	 * @param string $table
	 * @param int $id
	 * @return boolean
	 */
	private static function isArrayValueSet($array,$table,$id){
		return isset($array[$table.':'.$id]);
	}
}
?>