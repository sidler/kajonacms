<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	installer_faqs.php																					*
* 	Installer of the faqs module																		*																										*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                           *
********************************************************************************************************/

require_once(_systempath_."/class_installer_base.php");
require_once(_systempath_."/interface_installer.php");

/**
 * Class providing an installer for the faqs module
 *
 * @package modul_faqs
 */
class class_installer_faqs extends class_installer_base implements interface_installer {

	public function __construct() {
		$arrModule["version"] 		  = "3.0.9";
		$arrModule["name"] 			  = "faqs";
		$arrModule["class_admin"]  	  = "class_modul_faqs_admin";
		$arrModule["file_admin"] 	  = "class_modul_faqs_admin.php";
		$arrModule["class_portal"] 	  = "class_modul_faqs_portal";
		$arrModule["file_portal"] 	  = "class_modul_faqs_portal.php";
		$arrModule["name_lang"] 	  = "Module Faqs";
		$arrModule["moduleId"] 		  = _faqs_modul_id_;

		$arrModule["tabellen"][]      = _dbprefix_."faqs";
		$arrModule["tabellen"][]      = _dbprefix_."faqs_category";
		$arrModule["tabellen"][]      = _dbprefix_."faqs_member";
		$arrModule["tabellen"][]      = _dbprefix_."element_faqs";
		parent::__construct($arrModule);
	}

	public function getNeededModules() {
	    return array("system", "pages");
	}
	
    public function getMinSystemVersion() {
	    return "3.0.9";
	}

	public function hasPostInstalls() {
	    $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='faqs'";
	    $arrRow = $this->objDB->getRow($strQuery);
        if($arrRow["COUNT(*)"] == 0)
            return true;

        return false;
	}

   public function install() {
		$strReturn = "";
		//Tabellen anlegen

		//faqs cat-------------------------------------------------------------------------------------
		$strReturn .= "Installing table faqs_category...\n";
		
		$arrFields = array();
		$arrFields["faqs_cat_id"] 		= array("char20", false);
		$arrFields["faqs_cat_title"]	= array("char254", true);

		if(!$this->objDB->createTable("faqs_category", $arrFields, array("faqs_cat_id")))
			$strReturn .= "An error occured! ...\n";

		//faqs----------------------------------------------------------------------------------
		$strReturn .= "Installing table faqs...\n";

		$arrFields = array();
		$arrFields["faqs_id"] 		= array("char20", false);
		$arrFields["faqs_question"]	= array("text", true);
		$arrFields["faqs_answer"]	= array("text", true);
		
		if(!$this->objDB->createTable("faqs", $arrFields, array("faqs_id")))
			$strReturn .= "An error occured! ...\n";

		//faqs_member----------------------------------------------------------------------------------
		$strReturn .= "Installing table faqs_member...\n";
		
		$arrFields = array();
		$arrFields["faqsmem_id"] 		= array("char20", false);
		$arrFields["faqsmem_faq"]		= array("char20", false);
		$arrFields["faqsmem_category"]	= array("char20", false);

		if(!$this->objDB->createTable("faqs_member", $arrFields, array("faqsmem_id")))
			$strReturn .= "An error occured! ...\n";


		//register the module
		$strSystemID = $this->registerModule("faqs", _faqs_modul_id_, "class_modul_faqs_portal.php", "class_modul_faqs_admin.php", $this->arrModule["version"], true);


		$strReturn .= "Registering system-constants...\n";

		$this->registerConstant("_faqs_suche_seite_", "faqs", class_modul_system_setting::$int_TYPE_PAGE, _faqs_modul_id_);

		return $strReturn;

	}

	public function postInstall() {
		$strReturn = "";

		//Table for page-element
		$strReturn .= "Installing faqs-element table...\n";
		
		$arrFields = array();
		$arrFields["content_id"] 	= array("char20", false);
		$arrFields["faqs_category"]	= array("char20", true);
		$arrFields["faqs_template"]	= array("char254", true);
		
		if(!$this->objDB->createTable("element_faqs", $arrFields, array("content_id")))
			$strReturn .= "An error occured! ...\n";

		//Register the element
		$strReturn .= "Registering faqs-element...\n";
		//check, if not already existing
		$strQuery = "SELECT COUNT(*) FROM "._dbprefix_."element WHERE element_name='faqs'";
		$arrRow = $this->objDB->getRow($strQuery);
		if($arrRow["COUNT(*)"] == 0) {
			$strQuery = "INSERT INTO "._dbprefix_."element
							(element_id, element_name, element_class_portal, element_class_admin, element_repeat) VALUES
							('".$this->generateSystemid()."', 'faqs', 'class_element_faqs.php', 'class_element_faqs.php', 1)";
			$this->objDB->_query($strQuery);
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";
			}
		return $strReturn;
	}


	public function update() {
	    $strReturn = "";
        //check the version we have and to what version to update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.1") {
            $strReturn .= $this->update_301_302();
        }
        
	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.0.2") {
            $strReturn .= $this->update_302_309();
        }


        return $strReturn."\n\n";
	}

	private function update_301_302() {
	    //Run the updates
	    $strReturn = "";
        $strReturn .= "Updating 3.0.1 to 3.0.2...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("faqs", "3.0.2");

        return $strReturn;
	}
	
    private function update_302_309() {
        //Run the updates
        $strReturn = "";
        $strReturn .= "Updating 3.0.2 to 3.0.0...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("faqs", "3.0.9");

        return $strReturn;
    }
}
?>