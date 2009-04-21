<?php
require_once t3lib_extMgm::extPath ( 'mvc' ) . 'mvc/view/widget/class.tx_mvc_view_widget_progress.php';

class tx_l10nmgr_view_export_progress extends tx_mvc_view_widget_progress{

	
	protected static $jsloaded;
		
	protected function preRenderProcessing() {
		if(!self::$jsloaded){
			$this->addJavaScriptInclude ( 'EXT:l10nmgr/res/contrib/jquery-1.2.3.js',false );
			$this->addJavaScriptInclude ( 'EXT:l10nmgr/res/js/export.js',false );
			
			$ajaxLink 		= $this->linkCreator->getAjaxActionLink ( 'ajaxDoExportRun' );
			$progressUrl 	= $ajaxLink->useOverruledParameters()->makeUrl ();
			
			$this->addJavascript ( '			
				var tx_l10nmgr_ajaxDoExportRunUrl=\'' . $progressUrl . '\';
				
			' );
			
			self::$jsloaded = true;
			
		}
	}

}


?>