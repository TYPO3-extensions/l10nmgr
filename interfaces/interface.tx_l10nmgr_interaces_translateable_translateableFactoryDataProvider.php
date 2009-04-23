<?php
interface tx_l10nmgr_interaces_translateable_translateableFactoryDataProvider{
	
	/**
	 * The implementation of the method should return an ArrayObject 
	 * with all relevant tablenames.
	 *
	 * @return ArrayObject
	 */
	public function getRelevantTables();

	/**
	 * Sould return an ArrayObject with all relevant pageIds.
	 * 
	 * @return ArrayObject
	 *
	 */
	public function getRelevantPageIds();
	
	/**
	 * Should return an ArrayObject with all relevant elementIds
	 *
	 * @param string $tablename
	 * @param int $pageid
	 * @return ArrayObject collection with element its
	 */
	public function getRelevantElementIdsByTablenameAndPageId($tablename,$pageid);
	
	/**
	 * Should return an ArrayObject with all relevant TranslationDetails
	 *
	 * The translationDetail result should have the following structure:
	 * 
	 * 
	 * @param string $tablename
	 * @param int $elementid
	 * @return array
	 */
	public function getTranslationDetailsByTablenameAndElementId($tablename,$elementid);

}	
?>