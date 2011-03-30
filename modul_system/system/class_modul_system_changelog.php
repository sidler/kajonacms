<?php
/*"******************************************************************************************************
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_user_log.php 3530 2011-01-06 12:30:26Z sidler $                                    *
********************************************************************************************************/

/**
 * The changelog is a global wrapper to the gui-based logging.
 * Changes should reflect user-changes and not internal system-logs.
 * For logging to the logfile, see class_logger.
 * But: entries added to the changelog are copied to the systemlog leveled as information, too.
 * Changes are stored as a flat list in the database only and have no representation within the
 * system-table. This means there are no common system-id relations.
 * Have a look at the memento pattern by Gamma et al. to get a glance at the conecptional behaviour.
 *
 * @package modul_system
 * @author sidler@mulchprod.de
 * @see class_logger
 */
class class_modul_system_changelog extends class_model implements interface_model  {

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_system";
		$arrModul["moduleId"] 			= _system_modul_id_;
		$arrModul["table"]       		= _dbprefix_."changelog";
		$arrModul["modul"]				= "system";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array();
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "system changelog";
    }

    /**
     * Initalises the current object, if a systemid was given
     */
    public function initObject() {
    }

    /**
     * Generates a new entry in the modification log storing all relevant information.
     * Creates an entry in the systemlog leveled as information, too.
     * By default entries with same old- and new-values are dropped.
     * The passed object has to implement interface_versionable.
     *
     *
     * @param interface_versionable $objSourceModel
     * @param string $strAction
     * @param bool $bitForceEntry if set to true, an entry will be created even if the values didn't change
     * @return bool
     */
    public function createLogEntry($objSourceModel, $strAction, $bitForceEntry = false) {
        $bitReturn = true;

        $arrChanges = $objSourceModel->getChangedFields($strAction);
        if(is_array($arrChanges)) {
            foreach($arrChanges as $arrChangeSet) {
                $strOldvalue = $arrChangeSet["oldvalue"];
                $strNewvalue = $arrChangeSet["newvalue"];
                $strProperty= $arrChangeSet["property"];

                if(!$bitForceEntry && ($strOldvalue == $strNewvalue) )
                    continue;

                class_logger::getInstance()->addLogRow("change in class ".$objSourceModel->getClassname()."@".$strAction." systemid: ".$objSourceModel->getSystemid()." property: ".$strProperty." old value: ".uniStrTrim($strOldvalue, 60)." new value: ".uniStrTrim($strNewvalue, 60), class_logger::$levelInfo);

                $strQuery = "INSERT INTO ".$this->arrModule["table"]."
                     (change_id,
                      change_date,
                      change_systemid,
                      change_user,
                      change_class,
                      change_action,
                      change_property,
                      change_oldvalue,
                      change_newvalue) VALUES
                     (?,?,?,?,?,?,?,?,?)";

                $bitReturn = $bitReturn && $this->objDB->_pQuery($strQuery, array(
                    generateSystemid(),
                    class_date::getCurrentTimestamp(),
                    $objSourceModel->getSystemid(),
                    $this->objSession->getUserID(),
                    $objSourceModel->getClassname(),
                    $strAction,
                    $strProperty,
                    $strOldvalue,
                    $strNewvalue
                ));
            }
        }

        return $bitReturn;
    }

    /**
     * Creates the list of logentries, either without a systemid-based filter
     * or limited to the given systemid.
     *
     * @param string $strSystemidFilter
     * @param int $intStartDate
     * @param int $intEndDate
     * @return class_changelog_container
     */
    public static function getLogEntries($strSystemidFilter = "", $intStart = null, $intEnd = null) {
        $strQuery = "SELECT *
                       FROM "._dbprefix_."changelog
                      ".($strSystemidFilter != "" ? " WHERE change_systemid = ? ": "")."
                   ORDER BY change_date DESC";

        $arrParams = array();
        if($strSystemidFilter != "")
            $arrParams[] = $strSystemidFilter;

        if($intStart != null && $intEnd != null)
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, $arrParams, $intStart, $intEnd);
        else
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);

        $arrReturn = array();
        foreach($arrRows as $arrRow)
            $arrReturn[] = new class_changelog_container($arrRow["change_date"], $arrRow["change_systemid"], $arrRow["change_user"],
                           $arrRow["change_class"], $arrRow["change_action"], $arrRow["change_property"], $arrRow["change_oldvalue"], $arrRow["change_newvalue"]);

        return $arrReturn;
    }

    /**
     * Counts the number of logentries available
     *
     * @param string $strSystemidFilter
     * @return int
     */
    public static function getLogEntriesCount($strSystemidFilter = "") {
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."changelog
                      ".($strSystemidFilter != "" ? " WHERE change_systemid = ? ": "")."
                   ORDER BY change_date DESC";

        $arrParams = array();
        if($strSystemidFilter != "")
            $arrParams[] = $strSystemidFilter;

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrParams);
        return $arrRow["COUNT(*)"];
    }
   
}


/**
 * Simple data-container for logentries.
 * Has no regular use.
 */
final class class_changelog_container {
    private $objDate;
    private $strSystemid;
    private $strUserId;
    private $strClass;
    private $strAction;
    private $strProperty;
    private $strOldValue;
    private $strNewValue;

    function __construct($intDate, $strSystemid, $strUserId, $strClass, $strAction, $strProperty, $strOldValue, $strNewValue) {
        $this->objDate = new class_date($intDate);
        $this->strSystemid = $strSystemid;
        $this->strUserId = $strUserId;
        $this->strClass = $strClass;
        $this->strAction = $strAction;
        $this->strProperty = $strProperty;
        $this->strOldValue = $strOldValue;
        $this->strNewValue = $strNewValue;
    }

    /**
     *
     * @return interface_versionable
     */
    public function getObjTarget() {
        if(class_exists($this->strClass))
            return new $this->strClass($this->strSystemid);
        else
            return null;
    }

    public function getObjDate() {
        return $this->objDate;
    }

    public function getStrSystemid() {
        return $this->strSystemid;
    }

    public function getStrUserId() {
        return $this->strUserId;
    }

    public function getStrUsername() {
        $objUser = new class_modul_user_user($this->getStrUserId());
        return $objUser->getStrUsername();
    }

    public function getStrClass() {
        return $this->strClass;
    }

    public function getStrAction() {
        return $this->strAction;
    }

    public function getStrOldValue() {
        return $this->strOldValue;
    }

    public function getStrNewValue() {
        return $this->strNewValue;
    }

    public function getStrProperty() {
        return $this->strProperty;
    }


}
?>