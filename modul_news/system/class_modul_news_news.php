<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Model for a news itself
 *
 * @package modul_news
 * @author sidler@mulchprod.de
 */
class class_modul_news_news extends class_model implements interface_model  {

    private $strTitle = "";
    private $strImage = "";
    private $intHits = 0;
    private $strIntro = "";
    private $strText = "";

    private $longDateStart = 0;
    private $longDateEnd = 0;
    private $longDateSpecial = 0;

    private $arrCats = null;

    private $bitTitleChanged = false;

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_news";
		$arrModul["moduleId"] 			= _news_modul_id_;
		$arrModul["table"]       		= _dbprefix_."news";
		$arrModul["table2"]       		= _dbprefix_."news_member";
		$arrModul["modul"]				= "news";

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
        return array(_dbprefix_."news" => "news_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "news posting ".$this->getStrTitle();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
         $strQuery = "SELECT * FROM ".$this->arrModule["table"].",
	                "._dbprefix_."system, "._dbprefix_."system_date
	                WHERE system_id = news_id
	                  AND system_id = system_date_id
	                  AND system_id = ?";
         $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
         $this->setStrImage($arrRow["news_image"]);
         $this->setStrIntro($arrRow["news_intro"]);
         $this->setStrNewstext($arrRow["news_text"]);
         $this->setStrTitle($arrRow["news_title"]);
         $this->setIntHits($arrRow["news_hits"]);
         $this->setIntDateEnd($arrRow["system_date_end"]);
         $this->setIntDateStart($arrRow["system_date_start"]);
         $this->setIntDateSpecial($arrRow["system_date_special"]);

         $this->bitTitleChanged = false;
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {
    
        $objStartDate = null;
        $objEndDate = null;
        $objSpecialDate = null;

        if($this->getIntDateStart() != 0 && $this->getIntDateStart() != "")
            $objStartDate = new class_date($this->getIntDateStart());

        if($this->getIntDateEnd() != 0 && $this->getIntDateEnd() != "")
            $objEndDate = new class_date($this->getIntDateEnd());

        if($this->getIntDateSpecial() != 0 && $this->getIntDateSpecial() != "")
            $objSpecialDate = new class_date($this->getIntDateSpecial());

	    //dates
        $this->updateDateRecord($this->getSystemid(), $objStartDate, $objEndDate, $objSpecialDate);

        //news
        $strQuery = "UPDATE ".$this->arrModule["table"]."
                        SET news_title = ?,
                            news_intro = ?,
                            news_text = ?,
                            news_image = ?,
                            news_hits = ?
                       WHERE news_id = ?";
        $this->objDB->_pQuery($strQuery, array($this->getStrTitle(), $this->getStrIntro(), $this->getStrNewstext(), 
                 $this->getStrImage(), $this->getIntHits(), $this->getSystemid()), array($this->bitTitleChanged, false, false));

        //delete all relations & set them up again
        if(is_array($this->arrCats)) {
            class_modul_news_category::deleteNewsMemberships($this->getSystemid());
            //insert all memberships
            foreach($this->arrCats as $strCatID => $strValue) {
                $strQuery = "INSERT INTO ".$this->arrModule["table2"]."
                            (newsmem_id, newsmem_news, newsmem_category) VALUES
                            (?, ?, ?)";
                if(!$this->objDB->_pQuery($strQuery, array(generateSystemid(), $this->getSystemid(), $strCatID)))
                    return false;
            }
        }

        $this->bitTitleChanged = false;

        return true;
    }

    /**
     * saves the current object as a new object to the database
     *
     * @return bool
     */
    protected function onInsertToDb() {
        //Start wit the system-recods and a tx
        $bitReturn = true;

        $objStartDate = null;
        $objEndDate = null;
        $objSpecialDate = null;

        if($this->getIntDateStart() != 0 && $this->getIntDateStart() != "")
            $objStartDate = new class_date($this->getIntDateStart());

        if($this->getIntDateEnd() != 0 && $this->getIntDateEnd() != "")
            $objEndDate = new class_date($this->getIntDateEnd());

        if($this->getIntDateSpecial() != 0 && $this->getIntDateSpecial() != "")
            $objSpecialDate = new class_date($this->getIntDateSpecial());

	    //dates
        $this->createDateRecord($this->getSystemid(), $objStartDate, $objEndDate, $objSpecialDate);


        //and all memberships
        if(is_array($this->arrCats)) {
            foreach($this->arrCats as $strCatID => $strValue) {
                $strQuery = "INSERT INTO ".$this->arrModule["table2"]."
                            (newsmem_id, newsmem_news, newsmem_category) VALUES
                            (?, ?, ?)";
                if(!$this->objDB->_pQuery($strQuery, array(generateSystemid(), $this->getSystemid(), $strCatID)))
                    $bitReturn = false;
            }
        }

        return $bitReturn;
    }

    /**
	 * Loads all news from the database
	 * if passed, the filter is used to load the news of the given category
	 * If a start and end value is given, just a section of the list is being loaded
	 *
	 * @param string $strFilter
	 * @param int $intStart
	 * @param int $intEnd
	 * @return mixed
	 * @static
	 */
	public static function getNewsList($strFilter = "", $intStart = false, $intEnd = false) {
        $strQuery = "";
        $arrParams = array();
		if($strFilter != "") {
			$strQuery = "SELECT system_id
							FROM "._dbprefix_."news,
							      "._dbprefix_."system,
							      "._dbprefix_."system_date,
							      "._dbprefix_."news_member
							WHERE system_id = news_id
							  AND news_id = newsmem_news
							  AND news_id = system_date_id
							  AND newsmem_category = ?
							ORDER BY system_date_start DESC";
            $arrParams[] = $strFilter;
		}
		else {
			$strQuery = "SELECT system_id
							FROM "._dbprefix_."news,
							      "._dbprefix_."system,
							      "._dbprefix_."system_date
							WHERE system_id = news_id
							  AND system_id = system_date_id
							ORDER BY system_date_start DESC";
		}

		if($intEnd === false && $intStart === false)
		    $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
		else
		    $arrIds = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, $arrParams, $intStart, $intEnd);

		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_news_news($arrOneId["system_id"]);

		return $arrReturn;
	}


