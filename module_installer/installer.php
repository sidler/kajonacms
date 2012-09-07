<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                           *
********************************************************************************************************/

/**
 * Class representing a graphical installer.
 * Loads all sub-installers
 *
 * @author sidler@mulchprod.de
 * @package module_system
 */
class class_installer {


    private $STR_ORIG_CONFIG_FILE = "";
    private $STR_PROJECT_CONFIG_FILE = "";

    /**
     * @var class_module_packagemanager_metadata[]
     */
    private $arrMetadata;
    private $strOutput = "";
    private $strLogfile = "";
    private $strForwardLink = "";
    private $strBackwardLink = "";

    private $strVersion = "V 3.4.9";

    /**
     * Instance of template-engine
     *
     * @var class_template
     */
    private $objTemplates;

    /**
     * text-object
     *
     * @var class_lang
     */
    private $objLang;

    /**
     * session
     *
     * @var class_session
     */
    private $objSession;


    public function __construct() {
        //start up system
        class_carrier::getInstance();
        $this->objTemplates = class_carrier::getInstance()->getObjTemplate();
        $this->objLang = class_carrier::getInstance()->getObjLang();
        //init session-support
        $this->objSession = class_carrier::getInstance()->getObjSession();

        //set a different language?
        if(issetGet("language")) {
            if(in_array(getGet("language"), explode(",", class_carrier::getInstance()->getObjConfig()->getConfig("adminlangs"))))
                $this->objLang->setStrTextLanguage(getGet("language"));
            //and save to a cookie
            $objCookie = new class_cookie();
            $objCookie->setCookie("adminlanguage", getGet("language"));
        }
        else {
            //init correct text-file handling as in admins
            $this->objLang->setStrTextLanguage($this->objSession->getAdminLanguage(true));
        }

        $this->STR_ORIG_CONFIG_FILE = _corepath_."/module_system/system/config/config.php";
        $this->STR_PROJECT_CONFIG_FILE = _realpath_._projectpath_."/system/config/config.php";
    }


    /**
     * Action block to control the behaviour

     */
    public function action() {

        //check if needed values are given
        if(!$this->checkDefaultValues())
            $this->configWizard();

        //load a list of available installers
        $this->loadInstaller();

        //step one: needed php-values
        if(!isset($_GET["step"]))
            $this->checkPHPSetting();


        elseif($_GET["step"] == "config" || !$this->checkDefaultValues()) {
            $this->configWizard();
        }

        elseif($_GET["step"] == "loginData") {
            $this->adminLoginData();
        }

        elseif($_GET["step"] == "install") {
            $this->createModuleInstalls();
        }

        elseif($_GET["step"] == "samplecontent") {
            $this->installSamplecontent();
        }

        elseif($_GET["step"] == "finish") {
            $this->finish();
        }
    }

    /**
     * Makes a few checks on files and settings for a correct webserver

     */
    public function checkPHPSetting() {
        $strReturn = "";

        $arrFilesAndFolders = array("/project/system/config",
            "/project/dbdumps",
            "/project/log",
            "/files/cache",
            "/files/images/upload",
            "/files/images/public",
            "/files/downloads");

        $arrModules = array("mbstring",
            "gd",
            "xml");

        $strReturn .= $this->getLang("installer_phpcheck_intro");
        $strReturn .= $this->getLang("installer_phpcheck_lang");

        //link to different languages
        $arrLangs = explode(",", class_carrier::getInstance()->getObjConfig()->getConfig("adminlangs"));
        $intLangCount = 1;
        foreach($arrLangs as $strOneLang) {
            $strReturn .= "<a href=\""._webpath_."/installer.php?language=".$strOneLang."\">".class_carrier::getInstance()->getObjLang()->getLang("lang_".$strOneLang, "user")."</a>";
            if($intLangCount++ < count($arrLangs)) {
                $strReturn .= " | ";
            }
        }

        $strReturn .= "<br />".$this->getLang("installer_phpcheck_intro2");

        foreach($arrFilesAndFolders as $strOneFile) {
            $strReturn .= $this->getLang("installer_phpcheck_folder").$strOneFile."...<br />";
            if(is_writable(_realpath_.$strOneFile))
                $strReturn .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<span class=\"green\">".$this->getLang("installer_given")."</span>.<br />";
            else
                $strReturn .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<span class=\"red\">".$this->getLang("installer_missing")."</span>!<br />";
        }

        foreach($arrModules as $strOneModule) {
            $strReturn .= $this->getLang("installer_phpcheck_module").$strOneModule."...<br />";
            if(in_array($strOneModule, get_loaded_extensions()))
                $strReturn .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<span class=\"green\">".$this->getLang("installer_loaded")."</span>.<br />";
            else
                $strReturn .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<span class=\"red\">".$this->getLang("installer_nloaded")."</span>!<br />";
        }

        $this->strForwardLink = $this->getForwardLink(_webpath_."/installer.php?step=config");
        $this->strBackwardLink = "";
        $this->strOutput = $strReturn;
    }

