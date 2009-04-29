<?php
require_once(t3lib_extMgm::extPath('l10nmgr').'interfaces/interface.tx_l10nmgr_interaces_translateable_translateableFactoryDataProvider.php');

class tx_l10nmgr_models_translateable_typo3TranslateableFactoryDataProvider implements tx_l10nmgr_interaces_translateable_translateableFactoryDataProvider{
	
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
	 * @var tx_l10nmgr_models_language
	 */
	protected $sourceLanguage;
	
	protected $targetLanguage;
	
	protected $relevantTables;
	
	protected $collectionOfRelevantPageIds;
	
	/**
	 * Holds the exportData object
	 *
	 * @var tx_l10nmgr_models_export_exportData
	 */
	protected $exportData;
	
	public function __construct(tx_l10nmgr_models_exporter_exportData $exportData, 
							ArrayObject $pageIdCollection ){
		
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

		$this->addPageIdCollectionToRelevantPageIds($pageIdCollection);
	}
	
	/**
	 * Returns the related exportData object 
	 *
	 * @return tx_l10nmgr_models_exporter_exportDatat
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
	 * @return tx_l10nmgr_models_language_language
	 */
	public function getSourceLanguage() {
		return $this->sourceLanguage;
	}
	
	/**
	 * @return tx_l10nmgr_models_language_language
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
	 * @param tx_l10nmgr_models_language_language $sourceLanguage
	 */
	public function setSourceLanguage($sourceLanguage) {
		$this->sourceLanguage = $sourceLanguage;
	}
	
	/**
	 * Method to set a target language for the dataProvider
	 * @param tx_l10nmgr_models_language_language $targetLanguage
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
	public function getRelevantElementIdsByTablenameAndPageId($tablename,$pageid){
		$records = $this->t8Tools->getRecordsToTranslateFromTable($tablename, $pageid);
		$uids = array();
		foreach($records as $record){
			$uids[] = $record['uid'];			
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
	public function getTranslationDetailsByTablenameAndElementId($tablename,$elementid){
		if(!self::isInIncludeOrExcludeArray($this->excludeArray,$tablename,$elementid)){	
			//this is need because 'pages' and other tables need to be handled diffrent
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
	protected function addPageIdCollectionToRelevantPageIds($pageIdCollection){
		$this->collectionOfRelevantPageIds = new ArrayObject();
					
		/**
		* old check:
		* 
		*if(!self::isInIncludeOrExcludeArray($excludeArray,'pages',$pageId)  && ($pageRow['l18n_cfg']&2)!=2 && !in_array($pageRow['doktype'], $this->disallowDoktypes)){
		* 
		* $pageRow['l18n_cfg']&2)!=2 This check meens that the following options should not be checked in the backend:
		* "Hide default translation of page" but it should be possible to translate pages where the default translation is hidden, therefore
		* this check has been removed.
		*/
		
