<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Class representing the admin-part of the gallery element
 *
 * @package module_mediamanager
 * @targetTable element_gallery.content_id
 */
class class_element_gallery_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_gallery.gallery_id
     * @tableColumnDatatype char20
     * @fieldType dropdown
     * @fieldLabel gallery_id
     */
    private $strRepo;


    /**
     * @var string
     * @tableColumn element_gallery.gallery_template
     * @tableColumnDatatype char254
     * @fieldType template
     * @fieldLabel template
     * @fieldTemplateDir /module_mediamanager
     */
    private $strTemplate;

    /**
     * @var int
     * @tableColumn element_gallery.gallery_imagesperpage
     * @tableColumnDatatype int
     * @fieldType text
     * @fieldLabel gallery_imagesperpage
     */
    private $intImagesPerPage;


    /**
     * @var int
     * @tableColumn element_gallery.gallery_maxh_d
     * @tableColumnDatatype int
     * @fieldType text
     * @fieldLabel gallery_maxh_d
     */
    private $intMaxHD;

    /**
     * @var int
     * @tableColumn element_gallery.gallery_maxw_d
     * @tableColumnDatatype int
     * @fieldType text
     * @fieldLabel gallery_maxw_d
     */
    private $intMaxWD;

    /**
     * @var int
     * @tableColumn element_gallery.gallery_text
     * @tableColumnDatatype char254
     * @fieldType text
     * @fieldLabel gallery_text
     */
    private $strText;

    /**
     * @var int
     * @tableColumn element_gallery.gallery_text_x
     * @tableColumnDatatype int
     * @fieldType text
     * @fieldLabel gallery_text_x
     */
    private $intTextX;

    /**
     * @var int
     * @tableColumn element_gallery.gallery_text_y
     * @tableColumnDatatype int
     * @fieldType text
     * @fieldLabel gallery_text_y
     */
    private $intTextY;

    /**
     * @var int
     * @tableColumn element_gallery.gallery_mode
     * @tableColumnDatatype int
     * @fieldType hidden
     */
    private $intGalleryMode = 0;


    /**
     * @var int
     * @tableColumn element_gallery.gallery_overlay
     * @tableColumnDatatype char254
     * @fieldType image
     * @fieldLabel gallery_overlay
     */
    private $strGalleryOverlay;



    public function getAdminForm() {
        $objForm = parent::getAdminForm();

        $arrRawGals = class_module_mediamanager_repo::getObjectList();
        $arrGalleries = array();
        foreach($arrRawGals as $objOneGal) {
            $arrGalleries[$objOneGal->getSystemid()] = $objOneGal->getStrDisplayName();
        }
        $objForm->getField("repo")->setArrKeyValues($arrGalleries);

        $objForm->addField(new class_formentry_headline("h1"))->setStrValue($this->getLang("headline_list"));
        $objForm->setFieldToPosition("h1", 3);

        $objForm->addField(new class_formentry_headline("h2"))->setStrValue($this->getLang("headline_detail"));
        $objForm->addField(new class_formentry_textrow("t2"))->setStrValue($this->getLang("hint_detail"));
        $objForm->setFieldToPosition("h2", 5);
        $objForm->setFieldToPosition("t2", 6);

        $objForm->addField(new class_formentry_headline("h3"))->setStrValue($this->getLang("headline_overlay"));
        $objForm->addField(new class_formentry_textrow("t3"))->setStrValue($this->getLang("hint_text"));
        $objForm->setFieldToPosition("h3", 9);
        $objForm->setFieldToPosition("t3", 10);

        return $objForm;
    }




    /**
     * @param int $intImagesPerPage
     */
    public function setIntImagesPerPage($intImagesPerPage) {
        $this->intImagesPerPage = $intImagesPerPage;
    }

    /**
     * @return int
     */
    public function getIntImagesPerPage() {
        return $this->intImagesPerPage;
    }

    /**
     * @param int $intMaxHD
     */
    public function setIntMaxHD($intMaxHD) {
        $this->intMaxHD = $intMaxHD;
    }

    /**
     * @return int
     */
    public function getIntMaxHD() {
        return $this->intMaxHD;
    }

    /**
     * @param int $intMaxWD
     */
    public function setIntMaxWD($intMaxWD) {
        $this->intMaxWD = $intMaxWD;
    }

    /**
     * @return int
     */
    public function getIntMaxWD() {
        return $this->intMaxWD;
    }

    /**
     * @param int $intMode
     */
    public function setIntGalleryMode($intMode) {
        $this->intGalleryMode = $intMode;
    }

    /**
     * @return int
     */
    public function getIntGalleryMode() {
        return $this->intGalleryMode;
    }

    /**
     * @param int $intTextX
     */
    public function setIntTextX($intTextX) {
        $this->intTextX = $intTextX;
    }

    /**
     * @return int
     */
    public function getIntTextX() {
        return $this->intTextX;
    }

    /**
     * @param int $intTextY
     */
    public function setIntTextY($intTextY) {
        $this->intTextY = $intTextY;
    }

    /**
     * @return int
     */
    public function getIntTextY() {
        return $this->intTextY;
    }

    /**
     * @param int $strGalleryOverlay
     */
    public function setStrGalleryOverlay($strGalleryOverlay) {
        $this->strGalleryOverlay = $strGalleryOverlay;
    }

    /**
     * @return int
     */
    public function getStrGalleryOverlay() {
        return $this->strGalleryOverlay;
    }

    /**
     * @param string $strRepo
     */
    public function setStrRepo($strRepo) {
        $this->strRepo = $strRepo;
    }

    /**
     * @return string
     */
    public function getStrRepo() {
        return $this->strRepo;
    }

    /**
     * @param string $strTemplate
     */
    public function setStrTemplate($strTemplate) {
        $this->strTemplate = $strTemplate;
    }

    /**
     * @return string
     */
    public function getStrTemplate() {
        return $this->strTemplate;
    }

    /**
     * @param int $strText
     */
    public function setStrText($strText) {
        $this->strText = $strText;
    }

    /**
     * @return int
     */
    public function getStrText() {
        return $this->strText;
    }






}
