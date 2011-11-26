<?php

require_once (dirname(__FILE__)."/../system/class_testbase.php");

class class_test_autonavigationtest extends class_testbase  {

    private static $strFolderSystemid;
    private static $strPage1Systemid;
    private static $strPage2Systemid;
    private static $strPage2aSystemid;
    
    public function testSetup() {
        //creating a new page-node structure
        $objFolder = new class_modul_pages_folder();
        $objFolder->setStrName("naviautotest");
        $objFolder->updateObjectToDb();
        self::$strFolderSystemid = $objFolder->getSystemid();
        
        $objPage1 = new class_modul_pages_page();
        $objPage1->setStrName("testpage1");
        $objPage1->setStrBrowsername("testpage1");
        $objPage1->setIntType(class_modul_pages_page::$INT_TYPE_PAGE);
        $objPage1->setStrTemplate("kajona_demo.tpl");
        $objPage1->updateObjectToDb($objFolder->getSystemid());
        self::$strPage1Systemid = $objPage1->getSystemid();
        
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->setStrLanguage($objPage1->getStrAdminLanguageToWorkOn());
        $objPagelement->updateObjectToDb($objPage1->getSystemid());
        
        
        $objPage2 = new class_modul_pages_page();
        $objPage2->setStrName("testpage2");
        $objPage2->setStrBrowsername("testpage2");
        $objPage2->setIntType(class_modul_pages_page::$INT_TYPE_ALIAS);
        $objPage2->setStrAlias("testpage2a");
        $objPage2->updateObjectToDb($objFolder->getSystemid());
        self::$strPage2Systemid = $objPage2->getSystemid();
        
        $objPage3 = new class_modul_pages_page();
        $objPage3->setStrName("testpage2a");
        $objPage3->setStrBrowsername("testpage2a");
        $objPage3->setIntType(class_modul_pages_page::$INT_TYPE_PAGE);
        $objPage3->setStrTemplate("kajona_demo.tpl");
        $objPage3->updateObjectToDb($objPage2->getSystemid());
        self::$strPage2aSystemid = $objPage3->getSystemid();
        
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->setStrLanguage($objPage3->getStrAdminLanguageToWorkOn());
        $objPagelement->updateObjectToDb($objPage3->getSystemid());
     
        class_carrier::getInstance()->getObjDB()->flushQueryCache();
    }
    
    /**
     * @depends testSetup
     */
    public function testGeneration() {
        
        class_carrier::getInstance()->getObjDB()->flushQueryCache();
        
        $objTestNavigation = new class_modul_navigation_tree();
        $objTestNavigation->setStrName("autotest");
        
        $objTestNavigation->setStrFolderId(self::$strFolderSystemid);
        $objTestNavigation->updateObjectToDb();
        
        $arrNodes = $objTestNavigation->getCompleteNaviStructure();
        
            
        $this->assertEquals(2, count($arrNodes["subnodes"]));

        $objFirstNode = $arrNodes["subnodes"][0]["node"];
        $this->assertEquals("testpage1", $objFirstNode->getStrName());
        $this->assertEquals("testpage1", $objFirstNode->getStrPageI());

        $objFirstNode = $arrNodes["subnodes"][1]["node"];
        $this->assertEquals("testpage2", $objFirstNode->getStrName());
        $this->assertEquals("testpage2a", $objFirstNode->getStrPageI());
        
        
        $arrNodesOnSecLevel = $arrNodes["subnodes"][1]["subnodes"][0];
        $this->assertEquals(0, count($arrNodesOnSecLevel["subnodes"]));
        
        
        $objFirstNode = $arrNodesOnSecLevel["node"];
        $this->assertEquals("testpage2a", $objFirstNode->getStrName());
        $this->assertEquals("testpage2a", $objFirstNode->getStrPageI());    
        
        
        $objTestNavigation->deleteNavigation();
    }
    
    
    /**
     * @depends testGeneration
     */
    public function testFinalize() {
        class_carrier::getInstance()->getObjDB()->flushQueryCache();
        //delete pages and folders created
        
        $objPage = new class_modul_pages_page(self::$strPage2aSystemid);
        $objPage->deletePage();
        
        $objPage = new class_modul_pages_page(self::$strPage2Systemid);
        $objPage->deletePage();
        
        $objPage = new class_modul_pages_page(self::$strPage1Systemid);
        $objPage->deletePage();
        
        $objFolder = new class_modul_pages_folder(self::$strFolderSystemid);
        $objFolder->deleteFolder();
    }

}

?>