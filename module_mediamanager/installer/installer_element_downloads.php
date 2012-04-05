<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: installer_downloads.php 4161 2011-10-29 12:03:12Z sidler $                                      *
********************************************************************************************************/

/**
 * Installer to install the downloads-module
 *
 * @package module_mediamanager
 */
class class_installer_element_downloads extends class_installer_base implements interface_installer {

	public function __construct() {
		$this->setArrModuleEntry("version", "3.4.9");
		$this->setArrModuleEntry("name", "element_downloads");
		$this->setArrModuleEntry("name_lang", "Element Downloads");
		$this->setArrModuleEntry("moduleId", _mediamanager_module_id_);
		parent::__construct();
	}

	public function getNeededModules() {
	    return array("system", "pages", "mediamanager");
	}

    public function getMinSystemVersion() {
	    return "3.4.1";
	}

	public function hasPostInstalls() {
        $objElement = class_module_pages_element::getElement("downloads");
        if($objElement === null)
            return true;

        return false;
	}


    public function install() {
		return "";
	}

    public function hasPostUpdates() {
        $objElement = null;
        try {
            $objElement = class_module_pages_element::getElement("downloads");
            if($objElement != null && version_compare($this->arrModule["version"], $objElement->getStrVersion(), ">"))
                return true;
        }
        catch (class_exception $objEx)  {
        }

        return false;
    }

	public function postInstall() {
		$strReturn = "";

		//Table for page-element
		$strReturn .= "Installing downloads-element table...\n";

		$arrFields = array();
		$arrFields["content_id"] 		= array("char20", false);
		$arrFields["download_id"] 		= array("char20", true);
		$arrFields["download_template"] = array("char254", true);
		$arrFields["download_amount"]   = array("int", true);

		if(!$this->objDB->createTable("element_downloads", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering downloads-element...\n";
        if(class_module_pages_element::getElement("downloads") == null) {
            $objElement = new class_module_pages_element();
            $objElement->setStrName("downloads");
            $objElement->setStrClassAdmin("class_element_downloads_admin.php");
            $objElement->setStrClassPortal("class_element_downloads_portal.php");
            $objElement->setIntCachetime(3600);
            $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->getVersion());
            $objElement->updateObjectToDb();
            $strReturn .= "Element registered...\n";
        }
        else {
            $strReturn .= "Element already installed!...\n";
        }
		return $strReturn;
	}


	public function update() {
	    return "";
	}

    public function postUpdate() {
        $strReturn = "";

        $strReturn = "";
        if(class_module_pages_element::getElement("downloads")->getStrVersion() == "3.4.1") {
            $strReturn .= $this->postUpdate_341_349();
            $this->objDB->flushQueryCache();
        }

        return $strReturn;

    }


    public function postUpdate_341_349() {
        $strReturn = "Updating element downloads to 3.4.9...\n";

        $this->updateElementVersion("downloads", "3.4.9");
        $this->updateElementVersion("galleryRandom", "3.4.9");
        return $strReturn;
    }

}