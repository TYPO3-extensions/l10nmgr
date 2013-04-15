<?php
require_once(t3lib_extMgm::extPath('mvc').'mvc/view/widget/class.tx_mvc_view_widget_panelBorder.php');

class tx_l10nmgr_view_translate_borderPanel extends tx_mvc_view_widget_panelBorder{

	/**
	 * 
	 * @return void
	 */
	public function __construct(){
		parent::__construct();
		
		$extPath = t3lib_div::resolveBackPath($GLOBALS['BACK_PATH'] . t3lib_extMgm::extRelPath('l10nmgr'));
		$cssInclude = $extPath . 'templates/translate/css/panel.css';		
		$cssIncludes[] = $cssInclude;
		$this->setCSSIncludes($cssIncludes);
				
	}
}
?>
