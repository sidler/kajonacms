<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_cookie.php 3538 2011-01-07 09:30:19Z sidler $                                            *
********************************************************************************************************/

/**
 * Data-Container for a single calendar-entry.
 * May be produces by classes in order to be written into the calendar.
 *
 * @package modul_dashboard
 * @author sidler@mulchprod.de
 * @since 3.4
 */
class class_calendarentry {

    private $strName;
    private $strClass = "calendarEvent";

    /**
     *
     * @var class_date
     */
    private $objDate;

    public function getStrName() {
        return $this->strName;
    }

    public function setStrName($strName) {
        $this->strName = $strName;
    }

    public function getObjDate() {
        return $this->objDate;
    }

    public function setObjDate($objDate) {
        $this->objDate = $objDate;
    }

    public function getStrClass() {
        return $this->strClass;
    }

    public function setStrClass($strClass) {
        $this->strClass = $strClass;
    }


}

?>