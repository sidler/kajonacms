<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_carrier.php 4059 2011-08-09 14:52:41Z sidler $                                            *
********************************************************************************************************/

/**
 * Installer to install the mediamanager-module
 *
 * @package module_mediamanager
 */
class class_installer_element_gallery extends class_installer_base implements interface_installer {

	public function __construct() {
		$this->setArrModuleEntry("version", "3.4.9");
		$this->setArrModuleEntry("name", "element_gallery");
		$this->setArrModuleEntry("name_lang", "Element Gallery");
		$this->setArrModuleEntry("moduleId", _mediamanager_module_id_);

		parent::__construct();
	}

	public function getNeededModules() {
	    return array("system", "pages", "mediamanager");
	}

	public function hasPostInstalls() {

        $objElement = class_module_pages_element::getElement("gallery");
        if($objElement === null)
            return true;

        $objElement = class_module_pages_element::getElement("galleryRandom");
        if($objElement === null)
            return true;


        return false;
	}

    public function getMinSystemVersion() {
	    return "3.4.9";
	}

    public function install() {
        return "";
	}

    public function hasPostUpdates() {
        $objElement = null;
        try {
            $objElement = class_module_pages_element::getElement("gallery");
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
		$strReturn .= "Installing gallery-element table...\n";

		$arrFields = array();
		$arrFields["content_id"] 			= array("char20", false);
		$arrFields["gallery_id"] 			= array("char20", true);
		$arrFields["gallery_mode"] 			= array("int", true);
		$arrFields["gallery_template"] 		= array("char254", true);
		$arrFields["gallery_maxh_d"] 		= array("int", true);
		$arrFields["gallery_maxw_d"] 		= array("int", true);
		$arrFields["gallery_imagesperpage"] = array("int", true);
		$arrFields["gallery_text"] 			= array("char254", true);
		$arrFields["gallery_overlay"]    	= array("char254", true);
		$arrFields["gallery_text_x"] 		= array("int", true);
		$arrFields["gallery_text_y"] 		= array("int", true);

		if(!$this->objDB->createTable("element_gallery", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering gallery-element...\n";
        $objElement = null;
		if(class_module_pages_element::getElement("gallery") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("gallery");
		    $objElement->setStrClassAdmin("class_element_gallery_admin.php");
		    $objElement->setStrClassPortal("class_element_gallery_portal.php");
		    $objElement->setIntCachetime(3600);
		    $objElement->setIntRepeat(1);
            $objElement->setStrVersion($this->getVersion());
			$objElement->updateObjectToDb();
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";
		}


		$strReturn .= "Registering galleryRandom-element...\n";
		if(class_module_pages_element::getElement("galleryRandom") == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("galleryRandom");
		    $objElement->setStrClassAdmin("class_element_galleryRandom_admin.php");
		    $objElement->setStrClassPortal("class_element_gallery_portal.php");
		    $objElement->setIntCachetime(-1);
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
        if(class_module_pages_element::getElement("gallery")->getStrVersion() == "3.4.1") {
            $strReturn .= $this->postUpdate_341_349();
            $this->objDB->flushQueryCache();
        }

        return $strReturn;
    }

    public function postUpdate_341_349() {
        $strReturn = "Updating element gallery to 3.4.9...\n";

        $strReturn .= "Migrating old gallery-element table...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."element_gallery")."
                            DROP ".$this->objDB->encloseColumnName("gallery_maxh_p").",
                            DROP ".$this->objDB->encloseColumnName("gallery_maxw_p").",
                            DROP ".$this->objDB->encloseColumnName("gallery_maxh_m").",
                            DROP ".$this->objDB->encloseColumnName("gallery_maxw_m")."";
        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occured! ...\n";

        $this->updateElementVersion("gallery", "3.4.9");
        $this->updateElementVersion("galleryRandom", "3.4.9");
        return $strReturn;
    }


}