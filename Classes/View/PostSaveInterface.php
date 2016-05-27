<?php
namespace Localizationteam\L10nmgr\View;

/**
 * PostSaveInterface $COMMENT$
 *
 * @author      Peter Russ<peter.russ@4many.net>
 * @package     TYPO3
 * @date        20150909-2127
 * @subpackage  l10nmgr
 * 
 */
interface PostSaveInterface {

    /**
     * @param array $params
     * @return void
     */
    public function postExportAction(array $params);

}