    /**
     * Shows a form to write the values to the config files

     */
    public function configWizard() {
        $strReturn = "";

        if($this->checkDefaultValues())
            header("Location: "._webpath_."/installer.php?step=loginData");


        if(!isset($_POST["write"])) {

            //check for available modules
            $strMysqliInfo = "";
            $strSqlite3Info = "";
            $strPostgresInfo = "";
            $strOci8Info = "";
            if(!in_array("mysqli", get_loaded_extensions())) {
                $strMysqliInfo = "<div class=\"error\">".$this->getLang("installer_dbdriver_na")." mysqli</div>";
            }
            if(!in_array("pgsql", get_loaded_extensions())) {
                $strPostgresInfo = "<div class=\"error\">".$this->getLang("installer_dbdriver_na")." postgres</div>";
            }
            if(in_array("sqlite3", get_loaded_extensions())) {
                $strSqlite3Info = "<div class=\"info\">".$this->getLang("installer_dbdriver_sqlite3")."</div>";
            }
            else {
                $strSqlite3Info = "<div class=\"error\">".$this->getLang("installer_dbdriver_na")." sqlite3</div>";
            }
            if(in_array("oci8", get_loaded_extensions())) {
                $strOci8Info = "<div class=\"info\">".$this->getLang("installer_dbdriver_oci8")."</div>";
            }
            else {
                $strOci8Info = "<div class=\"error\">".$this->getLang("installer_dbdriver_na")." oci8</div>";
            }

            //configwizard_form
            $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "configwizard_form", true);
            $strReturn .= $this->objTemplates->fillTemplate(
                array(
                    "config_intro"     => $this->getLang("installer_config_intro"),
                    "config_hostname"  => $this->getLang("installer_config_dbhostname"),
                    "config_username"  => $this->getLang("installer_config_dbusername"),
                    "config_password"  => $this->getLang("installer_config_dbpassword"),
                    "config_port"      => $this->getLang("installer_config_dbport"),
                    "config_portinfo"  => $this->getLang("installer_config_dbportinfo"),
                    "config_driver"    => $this->getLang("installer_config_dbdriver"),
                    "config_dbname"    => $this->getLang("installer_config_dbname"),
                    "config_prefix"    => $this->getLang("installer_config_dbprefix"),
                    "config_save"      => $this->getLang("installer_config_write"),
                    "mysqliInfo"       => $strMysqliInfo,
                    "sqlite3Info"      => $strSqlite3Info,
                    "postgresInfo"     => $strPostgresInfo,
                    "oci8Info"         => $strOci8Info
                ), 
                $strTemplateID
            );
            $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php");

        }
        elseif($_POST["write"] == "true") {
            //check vor values
            if($_POST["hostname"] == "" || $_POST["username"] == "" || $_POST["password"] == "" || $_POST["dbname"] == "" || $_POST["driver"] == "") {
                header("Location: "._webpath_."/installer.php");
                return;
            }


            $strFileContent = "<?php\n";
            $strFileContent .= "/*\n Kajona V4 config-file.\n If you want to overwrite additional settings, copy them from /core/module_system/system/config/config.php into this file.\n*/";
            $strFileContent .= "\n";
            $strFileContent .= "  \$config['dbhost']               = '".$_POST["hostname"]."';                   //Server name \n";
            $strFileContent .= "  \$config['dbusername']           = '".$_POST["username"]."';                   //Username \n";
            $strFileContent .= "  \$config['dbpassword']           = '".$_POST["password"]."';                   //Password \n";
            $strFileContent .= "  \$config['dbname']               = '".$_POST["dbname"]."';                     //Database name \n";
            $strFileContent .= "  \$config['dbdriver']             = '".$_POST["driver"]."';                     //DB-Driver \n";
            $strFileContent .= "  \$config['dbprefix']             = '".$_POST["dbprefix"]."';                   //Table-prefix \n";
            $strFileContent .= "  \$config['dbport']               = '".$_POST["port"]."';                       //Database port \n";

            $strFileContent .= "\n";
            //and save to file
            file_put_contents($this->STR_PROJECT_CONFIG_FILE, $strFileContent);
            // and reload
            header("Location: "._webpath_."/installer.php?step=loginData");
        }

