<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_workflows_handler.php 4689 2012-06-15 15:59:43Z sidler $					    *
********************************************************************************************************/

/**
 * A workflow handler stores all metadata of a single workflow-handler.
 * This means, this is not the real workflow-instance running, but rather a wrapper to
 * metainfos provided to a single handler, e.g. configuration values.
 *
 *
 * @package module_workflows
 * @author sidler@mulchprod.de
 * @since 3.4
 *
 * @targetTable workflows_handler.workflows_handler_id
 */
class class_module_workflows_handler extends class_model implements interface_model, interface_admin_listable  {

    /**
     * @var string
     * @tableColumn workflows_handler.workflows_handler_class
     */
    private $strHandlerClass = "";

    /**
     * @var string
     * @tableColumn workflows_handler.workflows_handler_val1
     */
    private $strConfigVal1 = "";

    /**
     * @var string
     * @tableColumn workflows_handler.workflows_handler_val2
     */
    private $strConfigVal2 = "";

    /**
     * @var string
     * @tableColumn workflows_handler.workflows_handler_val3
     */
    private $strConfigVal3 = "";


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("moduleId", _workflows_module_id_);
        $this->setArrModuleEntry("modul", "workflows");

        //base class
        parent::__construct($strSystemid);
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon() {
        return "icon_workflow.png";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        if($this->getObjInstanceOfHandler() != null)
            return $this->getObjInstanceOfHandler()->getStrName();
        else
            return "";
    }


    /**
     * Loads a single handler-class, identified by the mapped class
     *
     * @param string $strClass
     * @return class_module_workflows_handler
     */
    public static function getHandlerByClass($strClass) {
        $strQuery = "SELECT system_id FROM
                            "._dbprefix_."system,
                            "._dbprefix_."workflows_handler
                      WHERE system_id = workflows_handler_id
                        AND workflows_handler_class = ?";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strClass));
        if(count($arrRow) > 0)
            return new class_module_workflows_handler($arrRow["system_id"]);
        else
            return null;
    }

    /**
     * Retrieves the list of workflow-handlers available
     *
     * @param bool|int $intStart
     * @param bool|int $intEnd
     * @return class_module_workflows_handler[]
     */
    public static function getAllworkflowHandlers($intStart = false, $intEnd = false) {
        $strQuery = "SELECT system_id FROM
                            "._dbprefix_."system,
                            "._dbprefix_."workflows_handler
                      WHERE system_id = workflows_handler_id
                   ORDER BY workflows_handler_class ASC";


        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_module_workflows_handler($arrSingleRow["system_id"]);
        }

        return $arrReturn;
    }


    /**
     * Counts the number of workflows handlers available
     * @return int
     */
    public static function getAllworkflowHandlersCount() {
        $strQuery = "SELECT COUNT(*) FROM
                            "._dbprefix_."system,
                            "._dbprefix_."workflows_handler
                      WHERE system_id = workflows_handler_id";

       $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
       return $arrRow["COUNT(*)"];
    }


    /**
     * Synchronizes the list of handlers available on the filesystem compared to the list
     * of handlers available in the database.
     * Adds or removes handlers from or to the database.
     *
     */
    public static function synchronizeHandlerList() {
        //load the list of handlers in the filesystem
        $objFilesystem = new class_filesystem();
        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/system/workflows", array(".php"));
        foreach($arrFiles as $strOneFile) {
            $strClassname = uniStrReplace(".php", "", $strOneFile);
            $objWorkflow = class_module_workflows_handler::getHandlerByClass($strClassname);
            if($objWorkflow == null) {
                $objWorkflow = new class_module_workflows_handler();
                $objWorkflow->setStrHandlerClass($strClassname);

                $arrDefault = $objWorkflow->getObjInstanceOfHandler()->getDefaultValues();
                if(isset($arrDefault[0]))   $objWorkflow->setStrConfigVal1($arrDefault[0]);
                if(isset($arrDefault[1]))   $objWorkflow->setStrConfigVal2($arrDefault[1]);
                if(isset($arrDefault[2]))   $objWorkflow->setStrConfigVal3($arrDefault[2]);

                $objWorkflow->updateObjectToDb();
            }
        }

        //find workflows to remove
        $arrWorkflows = self::getAllworkflowHandlers();
        foreach($arrWorkflows as $objOneWorkflow) {
            if(!in_array($objOneWorkflow->getStrHandlerClass().".php", $arrFiles))
                $objOneWorkflow->deleteObject();
        }
    }

    /**
     * Creates a non-initialized instance of the concrete handler
     *
     * @return interface_workflows_handler
     */
    public function getObjInstanceOfHandler() {
        if($this->getStrHandlerClass() != "") {
            $strClassname = uniStrReplace(".php", "", $this->getStrHandlerClass());
            return new $strClassname();
        }
        else
            return null;
    }



    public function getStrHandlerClass() {
        return $this->strHandlerClass;
    }

    public function setStrHandlerClass($strHandlerClass) {
        $this->strHandlerClass = $strHandlerClass;
    }

    /**
     * @return string
     * @fieldType text
     */
    public function getStrConfigVal1() {
        return $this->strConfigVal1;
    }

    public function setStrConfigVal1($strConfigVal1) {
        $this->strConfigVal1 = $strConfigVal1;
    }

    /**
     * @return string
     * @fieldType text
     */
    public function getStrConfigVal2() {
        return $this->strConfigVal2;
    }

    public function setStrConfigVal2($strConfigVal2) {
        $this->strConfigVal2 = $strConfigVal2;
    }

    /**
     * @return string
     * @fieldType text
     */
    public function getStrConfigVal3() {
        return $this->strConfigVal3;
    }

    public function setStrConfigVal3($strConfigVal3) {
        $this->strConfigVal3 = $strConfigVal3;
    }

}