<?php
namespace Localizationteam\L10nmgr\View;

use TYPO3\CMS\Core\Utility\MathUtility;

class Template
{

    /**
     * @var  array $registryData Store all available data used by the template file
     */
    var $registryData = array();

    /**
     * @var  string $templateFile Relative path to the template file
     */
    var $templateFile = '';

    /**
     * @var  template $document Modul template object
     */
    var $document = null;

    /**
     * @var  integer $pageId Page id of parent page clicked in the tree
     */
    var $pageId = 0;

    /**
     *
     *
     * @param  array $registryData All available data
     * @param  string $templateFile Relative path to the template file
     * @access  public
     * @return  void
     */
    function Template($registryData, $templateFile)
    {

        $this->registryData = (is_array($registryData)) ? $registryData : array();
        $this->templateFile = $templateFile;
    }

    /**
     * Build the HTML based template view
     *
     * @access  public
     * @return  string    HTML based outputOA
     */
    function render()
    {
        $content = '';

        ob_start();
        require($this->templateFile);
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Return the availabel data
     *
     * @access  public
     * @return  array
     */
    function getRegistryData()
    {
        return $this->registryData;
    }

    /**
     * Get the modul document object
     *
     * @access  public
     * @return  template    Modul template object
     */
    function getDocument()
    {
        return $this->document;
    }

    /**
     * Set wherever you want to use the document object
     *
     * @param  template $document Modul template object
     * @access  public
     * @return  void
     */
    function setDocument($document)
    {
        $this->document = (is_object($document)) ? $document : null;
    }

    /**
     * Get the page id of page clicked in the tree
     *
     * @access  public
     * @return  integer
     */
    function getPageId()
    {
        return $this->pageId;
    }

    /**
     * Set wherever you want to use the parent page id clicked in the tree
     *
     * @param  integer $pid
     * @access  public
     * @return  void
     */
    function setPageId($pid)
    {
        $this->pageId = MathUtility::convertToPositiveInteger($pid);
    }
}