<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * Loads the last-modified date of the current page and prepares it for output
 *
 * @package modul_pages
 */
class class_element_rssfeed extends class_element_portal implements interface_portal_element {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
        $arrModule = array();
		$arrModule["name"] 			= "element_rssfeed";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_universal";
		$arrModule["modul"]		    = "elemente";

		parent::__construct($arrModule, $objElementData);
	}

    /**
     * Loads the feed and displays it
     *
     * @return string the prepared html-output
     */
	public function loadData() {
       $strReturn = "";
       $strFeed = "";
	   try {
            $objRemoteloader = new class_remoteloader();
            $objRemoteloader->setStrHost(str_ireplace("http://", "", $this->arrElementData["char2"]));
            $objRemoteloader->setIntPort(0);
            $strFeed = $objRemoteloader->getRemoteContent();
        }
        catch (class_exception $objExeption) {
            $strFeed = "";
        }

		$strFeedTemplateID = $this->objTemplate->readTemplate("/element_rssfeed/".$this->arrElementData["char1"], "rssfeed_feed");
        $strPostTemplateID = $this->objTemplate->readTemplate("/element_rssfeed/".$this->arrElementData["char1"], "rssfeed_post");

		$strContent = "";
		$arrTemplate = array();
		if(uniStrlen($strFeed) == 0) {
		  $strContent = $this->getText("rssfeed_errorloading");
		}
		else {
		    $objXmlparser = new class_xml_parser();
		    $objXmlparser->loadString($strFeed);

		    $arrFeed = $objXmlparser->xmlToArray();

		    if(count($arrFeed) >= 1) {

		        //rss feed
		        if(isset($arrFeed["rss"])) {

        		    $arrTemplate["feed_title"] = $arrFeed["rss"][0]["channel"][0]["title"][0]["value"];
        		    $arrTemplate["feed_link"] = $arrFeed["rss"][0]["channel"][0]["link"][0]["value"];
        		    $arrTemplate["feed_description"] = $arrFeed["rss"][0]["channel"][0]["description"][0]["value"];
        		    $intCounter = 0;

                    if(isset($arrFeed["rss"][0]["channel"][0]["item"]) && is_array($arrFeed["rss"][0]["channel"][0]["item"])) {
                        foreach ($arrFeed["rss"][0]["channel"][0]["item"] as $arrOneItem) {

                          $strDate = (isset($arrOneItem["pubDate"][0]["value"]) ? $arrOneItem["pubDate"][0]["value"] : "");
                            if($strDate != "") {
                                $intDate = strtotime($strDate);
                                if($intDate > 0) {
                                    $strDate = timeToString($intDate);
                                }
                            }

                            $arrMessage = array();
                            $arrMessage["post_date"] = $strDate;
                            $arrMessage["post_title"] = (isset($arrOneItem["title"][0]["value"]) ? $arrOneItem["title"][0]["value"] : "");
                            $arrMessage["post_description"] = (isset($arrOneItem["description"][0]["value"]) ? $arrOneItem["description"][0]["value"] : "");
                            $arrMessage["post_link"] = (isset($arrOneItem["link"][0]["value"]) ? $arrOneItem["link"][0]["value"] : "");

                            $strContent .= $this->fillTemplate($arrMessage, $strPostTemplateID);

                            if(++$intCounter >= $this->arrElementData["int1"])
                               break;

                        }
                    }
                    else
                        $strContent = $this->getText("rssfeed_noentry");

		        }

		        //atom feed
		        if(isset($arrFeed["feed"]) && isset($arrFeed["feed"][0]["entry"])) {

		            $arrTemplate["feed_title"] = $arrFeed["feed"][0]["title"][0]["value"];
                    $arrTemplate["feed_link"] = $arrFeed["feed"][0]["link"][0]["attributes"]["href"];
                    $arrTemplate["feed_description"] = $arrFeed["feed"][0]["subtitle"][0]["value"];
                    $intCounter = 0;

                    if(isset($arrFeed["feed"][0]["entry"]) && is_array($arrFeed["feed"][0]["entry"])) {
                        foreach ($arrFeed["feed"][0]["entry"] as $arrOneItem) {

                            $strDate = (isset($arrOneItem["updated"][0]["value"]) ? $arrOneItem["updated"][0]["value"] : "");
                            if($strDate != "") {
                                $intDate = strtotime($strDate);
                                if($intDate > 0) {
                                    $strDate = timeToString($intDate);
                                }
                            }

                            $arrMessage = array();
                            $arrMessage["post_date"] = $strDate;
                            $arrMessage["post_title"] = (isset($arrOneItem["title"][0]["value"]) ? $arrOneItem["title"][0]["value"] : "");
                            $arrMessage["post_description"] = (isset($arrOneItem["summary"][0]["value"]) ? $arrOneItem["summary"][0]["value"] : "");
                            $arrMessage["post_link"] = (isset($arrOneItem["link"][0]["attributes"]["href"]) ? $arrOneItem["link"][0]["attributes"]["href"] : "");

                            $strContent .= $this->fillTemplate($arrMessage, $strPostTemplateID);

                            if(++$intCounter >= $this->arrElementData["int1"])
                               break;

                        }
                    }
                    else
                        $strContent = $this->getText("rssfeed_noentry");
		        }
		    }
		    else {
		        $strContent = $this->getText("rssfeed_errorparsing");
		    }

		}

		$arrTemplate["feed_content"] = $strContent;
		$strReturn .= $this->fillTemplate($arrTemplate, $strFeedTemplateID);

		return $strReturn;
	}

}
?>