		foreach($pageIdCollection as $pageId){
			$pageRow = t3lib_BEfunc::getRecordWSOL('pages',$pageId);
			if(!self::isInIncludeOrExcludeArray($this->excludeArray,'pages',$pageId)  && $this->hasAllowedDoctype($pageRow)){
				$this->collectionOfRelevantPageIds->append($pageId);	
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
	 * @param tx_l10nmgr_models_configuration_configuration $l10ncfg
	 * @param tx_l10nmgr_models_language_Language $previewLanguage
	 * @return tx_l10nmgr_tools
	 */
	protected function getInitializedt8Tools($l10ncfg,$sourceLanguage = NULL){
		// Init:
		$t8Tools = t3lib_div::makeInstance('tx_l10nmgr_tools');
		$t8Tools->verbose = FALSE;	// Otherwise it will show records which has fields but none editable.
		if ($l10ncfg->getIncludeFCEWithDefaultLanguage()) {
			$t8Tools->includeFceWithDefaultLanguage=TRUE;
		}
		
		if($sourceLanguage instanceof tx_l10nmgr_models_language_Language ){
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
	 * @param tx_l10nmgr_models_configuration_configuration $l10ncfg
	 * @param tx_l10nmgr_models_language_Language $targetLanguage
	 * @return string
	 */
	protected function getFlexFormDiffForTargetLanguage($l10ncfg,$targetLanguage){
		// FlexForm Diff data:
		$flexFormDiff = unserialize($l10ncfg->getFlexFormDiff());
		$flexFormDiff = $flexFormDiff[$targetLanguage->getUid()];
		
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
	private static function isInIncludeOrExcludeArray($array,$table,$id){
		return isset($array[$table.':'.$id]);
	}
	
		
	/**
	 * just copyed to have the old code in place
	 * 
	 * @deprecated 
	 *
	 */
	protected function calculateInternalAccumulatedInformationsArray() {
//		global $TCA;
//		$tree=$this->tree;
//		$l10ncfg=$this->l10ncfg;
//		$accum = array();
//		$sysLang=$this->sysLang;
//
//			// FlexForm Diff data:
//		$flexFormDiff = unserialize($l10ncfg['flexformdiff']);
//		$flexFormDiff = $flexFormDiff[$sysLang];
//
//		$excludeIndex = array_flip(t3lib_div::trimExplode(',',$l10ncfg['exclude'],1));
//		$tableUidConstraintIndex = array_flip(t3lib_div::trimExplode(',',$l10ncfg['tableUidConstraint'],1));
//
//			// Init:
//		$t8Tools = t3lib_div::makeInstance('tx_l10nmgr_tools');
//		$t8Tools->verbose = FALSE;	// Otherwise it will show records which has fields but none editable.
//		if ($l10ncfg['incfcewithdefaultlanguage']==1) {
//			$t8Tools->includeFceWithDefaultLanguage=TRUE;
//		}
//
//			// Set preview language (only first one in list is supported):
//		if ($this->forcedPreviewLanguage!='') {
//			$previewLanguage=$this->forcedPreviewLanguage;
//		}
//		else {
//			$previewLanguage = current(t3lib_div::intExplode(',',$GLOBALS['BE_USER']->getTSConfigVal('options.additionalPreviewLanguages')));
//		}
//		if ($previewLanguage)	{
//			$t8Tools->previewLanguages = array($previewLanguage);
//		}
//
//			// Traverse tree elements:
//		foreach($tree->tree as $treeElement)	{
//
//			$pageId = $treeElement['row']['uid'];
//			if (!isset($excludeIndex['pages:'.$pageId]) && ($treeElement['row']['l18n_cfg']&2)!=2 && !in_array($treeElement['row']['doktype'], $this->disallowDoktypes) )	{
//
//				$accum[$pageId]['header']['title']	= $treeElement['row']['title'];
//				$accum[$pageId]['header']['icon']	= $treeElement['HTML'];
//				$accum[$pageId]['header']['prevLang'] = $previewLanguage;
//				$accum[$pageId]['items'] = array();
//
//					// Traverse tables:
//				foreach($TCA as $table => $cfg)	{
//
//						// Only those tables we want to work on:
//					if (t3lib_div::inList($l10ncfg['tablelist'], $table))	{
//
//						if ($table === 'pages')	{
//							$accum[$pageId]['items'][$table][$pageId] = $t8Tools->translationDetails('pages',t3lib_BEfunc::getRecordWSOL('pages',$pageId),$sysLang, $flexFormDiff);
//							$this->_increaseInternalCounters($accum[$pageId]['items'][$table][$pageId]['fields']);
//						} else {
//							$allRows = $t8Tools->getRecordsToTranslateFromTable($table, $pageId);
//
//							if (is_array($allRows))	{
//								if (count($allRows))	{
//										// Now, for each record, look for localization:
//									foreach($allRows as $row)	{
//										t3lib_BEfunc::workspaceOL($table,$row);
//										if (is_array($row) && count($tableUidConstraintIndex) > 0) {
//											if (is_array($row) && isset($tableUidConstraintIndex[$table.':'.$row['uid']]))	{
//												$accum[$pageId]['items'][$table][$row['uid']] = $t8Tools->translationDetails($table,$row,$sysLang,$flexFormDiff);
//												$this->_increaseInternalCounters($accum[$pageId]['items'][$table][$row['uid']]['fields']);
//											}
//										}else if (is_array($row) && !isset($excludeIndex[$table.':'.$row['uid']]))	{
//											$accum[$pageId]['items'][$table][$row['uid']] = $t8Tools->translationDetails($table,$row,$sysLang,$flexFormDiff);
//											$this->_increaseInternalCounters($accum[$pageId]['items'][$table][$row['uid']]['fields']);
//										}
//									}
//								}
//							}
//						}
//					}
//				}
//			} 
//		}
//
//
//		$includeIndex = array_unique(t3lib_div::trimExplode(',',$l10ncfg['include'],1));
//		foreach($includeIndex as $recId)	{
//			list($table, $uid) = explode(':',$recId);
//			$row = t3lib_BEfunc::getRecordWSOL($table, $uid);
//			if (count($row))	{
//				$accum[-1]['items'][$table][$row['uid']] = $t8Tools->translationDetails($table,$row,$sysLang,$flexFormDiff);
//				$this->_increaseInternalCounters($accum[-1]['items'][$table][$row['uid']]['fields']);
//			}
//		}
//
//		$this->_accumulatedInformations=$accum;
	}		
}
?>