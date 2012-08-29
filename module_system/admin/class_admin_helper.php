<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                            *
********************************************************************************************************/

/**
 * A class holding common helper-methods for the backend.
 * The main purpose is to reduce the code stored at class_admin
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_admin_helper {


    const STR_PAGES_GROUP          = "1_pages";
    const STR_SYSTEM_GROUP         = "3_system";
    const STR_USERCONTENT_GROUP    = "2_usercontent";

    /**
     * Adds a menu-button to the second entry of the path-array. The menu renders the list of all modules installed,
     * including a quick-jump link.
     *
     * @static
     *
     * @param $arrPathEntries
     * @param string $strSourceModule
     *
     * @internal param array $arrModuleActions
     * @return string
     */
    public static function getAdminPathNavi($arrPathEntries, $strSourceModule = "") {
        //modify some of the entries
        $arrMenuEntries = array();
        $arrModules = class_module_system_module::getModulesInNaviAsArray(class_module_system_aspect::getCurrentAspectId());
        foreach($arrModules as $arrOneModule) {
            $arrMenuEntries[] = array(
                "name" => class_carrier::getInstance()->getObjLang()->getLang("modul_titel", $arrOneModule["module_name"]),
                "onclick" => "location.href='".getLinkAdminHref($arrOneModule["module_name"], "", "", false)."'"
            );
        }


        $strModuleMenuId = generateSystemid();
        $strModuleSwitcher = "
                    <span class='dropdown moduleSwitch'><a href=\"#\" data-toggle='dropdown' role='button' onclick=\"KAJONA.admin.contextMenu.showElementMenu('".$strModuleMenuId."', this);\">+</a>
                    ".class_carrier::getInstance()->getObjToolkit("admin")->registerMenu($strModuleMenuId, $arrMenuEntries)."</span>";

        $arrFinalEntries = array(
            array_shift($arrPathEntries),
            $strModuleSwitcher
        );


        if($strSourceModule != "") {
            $objModule = class_module_system_module::getModuleByName($strSourceModule);
            if($objModule != null) {
                $arrModuleActions = self::getModuleActionNaviHelper($objModule->getAdminInstanceOfConcreteModule());
                $arrActionMenuEntries = array();
                foreach($arrModuleActions as $strOneAction) {
                    if($strOneAction != "") {
                        $arrLink = splitUpLink($strOneAction);

                        $arrActionMenuEntries[] = array(
                            "name" => $arrLink["name"],
                            "onclick" => "location.href='".$arrLink["href"]."'"
                        );
                    }
                }

                $strModuleMenuId = generateSystemid();
                $strActionSwitcher = "
                        <span class='dropdown moduleSwitch'><a href=\"#\" data-toggle='dropdown' role='button' onclick=\"KAJONA.admin.contextMenu.showElementMenu('".$strModuleMenuId."', this);\">+</a>
                        ".class_carrier::getInstance()->getObjToolkit("admin")->registerMenu($strModuleMenuId, $arrActionMenuEntries)."</span>";


                if(isset($arrPathEntries[0]) && $arrPathEntries[0] != "")
                    $arrFinalEntries[] = array_shift($arrPathEntries);
                $arrFinalEntries[] = $strActionSwitcher;
            }
        }


        $arrFinalEntries = array_merge($arrFinalEntries, $arrPathEntries);

        return class_carrier::getInstance()->getObjToolkit("admin")->getPathNavigation($arrFinalEntries);

    }

    /**
     * Writes the main backend navigation, so collects
     * all modules of the current aspect
     * Creates a list of all installed modules
     * Internal helper, collects all modules and prepares the lins
     *
     * @return array
     */
    public static function getOutputMainNaviHelper() {
        if(class_carrier::getInstance()->getObjSession()->isLoggedin()) {
            //Loading all Modules
            $arrModules = class_module_system_module::getModulesInNaviAsArray(class_module_system_aspect::getCurrentAspectId());
            $intI = 0;
            $arrModuleRows = array();
            foreach ($arrModules as $arrModule) {
                $objCommon = new class_module_system_common($arrModule["module_id"]);
                if($objCommon->rightView()) {
                    //Generate a view infos
                    $arrModuleRows[$intI]["rawName"] = $arrModule["module_name"];
                    $arrModuleRows[$intI]["name"] = class_carrier::getInstance()->getObjLang()->getLang("modul_titel", $arrModule["module_name"]);
                    $arrModuleRows[$intI]["link"] = getLinkAdmin($arrModule["module_name"], "", "", $arrModule["module_name"], $arrModule["module_name"], "", true, "adminModuleNavi");
                    $arrModuleRows[$intI]["href"] = getLinkAdminHref($arrModule["module_name"], "");
                    $intI++;
                }
            }
            return $arrModuleRows;
        }

        return array();
    }

    /**
     * Fetches the list of action for a single module
     * @static
     * @param class_admin $objAdminModule
     * @return array
     */
    public static function getModuleActionNaviHelper(class_admin $objAdminModule) {
        if(class_carrier::getInstance()->getObjSession()->isLoggedin()) {
            $objModule = $objAdminModule->getObjModule();
            $arrItems = $objAdminModule->getOutputModuleNavi();
            $arrFinalItems = array();
            //build array of final items
            foreach($arrItems as $arrOneItem) {
                if($arrOneItem[0] == "")
                    $bitAdd = true;
                else
                    $bitAdd = class_carrier::getInstance()->getObjRights()->validatePermissionString($arrOneItem[0], $objModule);

                if($bitAdd || $arrOneItem[1] == "")
                    $arrFinalItems[] = $arrOneItem[1];
            }

            return $arrFinalItems;
        }
        return array();
    }


}
