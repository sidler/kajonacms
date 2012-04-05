<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_carrier.php 4059 2011-08-09 14:52:41Z sidler $                                            *
********************************************************************************************************/

/**
 * Portal-part of the gallery-element
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 */
class class_element_gallery_portal extends class_element_portal implements interface_portal_element {


    /**
     * Contructor
     *
     * @param $objElementData
     */
	public function __construct($objElementData) {
		parent::__construct($objElementData);
		$this->setArrModuleEntry("table", _dbprefix_."element_gallery");

        //we support ratings, so add cache-busters
        $this->setStrCacheAddon(getCookie("kj_ratingHistory"));
	}


    /**
     * Loads the gallery-class and passes control
     *
     * @return string
     */
	public function loadData() {
		$strReturn = "";

        $objMediamanagerModule = class_module_system_module::getModuleByName("mediamanager");
		if($objMediamanagerModule != null) {

            $this->arrElementData["repo_id"] = $this->arrElementData["gallery_id"];
            $this->arrElementData["repo_elementsperpage"] = $this->arrElementData["gallery_imagesperpage"];
            $this->arrElementData["repo_template"] = $this->arrElementData["gallery_template"];

    		$objGallery = $objMediamanagerModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objGallery->action();
		}

		return $strReturn;
	}

    public function getNavigationEntries() {
        $arrData = $this->getElementContent($this->getSystemid());

        $arrData["repo_id"] = $arrData["gallery_id"];
        $arrData["repo_elementsperpage"] = $arrData["gallery_imagesperpage"];
        $arrData["repo_template"] = $arrData["gallery_template"];

        $objDownloadsModule = class_module_system_module::getModuleByName("mediamanager");

        if($objDownloadsModule != null) {

            /** @var $objDownloads class_module_mediamanager_portal */
            $objDownloads = $objDownloadsModule->getPortalInstanceOfConcreteModule($arrData);
            $arrReturn = $objDownloads->getNavigationNodes();

            return $arrReturn;
        }

        return false;
    }

}