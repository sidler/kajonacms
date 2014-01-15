<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: $                                  *
********************************************************************************************************/

/**
 * General object to build / rebuild / update the search-index.
 * Registers for record-updated events in order to update the index of an object.
 *
 * @package module_search
 * @author tim.kiefer@kojikui.de
 */
class class_module_search_indexwriter implements interface_recordupdated_listener, interface_recorddeleted_listener {
    private $objConfig = null;
    private $objDB = null;

    function __construct() {
        //Generating all the needed objects. For this we use our cool cool carrier-object
        //take care of loading just the necessary objects
        $this->objConfig = class_carrier::getInstance()->getObjConfig();
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }

    /**
     * Returns the number of documents currently in the index
     * @return int
     */
    public function getNumberOfDocuments() {
        $arrRow = $this->objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."search_index_document", array());
        return $arrRow["COUNT(*)"];
    }

    /**
     * Returns the number of entries currently in the index
     * @return int
     */
    public function getNumberOfContentEntries() {
        $arrRow = $this->objDB->getPRow("SELECT COUNT(*) FROM "._dbprefix_."search_index_content", array());
        return $arrRow["COUNT(*)"];
    }

    /**
     * Called whenever a records was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     * @param $strSystemid
     * @param string $strSourceClass The class-name of the object deleted
     *
     * @return bool
     */
    public function handleRecordDeletedEvent($strSystemid, $strSourceClass) {
        return $this->removeRecordFromIndex($strSystemid);
    }

    /**
     * Removes an entry from the index, based on the systemid. Removes the indexed content and the document.
     * @param $strSystemid
     *
     * @return bool
     */
    private function removeRecordFromIndex($strSystemid) {
        $arrRow = $this->objDB->getPRow("SELECT * FROM "._dbprefix_."search_index_document WHERE search_index_system_id = ?", array($strSystemid));

        if(isset($arrRow["search_index_document_id"])) {
            $this->objDB->_pQuery("DELETE FROM "._dbprefix_."search_index_content WHERE search_index_content_id = ?", array($arrRow["search_index_document_id"]));
            $this->objDB->_pQuery("DELETE FROM "._dbprefix_."search_index_document WHERE search_index_document_id = ?", array($arrRow["search_index_document_id"]));
        }

        return true;
    }



    /**
     * The event is triggered after the source-object was updated to the database.
     *
     * @param class_model $objRecord
     *
     * @return bool
     */
    public function handleRecordUpdatedEvent($objRecord) {
        if(class_module_system_module::getModuleByName("search") !== null)
            $this->indexObject($objRecord);
    }


    public function indexObject(class_model $objInstance) {

        if($objInstance instanceof class_module_pages_pageelement) {
            $objInstance = $objInstance->getConcreteAdminInstance();
            if($objInstance != null)
                $objInstance->loadElementData();
        }

        if($objInstance == null)
            return;

        $objSearchDocument = new class_module_search_document();
        $objSearchDocument->setDocumentId(generateSystemid());
        $objSearchDocument->setStrSystemId($objInstance->getSystemid());

        $objReflection = new class_reflection($objInstance);
        $arrProperties = $objReflection->getPropertiesWithAnnotation("@addSearchIndex");
        foreach($arrProperties as $strPropertyName => $strAnnotationValue) {
            $getter = $objReflection->getGetter($strPropertyName);
            $strField = $objReflection->getAnnotationValueForProperty($strPropertyName, class_orm_mapper::STR_ANNOTATION_TABLECOLUMN);
            $strContent = $objInstance->$getter();
            //TODO sir: changed first param from db-field to property name since there may be indexable fields not stored directly to the database
            $objSearchDocument->addContent($strPropertyName, $strContent);
        }

        //trigger event-listeners
        class_core_eventdispatcher::notifyListeners("interface_objectindexed_listener", "handleObjectIndexedEvent", array($objInstance, $objSearchDocument));

        $this->updateSearchDocumentToDb($objSearchDocument);
    }

    public function indexRebuild() {
        $this->clearIndex();
        $arrObj = $this->getIndexableEntries();

        foreach($arrObj as $objObj) {
            $this->indexObject(class_objectfactory::getInstance()->getObject($objObj["system_id"]));
        }
    }

    private function getIndexableEntries(){
        //Load possible existing document if exists
        $strQuery = "SELECT * FROM " . _dbprefix_ . "system ";

        return $this->objDB->getPArray($strQuery, array());
    }

    public function clearIndex() {
        // Delete existing entries
        $strQuery = "TRUNCATE TABLE " . _dbprefix_ . "search_index_document";
        $this->objDB->_pQuery($strQuery, array());

        $strQuery = "TRUNCATE TABLE " . _dbprefix_ . "search_index_content";
        $this->objDB->_pQuery($strQuery, array());
    }

    /**
     * @param class_module_search_document $objSearchDocument
     * @return bool
     */
    public function updateSearchDocumentToDb($objSearchDocument) {

        //Load possible existing document if exists
        $strQuery = "SELECT * FROM " . _dbprefix_ . "search_index_document " .
            "WHERE search_index_system_id = ?";

        $arrSearchDocument = $this->objDB->getPRow($strQuery, array($objSearchDocument->getStrSystemId()));

        // Delete existing entries
        if(count($arrSearchDocument) > 0) {
            $strDocumentId = $arrSearchDocument["search_index_document_id"];


            $strQuery = "DELETE FROM " . _dbprefix_ . "search_index_document
            WHERE search_index_system_id = ?";
            $this->objDB->_pQuery($strQuery, array($objSearchDocument->getStrSystemId()));

            $strQuery = "DELETE FROM " . _dbprefix_ . "search_index_content
            WHERE search_index_content_document_id = ?";
            $this->objDB->_pQuery($strQuery, array($strDocumentId));
        }

        //insert search document
        $strQuery = "INSERT INTO " . _dbprefix_ . "search_index_document
                        (search_index_document_id, search_index_system_id) VALUES
                        (?, ?)";
        $this->objDB->_pQuery($strQuery, array($objSearchDocument->getDocumentId(), $objSearchDocument->getStrSystemId()));

        foreach($objSearchDocument->getContent() as $objSearchContent)
            $this->updateSearchContentToDb($objSearchContent);
    }

    /**
     * @param class_module_search_content $objSearchContent
     */
    private function updateSearchContentToDb($objSearchContent) {
        //insert search document
        $strQuery = "INSERT INTO " . _dbprefix_ . "search_index_content
                        (search_index_content_id, search_index_content_field_name, search_index_content_content, search_index_content_score, search_index_content_document_id) VALUES
                        (?, ?, ?, ?, ?)";
        $this->objDB->_pQuery($strQuery, array($objSearchContent->getStrId(), $objSearchContent->getFieldName(), $objSearchContent->getContent(), $objSearchContent->getScore(), $objSearchContent->getDocumentId()));
    }

}
