<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

/**
 * The Base-Class for all other admin-classes
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
abstract class class_admin {


    /**
     * Instance of class_config
     *
     * @var class_config
     */
    protected $objConfig = null; //Object containing config-data
    /**
     * Instance of class_db
     *
     * @var class_db
     */
    protected $objDB = null; //Object to the database
    /**
     * Instance of class_toolkit_admin
     *
     * @var class_toolkit_admin
     */
    protected $objToolkit = null; //Toolkit-Object
    /**
     * Instance of class_session
     *
     * @var class_session
     */
    protected $objSession = null; //Object containing the session-management
    /**
     * Instance of class_template
     *
     * @var class_template
     */
    protected $objTemplate = null; //Object to handle templates
    /**
     * Instance of class_lang
     *
     * @var class_lang
     */
    private $objLang = null; //Object managing the lang-files

    /**
     * Instance of the current modules' definition
     *
     * @var class_module_system_module
     */
    private $objModule = null;

    private $strAction; //current action to perform (GET/POST)
    private $strSystemid; //current systemid
    private $strLangBase; //String containing the current module to be used to load texts
    private $arrHistory; //Stack containing the 5 urls last visited
    protected $arrModule = array(); //Array containing information about the current module
    protected $strOutput; //String containing the output generated by an internal action
    private $arrOutput; //Array containing the admin-output
    protected $arrValidationErrors = array(); //Array to keep found validation errors

    /**
     * Constructor
     *
     * @param string $strSystemid
     *
     * @internal param array $arrModul
     */
    public function __construct($strSystemid = "") {

        //default-template: main.tpl
        if(!isset($this->arrModule["template"])) {
            $this->setArrModuleEntry("template", "/main.tpl");
        }

        //Setting SystemID
        if($strSystemid == "") {
            $this->setSystemid(class_carrier::getInstance()->getParam("systemid"));
        }
        else {
            $this->setSystemid($strSystemid);
        }

        //Generating all the needed Objects. For this we use our cool cool carrier-object
        //take care of loading just the necessary objects
        $objCarrier = class_carrier::getInstance();
        $this->objConfig = $objCarrier->getObjConfig();
        $this->objDB = $objCarrier->getObjDB();
        $this->objToolkit = $objCarrier->getObjToolkit("admin");
        $this->objSession = $objCarrier->getObjSession();
        $this->objLang = $objCarrier->getObjLang();
        $this->objTemplate = $objCarrier->getObjTemplate();

        //Writing to the history
        if(!_xmlLoader_) {
            $this->setHistory();
        }

        //And keep the action
        $this->strAction = $this->getParam("action");
        //in most cases, the list is the default action if no other action was passed
        if($this->strAction == "") {
            $this->strAction = "list";
        }

        //set the correct language to the text-object
        $this->objLang->setStrTextLanguage($this->objSession->getAdminLanguage(true));

        $this->strLangBase = $this->arrModule["modul"];

        //define the print-view, if requested
        if($this->getParam("printView") != "") {
            $this->arrModule["template"] = "/print.tpl";
        }

        if($this->getParam("folderview") != "") {
            $this->arrModule["template"] = "/folderview.tpl";
        }


        //TODO: find proper position
        class_adminskin_helper::defineSkinWebpath();
    }

    // --- Common Methods -----------------------------------------------------------------------------------


    /**
     * Writes a value to the params-array
     *
     * @param string $strKey Key
     * @param mixed $mixedValue Value
     *
     * @return void
     */
    public function setParam($strKey, $mixedValue) {
        class_carrier::getInstance()->setParam($strKey, $mixedValue);
    }

    /**
     * Returns a value from the params-Array
     *
     * @param string $strKey
     *
     * @return string|string[] else ""
     */
    public function getParam($strKey) {
        return class_carrier::getInstance()->getParam($strKey);
    }

    /**
     * Returns the complete Params-Array
     *
     * @return mixed
     * @final
     */
    public final function getAllParams() {
        return class_carrier::getAllParams();
    }

    /**
     * returns the action used for the current request
     *
     * @return string
     * @final
     */
    public final function getAction() {
        return (string)$this->strAction;
    }

    /**
     * Overwrites the current action
     *
     * @param string $strAction
     */
    public final function setAction($strAction) {
        $this->strAction = $strAction;
    }


    // --- SystemID & System-Table Methods ------------------------------------------------------------------

    /**
     * Sets the current SystemID
     *
     * @param string $strID
     *
     * @return bool
     * @final
     */
    public final function setSystemid($strID) {
        if(validateSystemid($strID)) {
            $this->strSystemid = $strID;
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Returns the current SystemID
     *
     * @return string
     * @final
     */
    public final function getSystemid() {
        return $this->strSystemid;
    }

    /**
     * Negates the status of a systemRecord
     *
     * @param string $strSystemid
     *
     * @return bool
     * @deprecated call setStatus on a model-object directly
     * @todo remove before 4.0 release
     */
    public function setStatus($strSystemid = "") {
        if($strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }
        $objCommon = new class_module_system_common($strSystemid);
        return $objCommon->setStatus();
    }

    /**
     * Gets the status of a systemRecord
     *
     * @param string $strSystemid
     *
     * @return int
     * @deprecated call getStatus on a model-object directly
     * @todo remove before 4.0 release
     */
    public function getStatus($strSystemid = "") {
        if($strSystemid == "0" || $strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }
        $objCommon = new class_module_system_common($strSystemid);
        return $objCommon->getStatus();
    }

    /**
     * Returns the name of the user who last edited the record
     *
     * @param string $strSystemid
     *
     * @return string
     * @deprecated
     * @todo remove before 4.0 release
     */
    public function getLastEditUser($strSystemid = "") {
        if($strSystemid == 0 || $strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }
        $objCommon = new class_module_system_common($strSystemid);
        return $objCommon->getLastEditUser();
    }

    /**
     * Gets the Prev-ID of a record
     *
     * @param string $strSystemid
     *
     * @return string
     * @deprecated
     * @todo remove before 4.0 release
     */
    public function getPrevId($strSystemid = "") {
        if($strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }
        $objCommon = new class_module_system_common($strSystemid);
        return $objCommon->getPrevId();

    }

    /**
     * Returns the data for a registered module
     * FIXME: validate if still required
     *
     * @param string $strName
     * @param bool $bitCache
     *
     * @return mixed
     * @deprecated
     */
    public function getModuleData($strName, $bitCache = true) {
        return class_module_system_module::getPlainModuleData($strName, $bitCache);

    }

    /**
     * Returns the SystemID of a installed module
     *
     * @param string $strModule
     *
     * @return string "" in case of an error
     * @deprecated
     */
    public function getModuleSystemid($strModule) {
        $objModule = class_module_system_module::getModuleByName($strModule);
        if($objModule != null) {
            return $objModule->getSystemid();
        }
        else {
            return "";
        }
    }

    /**
     * Generates a sorted array of systemids, reaching from the passed systemid up
     * until the assigned module-id
     *
     * @param string $strSystemid
     * @param string $strStopSystemid
     *
     * @return mixed
     * @deprecated should be handled by the model-classes instead
     */
    public function getPathArray($strSystemid = "", $strStopSystemid = "") {
        if($strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }
        if($strStopSystemid == "") {
            $strStopSystemid = $this->getObjModule()->getSystemid();
        }

        $objSystemCommon = new class_module_system_common();
        return $objSystemCommon->getPathArray($strSystemid, $strStopSystemid);
    }

    /**
     * Returns a value from the $arrModule array.
     * If the requested key not exists, returns ""
     *
     * @param string $strKey
     *
     * @return string
     */
    public function getArrModule($strKey) {
        if(isset($this->arrModule[$strKey])) {
            return $this->arrModule[$strKey];
        }
        else {
            return "";
        }
    }

    /**
     * Writes a key-value-pair to the arrModule
     *
     * @param string $strKey
     * @param mixed $strValue
     */
    public function setArrModuleEntry($strKey, $strValue) {
        $this->arrModule[$strKey] = $strValue;
    }

    /**
     * Creates a text-based description of the current module.
     * Therefore the text-entry module_description should be available.
     *
     * @return string
     * @since 3.2.1
     */
    public function getModuleDescription() {
        $strDesc = $this->getLang("module_description");
        if($strDesc != "!module_description!") {
            return $strDesc;
        }
        else {
            return "";
        }
    }

    // --- HistoryMethods -----------------------------------------------------------------------------------

    /**
     * Holds the last 5 URLs the user called in the Session
     * Admin and Portal are seperated arrays, but don't worry anyway...

     */
    protected function setHistory() {
        //Loading the current history from session
        $this->arrHistory = $this->objSession->getSession("adminHistory");

        $strQueryString = getServer("QUERY_STRING");
        //Clean Querystring of emtpy actions
        if(uniSubstr($strQueryString, -8) == "&action=") {
            $strQueryString = substr_replace($strQueryString, "", -8);
        }
        //Just do s.th., if not in the rights-mgmt
        if(uniStrpos($strQueryString, "module=right") !== false) {
            return;
        }
        //And insert just, if different to last entry
        if($strQueryString == $this->getHistory()) {
            return;
        }
        //If we reach up here, we can enter the current query
        if($this->arrHistory !== false) {
            array_unshift($this->arrHistory, $strQueryString);
            while(count($this->arrHistory) > 5) {
                array_pop($this->arrHistory);
            }
        }
        else {
            $this->arrHistory[] = $strQueryString;
        }
        //saving the new array to session
        $this->objSession->setSession("adminHistory", $this->arrHistory);
    }

    /**
     * Returns the URL at the given position (from HistoryArray)
     *
     * @param int $intPosition
     *
     * @return string
     */
    protected function getHistory($intPosition = 0) {
        if(isset($this->arrHistory[$intPosition])) {
            return $this->arrHistory[$intPosition];
        }
        else {
            return "History error!";
        }
    }

    // --- TextMethods --------------------------------------------------------------------------------------

    /**
     * Used to get Text out of Textfiles
     *
     * @param string $strName
     * @param string $strModule
     *
     * @return string
     */
    public function getLang($strName, $strModule = "") {
        if($strModule == "") {
            $strModule = $this->strLangBase;
        }

        //Now we have to ask the Text-Object to return the text
        return $this->objLang->getLang($strName, $strModule);
    }

    /**
     * Sets the textbase, so the module used to load texts
     *
     * @param string $strLangbase
     */
    protected final function setStrLangBase($strLangbase) {
        $this->strLangBase = $strLangbase;
    }

    /**
     * Returns the current Text-Object Instance
     *
     * @return class_lang
     */
    protected function getObjLang() {
        return $this->objLang;
    }

    // --- PageCache Features -------------------------------------------------------------------------------

    /**
     * Deletes the complete Pages-Cache
     *
     * @return bool
     */
    public function flushCompletePagesCache() {
        return class_cache::flushCache("class_element_portal");
    }

    /**
     * Removes one page from the cache
     *
     * @deprecated use flushCompletePagesCache() instead
     * @return bool
     */
    public function flushPageFromPagesCache() {
        //since the navigation may depend on page-internal characteristics, the complete cache is
        //flushed instead only the current page
        return self::flushCompletePagesCache();
    }

    // --- OutputMethods ------------------------------------------------------------------------------------

    /**
     * Basic controller method invoking all further methods in order to generate an admin view.
     * Takes care of generating the navigation, title, common JS variables, loading quickhelp texts,...
     *
     * @throws class_exception
     * @return string
     * @final
     * @todo could be moved to a general admin-skin helper
     */
    public final function getModuleOutput() {

        $this->validateAndUpdateCurrentAspect();

        //Calling the contentsetter
        $this->arrOutput["content"] = $this->strOutput;
        $this->arrOutput["path"] = class_admin_helper::getAdminPathNavi($this->getArrOutputNaviEntries(), $this->getArrModule("modul"));
        $this->arrOutput["moduleSitemap"] = $this->objToolkit->getAdminSitemap($this->getArrModule("modul"));
        $this->arrOutput["moduletitle"] = $this->getOutputModuleTitle();
        $this->arrOutput["actionTitle"] = $this->getOutputActionTitle();
        if(class_module_system_aspect::getObjectCount(true) > 1) {
            $this->arrOutput["aspectChooser"] = $this->objToolkit->getAspectChooser($this->arrModule["modul"], $this->getAction(), $this->getSystemid());
        }
        $this->arrOutput["login"] = $this->getOutputLogin();
        $this->arrOutput["quickhelp"] = $this->getQuickHelp();
        $this->arrOutput["languageswitch"] = (class_module_system_module::getModuleByName("languages") != null ? class_module_system_module::getModuleByName("languages")->getAdminInstanceOfConcreteModule()->getLanguageSwitch() : "");
        $this->arrOutput["module_id"] = $this->arrModule["moduleId"];
        $this->arrOutput["webpathTitle"] = urldecode(str_replace(array("http://", "https://"), array("", ""), _webpath_));
        $this->arrOutput["head"] = "<script type=\"text/javascript\">KAJONA_DEBUG = " . $this->objConfig->getDebug("debuglevel") . "; KAJONA_WEBPATH = '" . _webpath_ . "'; KAJONA_BROWSER_CACHEBUSTER = " . _system_browser_cachebuster_ . ";</script>";
        //Loading the desired Template
        //if requested the pe, load different template
        $strTemplateID = "";
        if($this->getParam("peClose") == 1 || $this->getParam("pe") == 1) {
            //add suffix
            try {
                $strTemplate = str_replace(".tpl", "", $this->arrModule["template"]) . "_portaleditor.tpl";
                $strTemplateID = $this->objTemplate->readTemplate($strTemplate, "", false, true);
            }
            catch(class_exception $objException) {
                //An error occured. In most cases, this is because the user ist not logged in, so the login-template was requested.
                if($this->arrModule["template"] == "/login.tpl") {
                    throw new class_exception("You have to be logged in to use the portal editor!!!", class_exception::$level_ERROR);
                }
            }
        }
        else {
            $strTemplateID = $this->objTemplate->readTemplate(class_adminskin_helper::getPathForSkin($this->objSession->getAdminSkin()) . $this->arrModule["template"], "", true);
        }
        return $this->objTemplate->fillTemplate($this->arrOutput, $strTemplateID);
    }

    /**
     * Validates if the requested module is valid for the current aspect.
     * If necessary, the current aspect is updated.
     *
     * @return void
     */
    private function validateAndUpdateCurrentAspect() {
        if(_xmlLoader_ === true || $this->arrModule["template"] == "/folderview.tpl") {
            return;
        }

        $objModule = $this->getObjModule();
        $strCurrentAspect = class_module_system_aspect::getCurrentAspectId();
        if($objModule != null && $objModule->getStrAspect() != "") {
            $arrAspects = explode(",", $objModule->getStrAspect());
            if(count($arrAspects) == 1 && $arrAspects[0] != $strCurrentAspect) {
                class_module_system_aspect::setCurrentAspectId($arrAspects[0]);
            }

        }
    }

    /**
     * Tries to generate a quick-help button.
     * Tests for exisiting help texts
     *
     * @return string
     */
    protected function getQuickHelp() {
        $strReturn = "";
        $strText = "";
        $strTextname = "";

        //Text for the current action available?
        //different loading when editing page-elements
        if($this->getParam("module") == "pages_content" && ($this->getParam("action") == "edit" || $this->getParam("action") == "new")) {
            $objElement = null;
            if($this->getParam("action") == "edit") {
                $objElement = new class_module_pages_pageelement($this->getSystemid());
            }
            else if($this->getParam("action") == "new") {
                $strPlaceholderElement = $this->getParam("element");
                $objElement = class_module_pages_element::getElement($strPlaceholderElement);
            }
            //Build the class-name
            $strElementClass = str_replace(".php", "", $objElement->getStrClassAdmin());
            //and finally create the object
            if($strElementClass != "") {
                $objElement = new $strElementClass();
                $strTextname = $this->getObjLang()->stringToPlaceholder("quickhelp_" . $objElement->getArrModule("name"));
                $strText = class_carrier::getInstance()->getObjLang()->getLang($strTextname, $objElement->getArrModule("modul"));
            }
        }
        else {
            $strTextname = $this->getObjLang()->stringToPlaceholder("quickhelp_" . $this->strAction);
            $strText = $this->getLang($strTextname);
        }

        if($strText != "!" . $strTextname . "!") {
            //Text found, embed the quickhelp into the current skin
            $strReturn .= $this->objToolkit->getQuickhelp($strText);
        }

        return $strReturn;
    }


    protected function getArrOutputNaviEntries() {
        $arrReturn = array(
            getLinkAdmin("dashboard", "", "", $this->getLang("modul_titel", "dashboard")),
            getLinkAdmin($this->getArrModule("modul"), "", "", $this->getOutputModuleTitle())
        );

        //see, if the current action may be mapped
        $strActionName = "action" . ucfirst($this->getAction());
        $strAction = $this->getLang($strActionName);
        if($strAction != "!" . $strActionName . "!") {
            $arrReturn[] = getLinkAdmin($this->getArrModule("modul"), $this->getAction(), "&systemid=" . $this->getSystemid(), $strAction);
        }

        return $arrReturn;
    }

    /**
     * Writes the ModuleNavi, overwrite if needed
     * Use two-dim arary:
     * array[
     *     array["right", "link"],
     *     array["right", "link"]
     * ]
     *
     * @return array array containing all links
     */
    public function getOutputModuleNavi() {
        return array();
    }

    /**
     * Writes the ModuleTitle, overwrite if needed
     *
     * @return string
     */
    protected function getOutputModuleTitle() {
        if($this->getLang("modul_titel") != "!modul_titel!") {
            return $this->getLang("modul_titel");
        }
        else {
            return $this->arrModule["modul"];
        }
    }

    /**
     * Creates the action name to be rendered in the output, in most cases below the pathnavigation-bar
     * @return string
     */
    protected function getOutputActionTitle() {
        return $this->getOutputModuleTitle();
    }

    /**
     * Writes the SessionInfo, overwrite if needed
     *
     * @return string
     */
    protected function getOutputLogin() {
        $objLogin = new class_module_login_admin();
        return $objLogin->getLoginStatus();
    }

    /**
     * This method triggers the internal processing.
     * It may be overridden if required, e.g. to implement your own action-handling.
     * By default, the method to be called is set up out of the action-param passed.
     * Example: The action requested is names "newPage". Therefore, the framework tries to
     * call actionNewPage(). If no method matching the schema is found, an exception is being thrown.
     * The actions' output is saved back to self::strOutput and, is returned in addition.
     * Returning the content is only implemented to remain backwards compatible with older implementations.
     * Since Kajona 4.0, the check on declarative permissions via annotations is supported.
     * Therefore the list of permissions, named after the "permissions" annotation are validated against
     * the module currently loaded.
     *
     * @see class_rights::validatePermissionString
     *
     * @param string $strAction
     *
     * @throws class_exception
     * @return string
     * @since 3.4
     */
    public function action($strAction = "") {

        if($strAction == "") {
            $strAction = $this->strAction;
        }
        else {
            $this->strAction = $strAction;
        }

        //search for the matching method - build method name
        $strMethodName = "action" . uniStrtoupper($strAction[0]) . uniSubstr($strAction, 1);

        if(method_exists($this, $strMethodName)) {

            //validate the permissions required to call this method, the xml-part is validated afterwards
            $objAnnotations = new class_reflection(get_class($this));

            $strPermissions = $objAnnotations->getMethodAnnotationValue($strMethodName, "@permissions");
            if($strPermissions !== false) {

                if(validateSystemid($this->getSystemid()) && class_objectfactory::getInstance()->getObject($this->getSystemid()) != null) {
                    $objObjectToCheck = class_objectfactory::getInstance()->getObject($this->getSystemid());
                }
                else {
                    $objObjectToCheck = $this->getObjModule();
                }

                if(!class_carrier::getInstance()->getObjRights()->validatePermissionString($strPermissions, $objObjectToCheck)) {
                    $this->strOutput = $this->getLang("commons_error_permissions");
                    throw new class_exception("you are not authorized/authenticated to call this action", class_exception::$level_ERROR);
                }
            }


            //validate the loading channel - xml or regular
            if(_xmlLoader_ === true) {
                //check it the method is allowed for xml-requests

                if(!$objAnnotations->hasMethodAnnotation($strMethodName, "@xml") && substr(get_class($this), -3) != "xml") {
                    throw new class_exception("called method " . $strMethodName . " not allowed for xml-requests", class_exception::$level_FATALERROR);
                }

                if($this->arrModule["modul"] != $this->getParam("module")) {
                    class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
                    throw new class_exception("you are not authorized/authenticated to call this action", class_exception::$level_FATALERROR);
                }
            }

            $this->strOutput = $this->$strMethodName();
        }
        else {
            $objReflection = new ReflectionClass($this);
            //if the pe was requested and the current module is a login-module, there are insufficient permissions given
            if($this->arrModule["template"] == "/login.tpl" && $this->getParam("pe") != "") {
                throw new class_exception("You have to be logged in to use the portal editor!!!", class_exception::$level_ERROR);
            }

            if(get_class($this) == "class_module_login_admin_xml") {
                class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
                throw new class_exception("you are not authorized/authenticated to call this action", class_exception::$level_FATALERROR);
            }

            throw new class_exception("called method " . $strMethodName . " not existing for class " . $objReflection->getName(), class_exception::$level_FATALERROR);
        }

        return $this->strOutput;
    }


    //--- FORM-Validation -----------------------------------------------------------------------------------

    /**
     * Method used to validate posted form-values.
     * NOTE: To work with this method, the derived class needs to implement
     * a method "getRequiredFields()", returning an array of field to validate.
     * The array returned by getRequiredFields() has to fit the format
     *  [fieldname] = type, whereas type can be one of
     * string, number, email, folder, systemid
     * The array saved in $this->$arrValidationErrors return by this method is empty in case of no validation Errors,
     * otherwise an array with the structure
     * [nonvalidField] = text from objText
     * is being created.
     *
     * @return bool
     */
    protected function validateForm() {
        $arrReturn = array();

        $arrFieldsToCheck = $this->getRequiredFields();

        foreach($arrFieldsToCheck as $strFieldname => $strType) {

            $bitAdd = false;

            if($strType == "string") {
                if(!checkText($this->getParam($strFieldname), 2)) {
                    $bitAdd = true;
                }
            }
            else if($strType == "character") {
                if(!checkText($this->getParam($strFieldname), 1)) {
                    $bitAdd = true;
                }
            }
            elseif($strType == "number") {
                if(!checkNumber($this->getParam($strFieldname))) {
                    $bitAdd = true;
                }
            }
            elseif($strType == "email") {
                if(!checkEmailaddress($this->getParam($strFieldname))) {
                    $bitAdd = true;
                }
            }
            elseif($strType == "folder") {
                if(!checkFolder($this->getParam($strFieldname))) {
                    $bitAdd = true;
                }
            }
            elseif($strType == "systemid") {
                if(!validateSystemid($this->getParam($strFieldname))) {
                    $bitAdd = true;
                }
            }
            elseif($strType == "date") {
                if(!checkNumber($this->getParam($strFieldname))) {
                    $objDate = new class_date("0");
                    $objDate->generateDateFromParams($strFieldname, $this->getAllParams());
                    if((int)$objDate->getLongTimestamp() == 0) {
                        $bitAdd = true;
                    }
                }
            }
            else {
                $arrReturn[$strFieldname] = "No or unknown validation-type for " . $strFieldname . " given";
            }

            if($bitAdd) {
                if($this->getLang("required_" . $strFieldname) != "!required_" . $strFieldname . "!") {
                    $arrReturn[$strFieldname] = $this->getLang("required_" . $strFieldname);
                }
                else if($this->getLang($strFieldname) != "!" . $strFieldname . "!") {
                    $arrReturn[$strFieldname] = $this->getLang($strFieldname);
                }
                else {
                    $arrReturn[$strFieldname] = $this->getLang("required_" . $strFieldname);
                }
            }

        }
        $this->arrValidationErrors = array_merge($this->arrValidationErrors, $arrReturn);
        return (count($this->arrValidationErrors) == 0);
    }

    /**
     * Overwrite this function, if you want to validate passed form-input
     *
     * @return mixed
     */
    public function getRequiredFields() {
        return array();
    }

    /**
     * Returns the array of validationErrors
     *
     * @return mixed
     */
    public function getValidationErrors() {
        return $this->arrValidationErrors;
    }

    /**
     * Adds a validation error to the array of errors
     *
     * @param string $strField
     * @param string $strErrormessage
     */
    public function addValidationError($strField, $strErrormessage) {
        $this->arrValidationErrors[$strField] = $strErrormessage;
    }

    /**
     * Removes a validation error from the array of errors
     *
     * @param string $strField
     */
    public function removeValidationError($strField) {
        unset($this->arrValidationErrors[$strField]);
    }

    /**
     * Use this method to reload a specific url.
     * <b>Use ONLY this method and DO NOT use header("Location: ...");</b>
     *
     * @param string $strUrlToLoad
     */
    public function adminReload($strUrlToLoad) {
        //filling constants
        $strUrlToLoad = str_replace("_webpath_", _webpath_, $strUrlToLoad);
        $strUrlToLoad = str_replace("_indexpath_", _indexpath_, $strUrlToLoad);
        //No redirect, if close-Command for admin-area should be sent
        if($this->getParam("peClose") == "") {
            class_response_object::getInstance()->setStrRedirectUrl($strUrlToLoad);
        }
    }

    /**
     * Loads the language to edit content
     *
     * @return string
     */
    public function getLanguageToWorkOn() {
        $objSystemCommon = new class_module_system_common();
        return $objSystemCommon->getStrAdminLanguageToWorkOn();
    }

    /**
     * Returns the current instance of class_module_system_module, based on the current subclass.
     * Lazy-loading, so loaded on first access.
     *
     * @return class_module_system_module|null
     */
    public function getObjModule() {

        if($this->objModule == null) {
            $this->objModule = class_module_system_module::getModuleByName($this->arrModule["modul"]);
        }

        return $this->objModule;
    }
}

