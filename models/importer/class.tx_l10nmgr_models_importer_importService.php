<?php
/**
 * This class is used to handle an importProcess. It uses an translateableInformation and an translatationDataObject to perform 
 * the changes in the TranslationData in the TYPO3 Core.
 *
 */
class tx_l10nmgr_models_importer_importService{
	
	/**
	 * This method performs an import base on a translateableInformation (same like an export on import time) and a translationData (values of the import file).
	 *
	 * @param tx_l10nmgr_models_translateable_translateableInformation $translateableInformaiton
	 * @param tx_models_translation_data $translationData
	 */
	public static function performImport(tx_l10nmgr_models_translateable_translateableInformation $translateableInformaiton, tx_models_translation_data $translationData){
		//this is where the tce main importing stuff goes
	}
}
?>