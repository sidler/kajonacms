<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_statsReportsTest extends class_testbase {

    public function testReports() {

        if(!defined("_skinwebpath_"))
            define("_skinwebpath_", "1");

        echo "processing reports...\n";

        $arrReportsInFs = class_resourceloader::getInstance()->getFolderContent("/admin/statsreports");

        $arrReports = array();
        foreach($arrReportsInFs as $strOneFile) {
            if(uniStripos($strOneFile, "class_stats_report") !== false) {
                $strClassname = uniSubstr($strOneFile, 0, -4);
                $objReport = new $strClassname(
                    class_carrier::getInstance()->getObjDB(),
                    class_carrier::getInstance()->getObjToolkit("admin"),
                    class_carrier::getInstance()->getObjLang()
                );

                if($objReport instanceof interface_admin_statsreports) {
                    $arrReports[$objReport->getReportTitle()] = $objReport;
                }

                $objStartDate = new class_date();
                $objStartDate->setPreviousDay();
                $objEndDate = new class_date();
                $objEndDate->setNextDay();
                $intStartDate = mktime(0, 0, 0, $objStartDate->getIntMonth() , $objStartDate->getIntDay(), $objStartDate->getIntYear());
                $intEndDate = mktime(0, 0, 0, $objEndDate->getIntMonth(), $objEndDate->getIntDay(), $objEndDate->getIntYear());
                $objReport->setEndDate($intEndDate);
                $objReport->setStartDate($intStartDate);
                $objReport->setInterval(2);
            }
        }

        /** @var interface_admin_statsreports $objReport */
        foreach($arrReports as $objReport) {
            ob_start();
            echo "processing report ".$objReport->getReportTitle()."\n";

            $objReport->getReport();
            $objReport->getReportGraph();
        }


    }
}

