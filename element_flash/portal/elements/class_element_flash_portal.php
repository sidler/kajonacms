<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_flash.php 4042 2011-07-25 17:37:44Z sidler $                         *
********************************************************************************************************/

/**
 * Loads the flash element and prepares it for output
 *
 * @package element_flash
 * @author jschroeter@kajona.de
 */
class class_element_flash_portal extends class_element_portal implements interface_portal_element {

    /**
     * Constructor
     *
     * @param class_module_pages_pageelement|mixed $objElementData
     */
	public function __construct($objElementData) {
        parent::__construct($objElementData);
        $this->setArrModuleEntry("table", _dbprefix_."element_universal");
	}

    /**
     * Loads the settings and generates the player object
     *
     * @return string the prepared html-output
     */
	public function loadData() {

        $arrTemplate = array();
        $arrTemplate["systemid"] = $this->getSystemid();
        $arrTemplate["file"] = $this->arrElementData["char1"];
        $arrTemplate["width"] = $this->arrElementData["int1"];
        $arrTemplate["height"] = $this->arrElementData["int2"];

		$strTemplateID = $this->objTemplate->readTemplate("/element_flash/".$this->arrElementData["char2"], "flash");
        $strReturn = $this->fillTemplate($arrTemplate, $strTemplateID);

		return $strReturn;
	}

}