	/**
	 * Calculates the number of news available for the
	 * given cat or in total
	 *
	 * @param string $strFilter
	 * @return int
	 */
	public function getNewsCount($strFilter = "") {
        $strQuery = "";
        $arrParams = array();
        if($strFilter != "") {
			$strQuery = "SELECT COUNT(*)
							FROM "._dbprefix_."news_member
							WHERE newsmem_category = ?";
            $arrParams[] = $strFilter;
		}
		else {
			$strQuery = "SELECT COUNT(*)
							FROM "._dbprefix_."news";
		}

		$arrRow = $this->objDB->getPRow($strQuery, $arrParams);
		return $arrRow["COUNT(*)"];
	}

	/**
	 * Deletes the given news and all relating memberships
	 *
	 * @return bool
	 */
	public function deleteNews() {
	    class_logger::getInstance()->addLogRow("deleted news ".$this->getSystemid(), class_logger::$levelInfo);
	    //Delete memberships
	    if(class_modul_news_category::deleteNewsMemberships($this->getSystemid())) {
			$strQuery = "DELETE FROM "._dbprefix_."news WHERE news_id = ? ";
			if($this->objDB->_pQuery($strQuery, array($this->getSystemid()))) {
			    if($this->deleteSystemRecord($this->getSystemid()))
			        return true;
			}
	    }

	    return false;
	}


    /**
     * Counts the number of news displayed for the passed portal-setup
     *
     * @param int $intMode 0 = regular, 1 = archive
	 * @param string $strCat
	 * @return int
	 * @static
     */
    public static function getNewsCountPortal($intMode, $strCat = 0) {
        return count(self::loadListNewsPortal($intMode, $strCat));
    }

