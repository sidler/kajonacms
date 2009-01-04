<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                 *
********************************************************************************************************/


//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

include_once(_systempath_."/class_modul_downloads_archive.php");
include_once(_systempath_."/class_modul_downloads_file.php");
include_once(_systempath_."/class_modul_rating_sort_absolute.php");

/**
 * Portal-part of the downloads_toplist-element
 *
 * @package modul_downloads
 */
class class_element_downloads_toplist extends class_element_portal implements interface_portal_element {

    /**
     * Contructor
     *
     * @param mixed $arrElementData
     */
    public function __construct($objElementData) {
        $arrModule["name"]          = "element_downloads_toplist";
        $arrModule["author"]        = "sidler@mulchprod.de";
        $arrModule["moduleId"]      = _pages_elemente_modul_id_;
        $arrModule["table"]         = _dbprefix_."element_universal";
        parent::__construct($arrModule, $objElementData);
    }


    /**
     * Loads the files, sorts them and generates the output
     *
     * @return string
     */
    public function loadData() {
        $strReturn = "";

        //load the archive
        $arrFiles = class_modul_downloads_file::getAllFilesUnderFolderLevelRecursive($this->arrElementData["char1"]);
        
        $objSorter = $this->getSortAlgo($this->arrElementData["char3"]);
        $objSorter->setElementsArray($arrFiles);
        $arrFiles = $objSorter->doSorting();
        
        //var_dump($arrFiles);
        //create the elements output
        $strOuterTemplateID = $this->objTemplate->readTemplate("/element_downloads_toplist/".$this->arrElementData["char2"], "dltoplist_list");
        $strInnerTemplateID = $this->objTemplate->readTemplate("/element_downloads_toplist/".$this->arrElementData["char2"], "dltoplist_entry");
        
        $intCounter = 1;
        $strInner = "";
        foreach($arrFiles as $objOneFile) {
        	$arrTemplate = array();
        	$arrTemplate["dltoplist_pos"] = $intCounter;
        	$arrTemplate["dltoplist_link"] = _webpath_."/downloads.php?systemid=".$objOneFile->getSystemid();
        	$arrTemplate["dltoplist_name"] = $objOneFile->getName();
        	$arrTemplate["dltoplist_rating"] = $objOneFile->getFloatRating();
        	
        	$strInner .= $this->objTemplate->fillTemplate($arrTemplate, $strInnerTemplateID);
        	
            if(++$intCounter > $this->arrElementData["int1"] && $this->arrElementData["int1"] > 0)
                break;	
        }
        
        $strReturn .= $this->objTemplate->fillTemplate(array("dltoplist_entries" => $strInner), $strOuterTemplateID);
        
        return $strReturn;
    }
    
    
    /**
     * @return interface_modul_rating_sortalgo
     */
    private function getSortAlgo($strAlgo) {
    	if($strAlgo == "absolute")
    	   return new class_modul_rating_sort_absolute();
    	
    }
    
    
    


}
?>