        $this->strOutput = $strReturn;
    }

    /**
     * Collects the data required to create a valid admin-login

     */
    public function adminLoginData() {
        $bitUserInstalled = false;
        $bitShowForm = true;
        $this->strOutput .= $this->getLang("installer_login_intro");

        //if user-module is already installed, skip this step
        try {
            $objUser = class_module_system_module::getModuleByName("user");
            if($objUser != null) {
                $bitUserInstalled = true;
            }
        }
        catch(class_exception $objE) {
        }


        if($bitUserInstalled) {
            $bitShowForm = false;
            $this->strOutput .= "<span class=\"green\">".$this->getLang("installer_login_installed")."</span>";
        }
        if(isset($_POST["write"]) && $_POST["write"] == "true") {
            $strUsername = $_POST["username"];
            $strPassword = $_POST["password"];
            $strEmail = $_POST["email"];
            //save to session
            if($strUsername != "" && $strPassword != "" && checkEmailaddress($strEmail)) {
                $bitShowForm = false;
                $this->objSession->setSession("install_username", $strUsername);
                $this->objSession->setSession("install_password", $strPassword);
                $this->objSession->setSession("install_email", $strEmail);
                header("Location: "._webpath_."/installer.php?step=install");
            }
        }

        if($bitShowForm) {
            $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "loginwizard_form", true);
            $this->strOutput .= $this->objTemplates->fillTemplate(
                array(
                    "login_username" => $this->getLang("installer_login_username"),
                    "login_password" => $this->getLang("installer_login_password"),
                    "login_email"    => $this->getLang("installer_login_email"),
                    "login_save"     => $this->getLang("installer_login_save")
                ), 
                $strTemplateID
            );
        }

        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php");
        if($bitUserInstalled || ($this->objSession->getSession("install_username") !== false && $this->objSession->getSession("install_password") !== false))
            $this->strForwardLink = $this->getForwardLink(_webpath_."/installer.php?step=install");
    }

    /**
     * Loads all installers available to this->arrInstaller

     */
    public function loadInstaller() {

        $objManager = new class_module_packagemanager_manager();
        $arrModules = $objManager->getAvailablePackages();

        $this->arrMetadata = array();
        foreach($arrModules as $objOneModule)
            if($objOneModule->getBitProvidesInstaller())
                $this->arrMetadata[] = $objOneModule;

    }

    /**
     * Loads all installers and requests a install / update link, if available

     */
    public function createModuleInstalls() {
        $strReturn = "";
        $strInstallLog = "";

        $objManager = new class_module_packagemanager_manager();

        //Is there a module to be updated?
        if(isset($_GET["update"])) {

            //search the matching modules
            foreach($this->arrMetadata as $objOneMetadata) {
                if($_GET["update"] == "installer_".$objOneMetadata->getStrTitle()) {
                    $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());
                    $strInstallLog .= $objHandler->installOrUpdate();
                }
            }

        }

        //module-installs to loop?
        if(isset($_POST["moduleInstallBox"]) && is_array($_POST["moduleInstallBox"])) {
            $arrModulesToInstall = $_POST["moduleInstallBox"];
            foreach($arrModulesToInstall as $strOneModule => $strValue) {

                //search the matching modules
                foreach($this->arrMetadata as $objOneMetadata) {
                    if($strOneModule == "installer_".$objOneMetadata->getStrTitle()) {
                        $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());
                        $strInstallLog .= $objHandler->installOrUpdate();
                    }
                }

            }
        }


        $this->strLogfile = $strInstallLog;
        $strReturn .= $this->getLang("installer_modules_found");

        $strRows = "";
        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_modules_row", true);
        $strTemplateIDInstallable = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_modules_row_installable", true);

        //Loading each installer

        foreach($this->arrMetadata as $objOneMetadata) {

            //skip samplecontent
            if($objOneMetadata->getStrTitle() == "samplecontent")
                continue;

            $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());

            $arrTemplate = array();
            $arrTemplate["module_name"] = $objHandler->getObjMetadata()->getStrTitle();
            $arrTemplate["module_nameShort"] = $objHandler->getObjMetadata()->getStrTitle();
            $arrTemplate["module_version"] = $objHandler->getObjMetadata()->getStrVersion();

            //generate the hint
            $arrTemplate["module_hint"] = "";

            if($objHandler->getVersionInstalled() !== null) {
                $arrTemplate["module_hint"] = $this->getLang("installer_versioninstalled", "system").$objHandler->getVersionInstalled();
            }
            else {
                //check missing modules
                $strRequired = "";
                $arrModules = explode(",", $objHandler->getObjMetadata()->getStrRequiredModules());
                foreach($arrModules as $strOneModule) {
                    if(trim($strOneModule) != "" && class_module_system_module::getModuleByName(trim($strOneModule)) === null)
                        $strRequired .= $strOneModule.", ";
                }

                if(trim($strRequired) != "") {
                    $arrTemplate["module_hint"] = $this->getLang("installer_modules_needed", "system").substr($strRequired, 0, -2);
                }
                else {
                    //check, if a min version of the system is needed
                    if($objOneMetadata->getStrMinVersion() != "") {
                        //the systems version to compare to
                        $objSystem = class_module_system_module::getModuleByName("system");
                        if($objSystem == null || version_compare($objOneMetadata->getStrMinVersion(), $objSystem->getStrVersion(), ">")) {
                            $arrTemplate["module_hint"] = $this->getLang("installer_systemversion_needed", "system").$objOneMetadata->getStrMinVersion()."<br />";
                        }
                    }
                }
            }

            if($objHandler->isInstallable()) {
                $strRows .= $this->objTemplates->fillTemplate($arrTemplate, $strTemplateIDInstallable);
            }
            else {
                $strRows .= $this->objTemplates->fillTemplate($arrTemplate, $strTemplateID);
            }

        }

        //wrap in form
        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_modules_form", true);
        $strReturn .= $this->objTemplates->fillTemplate(array("module_rows" => $strRows, "button_install" => $this->getLang("installer_install")), $strTemplateID);

        $this->strOutput .= $strReturn;
        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php?step=loginData");
        $this->strForwardLink = $this->getForwardLink(_webpath_."/installer.php?step=samplecontent");
    }


    /**
     * Installs, if available, the samplecontent

     */
    public function installSamplecontent() {
        $strReturn = "";
        $strInstallLog = "";

        $objManager = new class_module_packagemanager_manager();

        //Is there a module to be installed or updated?
        if(isset($_GET["update"])) {
            foreach($this->arrMetadata as $objOneMetadata) {
                if($objOneMetadata->getStrTitle() != "samplecontent")
                    continue;

                $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());
                $strInstallLog .= $objHandler->installOrUpdate();
            }
        }

        //module-installs to loop?
        if(isset($_POST["moduleInstallBox"]) && is_array($_POST["moduleInstallBox"])) {
            foreach($this->arrMetadata as $objOneMetadata) {
                if($objOneMetadata->getStrTitle() != "samplecontent")
                    continue;

                $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());
                $strInstallLog .= $objHandler->installOrUpdate();
            }
        }

        $this->strLogfile = $strInstallLog;
        $strReturn .= $this->getLang("installer_samplecontent");

        //Loading each installer
        $strRows = "";
        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_modules_row", true);
        $strTemplateIDInstallable = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_modules_row_installable", true);

        $bitInstallerFound = false;
        foreach($this->arrMetadata as $objOneMetadata) {

            if($objOneMetadata->getStrTitle() != "samplecontent")
                continue;

            $bitInstallerFound = true;

            $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());

            $arrTemplate = array();
            $arrTemplate["module_nameShort"] = $objOneMetadata->getStrTitle();
            $arrTemplate["module_name"] = $objOneMetadata->getStrTitle();
            $arrTemplate["module_version"] = $objOneMetadata->getStrVersion();

            //generate the hint
            $arrTemplate["module_hint"] = "";

            if($objHandler->getVersionInstalled() !== null) {
                $arrTemplate["module_hint"] = $this->getLang("installer_versioninstalled", "system").$objHandler->getVersionInstalled();
            }
            else {
                //check missing modules
                $strRequired = "";
                $arrModules = explode(",", $objHandler->getObjMetadata()->getStrRequiredModules());
                foreach($arrModules as $strOneModule) {
                    if(trim($strOneModule) != "" && class_module_system_module::getModuleByName(trim($strOneModule)) === null)
                        $strRequired .= $strOneModule.", ";
                }

                if(trim($strRequired) != "")
                    $arrTemplate["module_hint"] = $this->getLang("installer_modules_needed", "system").substr($strRequired, 0, -2);
            }

            if($objHandler->isInstallable())
                $strRows .= $this->objTemplates->fillTemplate($arrTemplate, $strTemplateIDInstallable);
            else
                $strRows .= $this->objTemplates->fillTemplate($arrTemplate, $strTemplateID);

        }

        if(!$bitInstallerFound)
            header("Location: "._webpath_."/installer.php?step=finish");

        //wrap in form
        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_samplecontent_form", true);
        $strReturn .= $this->objTemplates->fillTemplate(array("module_rows" => $strRows, "button_install" => $this->getLang("installer_install")), $strTemplateID);

        $this->strOutput .= $strReturn;
        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php?step=install");
        $this->strForwardLink = $this->getForwardLink(_webpath_."/installer.php?step=finish");
    }

    /**
     * The last page of the installer, showing a few infos and links how to go on

     */
    public function finish() {
        $strReturn = "";

        $this->objSession->sessionUnset("install_username");
        $this->objSession->sessionUnset("install_password");

        $strReturn .= $this->getLang("installer_finish_intro");
        $strReturn .= $this->getLang("installer_finish_hints");
        $strReturn .= $this->getLang("installer_finish_hints_update");
        $strReturn .= $this->getLang("installer_finish_closer");

        $this->strOutput = $strReturn;
        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php?step=samplecontent");
    }


    /**
     * Generates the sourrounding layout and embedds the installer-output
     *
     * @return string
     */
    public function getOutput() {
        if($this->strLogfile != "") {
            $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_log", true);
            $this->strLogfile = $this->objTemplates->fillTemplate(
                array(
                    "log_content" => $this->strLogfile,
                    "systemlog"   => $this->getLang("installer_systemlog")
                ), $strTemplateID
            );
        }


        //build the progress-entries
        $strCurrentCommand = (isset($_GET["step"]) ? $_GET["step"] : "");
        if($strCurrentCommand == "")
            $strCurrentCommand = "phpsettings";

        $arrProgressEntries = array(
            "phpsettings"   => $this->getLang("installer_step_phpsettings"),
            "config"        => $this->getLang("installer_step_dbsettings"),
            "loginData"     => $this->getLang("installer_step_adminsettings"),
            "install"       => $this->getLang("installer_step_modules"),
            "samplecontent" => $this->getLang("installer_step_samplecontent"),
            "finish"        => $this->getLang("installer_step_finish"),
        );

        $strProgress = "";
        $strTemplateEntryTodoID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_progress_entry", true);
        $strTemplateEntryCurrentID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_progress_entry_current", true);
        $strTemplateEntryDoneID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_progress_entry_done", true);

        $strTemplateEntryID = $strTemplateEntryDoneID;
        foreach($arrProgressEntries as $strKey => $strValue) {
            $arrTemplateEntry = array();
            $arrTemplateEntry["entry_name"] = $strValue;

            //choose the correct template section
            if($strCurrentCommand == $strKey) {
                $strProgress .= $this->objTemplates->fillTemplate($arrTemplateEntry, $strTemplateEntryCurrentID, true);
                $strTemplateEntryID = $strTemplateEntryTodoID;
            }
            else
                $strProgress .= $this->objTemplates->fillTemplate($arrTemplateEntry, $strTemplateEntryID, true);

        }
        $arrTemplate = array();
        $arrTemplate["installer_progress"] = $strProgress;
        $arrTemplate["installer_version"] = $this->strVersion;
        $arrTemplate["installer_output"] = $this->strOutput;
        $arrTemplate["installer_forward"] = $this->strForwardLink;
        $arrTemplate["installer_backward"] = $this->strBackwardLink;
        $arrTemplate["installer_logfile"] = $this->strLogfile;
        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_main", true);

        $strReturn = $this->objTemplates->fillTemplate($arrTemplate, $strTemplateID);
        $strReturn = $this->callScriptlets($strReturn);
        $this->objTemplates->setTemplate($strReturn);
        $this->objTemplates->deletePlaceholder();
        $strReturn = $this->objTemplates->getTemplate();
        return $strReturn;
    }


    /**
     * Calls the scriptlets in order to process additional tags and in order to enrich the content.
     *
     * @param $strContent
     *
     * @return string
     */
    private function callScriptlets($strContent) {
        $arrScriptletFiles = class_resourceloader::getInstance()->getFolderContent("/system/scriptlets", array(".php"));

        foreach($arrScriptletFiles as $strOneScriptlet) {
            $strOneScriptlet = uniSubstr($strOneScriptlet, 0, -4);
            /** @var $objScriptlet interface_scriptlet */
            $objScriptlet = new $strOneScriptlet();

            if($objScriptlet instanceof interface_scriptlet)
                $strContent = $objScriptlet->processContent($strContent);
        }

        return $strContent;
    }


    /**
     * Checks, if the config-file was filled with correct values
     *
     * @return bool
     */
    public function checkDefaultValues() {
        //use return true to disable config-check
        //return true;
        //Load the config to parse it
        return is_file($this->STR_PROJECT_CONFIG_FILE);
    }

    /**
     * Creates a forward-link
     *
     * @param string $strHref
     *
     * @return string
     */
    public function getForwardLink($strHref) {
        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_forward_link", true);
        return $this->objTemplates->fillTemplate(array("href" => $strHref, "text" => $this->getLang("installer_next")), $strTemplateID);
    }

    /**
     * Creates backward-link
     *
     * @param string $strHref
     *
     * @return string
     */
    public function getBackwardLink($strHref) {
        $strTemplateID = $this->objTemplates->readTemplate("/core/module_installer/installer.tpl", "installer_backward_link", true);
        return $this->objTemplates->fillTemplate(array("href" => $strHref, "text" => $this->getLang("installer_prev")), $strTemplateID);
    }

    /**
     * Loads a text
     *
     * @param string $strKey
     *
     * @return string
     * @deprecated use getLang instead
     */
    public function getText($strKey) {
        return $this->objLang->getLang($strKey, "system");
    }

    /**
     * Loads a text
     *
     * @param string $strKey
     *
     * @return string
     */
    public function getLang($strKey) {
        return $this->objLang->getLang($strKey, "system");
    }
}


//set admin to false
define("_admin_", false);

//Creating the Installer-Object
$objInstaller = new class_installer();
$objInstaller->action();
echo $objInstaller->getOutput();