	/**
	 * Loads all news from the db assigned to the passed cat
	 *
	 * @param int $intMode 0 = regular, 1 = archive
	 * @param string $strCat
	 * @param int $intOrder 0 = descending, 1 = ascending
     * @param int $intStart
     * @param int $intEnd
	 * @return mixed
	 * @static
	 */
	public static function loadListNewsPortal($intMode, $strCat = 0, $intOrder = 0, $intStart = false, $intEnd = false) {
		$arrReturn = array();
        $strOrder = "";
        $strTime = "";
        $strQuery = "";
        $arrParams = array();
		$longNow = class_date::getCurrentTimestamp();
		//Get Timeintervall
		if($intMode == "0") {
			//Regular news
			$strTime  = "AND (system_date_special IS NULL OR (system_date_special > ? OR system_date_special = 0))";
		}
		elseif($intMode == "1") {
			//Archivnews
			$strTime = "AND (system_date_special < ? AND system_date_special IS NOT NULL AND system_date_special != 0)";
		}
		else
			$strTime = "";
            
		
		
		//check if news should be ordered de- or ascending
		if ($intOrder == 0) {
			$strOrder  = "DESC";
		} else {
			$strOrder  = "ASC";
		}

        if($strCat != "0") {
            $strQuery = "SELECT system_id
                            FROM "._dbprefix_."news,
                                 "._dbprefix_."news_member,
                                 "._dbprefix_."system,
                                 "._dbprefix_."system_date
                            WHERE system_id = news_id
                              AND system_id = system_date_id
                              AND news_id = newsmem_news
                              AND newsmem_category = ?
                              AND system_status = 1
                              AND (system_date_start IS NULL or(system_date_start < ? OR system_date_start = 0))
                                ".$strTime."
                              AND (system_date_end IS NULL or (system_date_end > ? OR system_date_end = 0))
                            ORDER BY system_date_start ".$strOrder;
            $arrParams[] = $strCat;
            $arrParams[] = $longNow;
            if($strTime != "")
                $arrParams[] = $longNow;
            $arrParams[] = $longNow;
            
        }
        else {
             $strQuery = "SELECT system_id
                            FROM "._dbprefix_."news,
                                 "._dbprefix_."system,
                                 "._dbprefix_."system_date
                            WHERE system_id = news_id
                              AND system_id = system_date_id
                              AND system_status = 1
                              AND (system_date_start IS NULL or(system_date_start < ? OR system_date_start = 0))
                                ".$strTime."
                              AND (system_date_end IS NULL or (system_date_end > ? OR system_date_end = 0))
                            ORDER BY system_date_start ".$strOrder;
             
            $arrParams[] = $longNow;
            if($strTime != "")
                $arrParams[] = $longNow;
            $arrParams[] = $longNow;
        }

        if($intStart !== false && $intEnd !== false)
            $arrIds = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, $arrParams, $intStart, $intEnd);
        else
            $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
            
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_news_news($arrOneId["system_id"]);

		return $arrReturn;
	}

	/**
	 * Increments the hits counter of the current object
	 *
	 * @return unknown
	 */
	public function increaseHits() {
	    $strQuery = "UPDATE ".$this->arrModule["table"]." SET news_hits = ? WHERE news_id= ? ";
		return $this->objDB->_pQuery($strQuery, array($this->getIntHits()+1, $this->getSystemid()));
	}

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrTitle() {
        return $this->strTitle;
    }
    public function getStrIntro() {
        return $this->strIntro;
    }
    public function getStrNewstext() {
        return $this->strText;
    }
    public function getStrImage() {
        return $this->strImage;
    }
    public function getIntHits() {
        return $this->intHits;
    }
    public function getIntDateStart() {
        return $this->longDateStart;
    }
    public function getIntDateEnd() {
        return $this->longDateEnd;
    }
    public function getIntDateSpecial() {
        return $this->longDateSpecial;
    }
    public function getArrCats() {
        return $this->arrCats;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
        $this->bitTitleChanged = true;
    }
    public function setStrIntro($strIntro) {
        $this->strIntro = $strIntro;
    }
    public function setStrNewstext($strText) {
        $this->strText = $strText;
    }
    public function setStrImage($strImage) {
        $this->strImage = $strImage;
    }
    public function setIntHits($intHits) {
        $this->intHits = $intHits;
    }
    public function setIntDateStart($intDateStart) {
        $this->longDateStart = $intDateStart;
    }
    public function setIntDateEnd($intDateEnd) {
        $this->longDateEnd = $intDateEnd;
    }
    public function setIntDateSpecial($intDateSpecial) {
        $this->longDateSpecial = $intDateSpecial;
    }
    public function setArrCats($arrCats) {
        $this->arrCats = $arrCats;
    }
}
?>