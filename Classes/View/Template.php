<?php
namespace Localizationteam\L10nmgr\View;

use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Core\Utility\MathUtility;

class Template
{
    /**
     * @var array $registryData Store all available data used by the template file
     */
    protected $registryData = array();
    /**
     * @var string $templateFile Relative path to the template file
     */
    protected $templateFile = '';
    /**
     * @var DocumentTemplate $document Modul template object
     */
    protected $document = null;
    /**
     * @var integer $pageId Page id of parent page clicked in the tree
     */
    protected $pageId = 0;

    /**
     * @param array $registryData All available data
     * @param string $templateFile Relative path to the template file
     *
     * @access public
     * @return void
     */
    public function Template($registryData, $templateFile)
    {
        $this->registryData = (is_array($registryData)) ? $registryData : array();
        $this->templateFile = $templateFile;
    }

    /**
     * Build the HTML based template view
     *
     * @access public
     * @return string HTML based outputOA
     */
    public function render()
    {
        ob_start();
        require($this->templateFile);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * Return the availabel data
     *
     * @access public
     * @return array
     */
    public function getRegistryData()
    {
        return $this->registryData;
    }

    /**
     * Get the modul document object
     *
     * @access public
     * @return DocumentTemplate Modul template object
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set wherever you want to use the document object
     *
     * @param DocumentTemplate $document Modul template object
     *
     * @access public
     * @return void
     */
    public function setDocument($document)
    {
        $this->document = (is_object($document)) ? $document : null;
    }

    /**
     * Get the page id of page clicked in the tree
     *
     * @access public
     * @return integer
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * Set wherever you want to use the parent page id clicked in the tree
     *
     * @param integer $pid
     *
     * @access public
     * @return void
     */
    public function setPageId($pid)
    {
        $this->pageId = MathUtility::convertToPositiveInteger($pid);
    }
}