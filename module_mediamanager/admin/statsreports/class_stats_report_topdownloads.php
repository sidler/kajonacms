<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                         *
********************************************************************************************************/

/**
 * This plugin show the list of top download, served by the downloads-module
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 */
class class_stats_report_topdownloads implements interface_admin_statsreports {

    //class vars
    private $intDateStart;
    private $intDateEnd;
    private $intInterval;

    private $objTexts;
    private $objToolkit;
    private $objDB;

    /**
     * Constructor
     */
    public function __construct(class_db $objDB, class_toolkit_admin $objToolkit, class_lang $objTexts) {
        $this->objTexts = $objTexts;
        $this->objToolkit = $objToolkit;
        $this->objDB = $objDB;
    }

    /**
     * Returns the name of extension/plugin the objects wants to contribute to.
     *
     * @return string
     */
    public static function getExtensionName() {
        return "core.stats.admin.statsreport";
    }

    /**
     * @param int $intEndDate
     * @return void
     */
    public function setEndDate($intEndDate) {
        $this->intDateEnd = $intEndDate;
    }

    /**
     * @param int $intStartDate
     * @return void
     */
    public function setStartDate($intStartDate) {
        $this->intDateStart = $intStartDate;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->objTexts->getLang("stats_toptitle", "mediamanager");
    }

    /**
     * @return bool
     */
    public function isIntervalable() {
        return true;
    }

    /**
     * @param int $intInterval
     * @return void
     */
    public function setInterval($intInterval) {
        $this->intInterval = $intInterval;
    }

    /**
     * @return string
     */
    public function getReport() {
        $strReturn = "";

        $arrLogsRaw = $this->getLogbookData();
        $arrLogs = array();
        $intI = 0;
        foreach($arrLogsRaw as $intKey => $arrOneLog) {
            if($intI++ >= _stats_nrofrecords_) {
                break;
            }

            $arrLogs[$intKey][0] = $intI;
            $arrLogs[$intKey][1] = $arrOneLog["downloads_log_file"];
            $arrLogs[$intKey][2] = $arrOneLog["amount"];
        }
        //Create a data-table
        $arrHeader = array();
        $arrHeader[0] = "#";
        $arrHeader[1] = $this->objTexts->getLang("header_file", "mediamanager");
        $arrHeader[2] = $this->objTexts->getLang("header_amount", "mediamanager");
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrLogs);

        return $strReturn;
    }

    /**
     * Loads the records of the dl-logbook
     *
     * @return mixed
     */
    private function getLogbookData() {
        $strQuery = "SELECT COUNT(*) as amount, downloads_log_file
					  FROM " . _dbprefix_ . "mediamanager_dllog
					  WHERE downloads_log_date > ?
				        AND downloads_log_date <= ?
					  GROUP BY downloads_log_file
					  ORDER BY amount DESC";

        return $this->objDB->getPArray($strQuery, array($this->intDateStart, $this->intDateEnd), 0, _stats_nrofrecords_ - 1);
    }

    /**
     * @return array
     */
    public function getReportGraph() {
        $arrReturn = array();
        //generate a graph showing dls per interval
        //--- XY-Plot -----------------------------------------------------------------------------------
        //calc number of plots
        $arrPlots = array();
        $intCount = 1;
        $arrDownloads = $this->getLogbookData();
        if(count($arrDownloads) > 0) {
            foreach($arrDownloads as $arrOneDownload) {
                if($intCount++ <= 4) {
                    $arrPlots[$arrOneDownload["downloads_log_file"]] = array();
                }
                else {
                    break;
                }

            }

            $arrTickLabels = array();

            $intGlobalEnd = $this->intDateEnd;
            $intGlobalStart = $this->intDateStart;

            $this->intDateEnd = $this->intDateStart + 60 * 60 * 24 * $this->intInterval;

            $intCount = 0;
            while($this->intDateStart <= $intGlobalEnd) {
                $arrDownloads = $this->getLogbookData();
                //init plot array for this period
                $arrTickLabels[$intCount] = date("d.m.", $this->intDateStart);
                foreach($arrPlots as $strFile => &$arrOnePlot) {
                    $arrOnePlot[$intCount] = 0;
                    foreach($arrDownloads as $arrOneDownload) {
                        if($arrOneDownload["downloads_log_file"] == $strFile) {
                            $arrOnePlot[$intCount] += $arrOneDownload["amount"];
                        }
                    }
                }
                //increase start & end-date
                $this->intDateStart = $this->intDateEnd;
                $this->intDateEnd = $this->intDateStart + 60 * 60 * 24 * $this->intInterval;
                $intCount++;
            }
            //create graph
            if($intCount > 1) {
                $objGraph = class_graph_factory::getGraphInstance();
                $objGraph->setArrXAxisTickLabels($arrTickLabels);

                foreach($arrPlots as $arrPlotName => $arrPlotData) {
                    $objGraph->addLinePlot($arrPlotData, $arrPlotName);
                }
                $objGraph->renderGraph();
                $arrReturn[] = $objGraph->renderGraph();
            }
            //reset global dates
            $this->intDateEnd = $intGlobalEnd;
            $this->intDateStart = $intGlobalStart;
        }
        return $arrReturn;
    }

}
