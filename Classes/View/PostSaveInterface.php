<?php
namespace Localizationteam\L10nmgr\View;

/**
 * PostSaveInterface $COMMENT$
 *
 * @authorPeter Russ<peter.russ@4many.net>
 * @packageTYPO3
 * @date20150909-2127
 * @subpackage l10nmgr
 */
interface PostSaveInterface
{
    /**
     * @param array $params
     *
     * @return void
     */
    public function postExportAction(array $params);
}
