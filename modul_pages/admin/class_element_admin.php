<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/


/**
 * The base class for all page-elements
 *
 * @package modul_pages
 */
class class_element_admin extends class_admin {

    private $bitDoValidation = false;

	/**
	 * Constructor
	 *
	 * @param mixed $arrModule
	 */
	public function __construct($arrModule) {
		$arrModule["p_name"] 			= "element_admin";
		$arrModule["p_author"] 			= "sidler@mulchprod.de";
		$arrModule["p_nummer"] 			= _pages_elemente_modul_id_;
		$arrModule["p_module"]          = "pages_content";

		//Calling the base class
		parent::__construct($arrModule);
	}


	/**
	 * Forces the element to return a form and adds als stuff needed by the system to handle the request properly
	 *
	 * @param string $strMode edit || new
	 * @return string
	 */
	final public function actionEdit($strMode = "edit") {
		$strReturn = "";
		//Right before we do anything, load the data of the current element
		if($strMode == "edit")
			$arrElementData = $this->loadElementData();
		else
			$arrElementData = array();

		//Load the form generated by the element
		$strFormElement = $this->getEditForm(array_merge($arrElementData, $this->getAllParams()));

		//Start by creating the form & action
		$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["p_module"], "saveElement"), "elEditForm");

		//validation errors?
		if($this->bitDoValidation && !$this->validateForm())
		    $strReturn .= $this->objToolkit->getValidationErrors($this);

		//add a folder containing optional system-fields
        $strSystemFields = "";
        $arrStart = array("", "", "");
        $arrEnd = array("", "", "");
        $bitShow = false;
        if(isset($arrElementData["system_date_start"]) && (int)$arrElementData["system_date_start"] > 0) {
            $arrStart = explode(".", date("d.m.Y", $arrElementData["system_date_start"]));
            $bitShow = true;
        }
        if(isset($arrElementData["system_date_end"]) && (int)$arrElementData["system_date_end"] > 0) {
            $arrEnd = explode(".", date("d.m.Y", $arrElementData["system_date_end"]));
            $bitShow = true;
        }

        $strInternalTitle = (isset($arrElementData["page_element_placeholder_title"]) ? $arrElementData["page_element_placeholder_title"] : "");
        if($strInternalTitle != "")
            $bitShow = true;

		$strSystemFields .= $this->objToolkit->formInputText("page_element_placeholder_title", $this->getText("page_element_placeholder_title", "pages"), $strInternalTitle);
		$strSystemFields .= $this->objToolkit->formDateSimple("start", $arrStart[0], $arrStart[1], $arrStart[2], $this->getText("page_element_start", "pages"), false);
		$strSystemFields .= $this->objToolkit->formDateSimple("end", $arrEnd[0], $arrEnd[1], $arrEnd[2], $this->getText("page_element_end", "pages"), false);

        $strReturn .= "<br />".$this->objToolkit->getLayoutFolderPic($strSystemFields, $this->getText("page_element_system_folder", "pages"), "icon_folderOpen.gif", "icon_folderClosed.gif", $bitShow );

		//If its going to be a new element, allow to choose the position
		if($strMode == "new") {
    		$arrDropdown = array("first" => $this->getText("element_first", "pages"),
    		                     "last" => $this->getText("element_last", "pages"));
    		$strReturn .= $this->objToolkit->formInputDropdown("element_pos", $arrDropdown, $this->getText("element_pos", "pages"), "last");
		}

		//Adding the element-stuff
		$strReturn .= $strFormElement;
		//system-stuff systemid, mode

		//Language is placed right here instead as a hidden field
		if($strMode == "edit")
		    $strReturn .= $this->objToolkit->formInputHidden("page_element_placeholder_language", $arrElementData["page_element_placeholder_language"]);
		else
		    $strReturn .= $this->objToolkit->formInputHidden("page_element_placeholder_language", $this->getLanguageToWorkOn());

		$strReturn .= $this->objToolkit->formInputHidden("placeholder", $this->getParam("placeholder"));
		$strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
		$strReturn .= $this->objToolkit->formInputHidden("mode", $strMode);
		$strReturn .= $this->objToolkit->formInputHidden("element", $this->getParam("element"));
		//An finally the submit Button
		$strEventhandler = "";
		if($this->getParam("pe") == 1) {
		    $strReturn .= $this->objToolkit->formInputHidden("peClose", "1");
		}
		$strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"), "Submit", $strEventhandler);
		$strReturn .= $this->objToolkit->formClose();
		//and close the element


		return $strReturn;
	}



	/**
	 * Loads the data of the current element
	 *
	 * @return mixed
	 */
	protected final function loadElementData() {
	    //Element-Table given?
	    if($this->arrModule["table"] != "") {
    		$strQuery = "SELECT *
    					 FROM ".$this->arrModule["table"].",
    					 	  "._dbprefix_."element,
    					 	  "._dbprefix_."page_element,
    					 	  "._dbprefix_."system
    					 LEFT JOIN "._dbprefix_."system_date
    					    ON (system_id = system_date_id)
    					 WHERE element_name = page_element_placeholder_element
    					   AND page_element_id = content_id
    					   AND system_id = content_id
    					   AND system_id = '".dbsafeString($this->getSystemid())."'";
	    }
	    else {
	        $strQuery = "SELECT *
    					 FROM "._dbprefix_."element,
    					 	  "._dbprefix_."page_element,
    					 	  "._dbprefix_."system
    					 LEFT JOIN "._dbprefix_."system_date
    					    ON (system_id = system_date_id)
    					 WHERE element_name = page_element_placeholder_element
    					   AND page_element_id = system_id
    					   AND system_id = '".dbsafeString($this->getSystemid())."'";

	    }
		$arrElement = $this->objDB->getRow($strQuery);

		return $arrElement;
	}

	/**
	 * returns the table used by the element
	 *
	 * @return string
	 */
	public function getTable() {
		return $this->arrModule["table"];
	}


	/**
	 * Returns a short description of the saved content
	 * Overwrite if needed
	 *
	 * @return string
	 */
	public function getContentTitle() {
	    return "";
	}


    /**
     * Returns a textual description of the current element, based
     * on the lang key element_description.
     * 
     * @return string
     * @since 3.2.1
     */
    public function getElementDescription() {
        $strDesc = $this->getText($this->arrModule["name"]."_description");
        if($strDesc == "!".$this->arrModule["name"]."_description!")
            $strDesc = "";
        return $strDesc;
    }


    /**
     * Overwrite this method, if you want to execute
     * some special actions right after saving the element to the db, e.g.
     * cleanup functions.
     *
     * @since 3.2.1
     * @return void
     */
    public function doAfterSaveToDb() {
    }

	/**
	 * If the form generated should be validated, pass true. This invokes
	 * the internal validation and printing of errors.
	 * By default, the value is false. The framework sets the value, so there no
	 * need to call this setter in concrete element classes.
	 *
	 * @param bool $bitDoValidation
	 */
	public function setDoValidation($bitDoValidation) {
	    $this->bitDoValidation = $bitDoValidation;
	}
}

?>