<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                 *
********************************************************************************************************/


/**
 * Admin-Part of the toolkit-classes
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_toolkit_admin extends class_toolkit {

    /**
     * Constructor
     *
     */
    public function __construct() {
        //Calling the base class
        parent::__construct();
    }

    /**
     * Returns a simple date-form element. By default used to enter a date without a time.
     *
     * @param string $strName
     * @param string $strTitle
     * @param class_date $objDateToShow
     * @param string $strClass = inputDate
     * @param boolean $bitWithTime
     *
     * @throws class_exception
     * @return string
     * @since 3.2.0.9
     */
    public function formDateSingle($strName, $strTitle, $objDateToShow, $strClass = "", $bitWithTime = false) {
        //check passed param
        if($objDateToShow != null && !$objDateToShow instanceof class_date)
            throw new class_exception("param passed to class_toolkit_admin::formDateSingle is not an instance of class_date", class_exception::$level_ERROR);

        if($bitWithTime)
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_datetime_simple");
        else
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_date_simple");
        $arrTemplate = array();
        $arrTemplate["class"] = $strClass;
        $arrTemplate["titleDay"] = $strName."_day";
        $arrTemplate["titleMonth"] = $strName."_month";
        $arrTemplate["titleYear"] = $strName."_year";
        $arrTemplate["titleHour"] = $strName."_hour";
        $arrTemplate["titleMin"] = $strName."_minute";
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["valueDay"] = $objDateToShow != null ? $objDateToShow->getIntDay() : "";
        $arrTemplate["valueMonth"] = $objDateToShow != null ? $objDateToShow->getIntMonth() : "";
        $arrTemplate["valueYear"] = $objDateToShow != null ? $objDateToShow->getIntYear() : "";
        $arrTemplate["valueHour"] = $objDateToShow != null ? $objDateToShow->getIntHour() : "";
        $arrTemplate["valueMin"] = $objDateToShow != null ? $objDateToShow->getIntMin() : "";
        $arrTemplate["valuePlain"] = dateToString($objDateToShow, false);
        if($bitWithTime)
            $arrTemplate["dateFormat"] = class_carrier::getInstance()->getObjLang()->getLang("dateStyleShort", "system");
        else
            $arrTemplate["dateFormat"] = class_carrier::getInstance()->getObjLang()->getLang("dateStyleLong", "system");
        $arrTemplate["calendarLang"] = class_carrier::getInstance()->getObjSession()->getAdminLanguage();

        $arrTemplate["titleTime"] = class_carrier::getInstance()->getObjLang()->getLang("titleTime", "system");

        //set up the container div
        $arrTemplate["calendarId"] = $strName;
        $strContainerId = $strName."_calendarContainer";
        $arrTemplate["calendarContainerId"] = $strContainerId;
        $arrTemplate["calendarLang_weekday"] = " [" . class_carrier::getInstance()->getObjLang()->getLang("toolsetCalendarWeekday", "system") . "]\n";
        $arrTemplate["calendarLang_month"] = " [" . class_carrier::getInstance()->getObjLang()->getLang("toolsetCalendarMonth", "system") . "]\n";

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Returns a text-field using the cool WYSIWYG editor
     * You can use the different toolbar sets defined in /admin/scripts/ckeditor/config.js
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strContent
     * @param string $strToolbarset
     * @return string
     */
    public function formWysiwygEditor($strName = "inhalt", $strTitle = "", $strContent = "", $strToolbarset = "standard") {
        $strReturn = "";

        //create the html-input element
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "wysiwyg_ckeditor");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["content"] = htmlentities($strContent, ENT_COMPAT, "UTF-8");
        $strReturn .=  $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
        //for the popups, we need the skinwebpath
        $strReturn .= $this->formInputHidden("skinwebpath", _skinwebpath_);

        //set the language the user defined for the admin
        $strLanguage = class_session::getInstance()->getAdminLanguage();
        if($strLanguage == "")
            $strLanguage = "en";

        //include the settings made by admin skin
        $strTemplateInitID = $this->objTemplate->readTemplate("/elements.tpl", "wysiwyg_ckeditor_inits");
        $strTemplateInit = $this->objTemplate->fillTemplate(array(), $strTemplateInitID);

        //check if a customized editor-config is available
        $strConfigFile = "'config_kajona_standard.js'";
        if(is_file(_realpath_."/project/admin/scripts/ckeditor/config_kajona_standard.js"))
            $strConfigFile = "KAJONA_WEBPATH+'/project/admin/scripts/ckeditor/config_kajona_standard.js'";

        //to add role-based editors, you could load a different toolbar or also a different CKEditor config file
        //the editor code
        $strReturn .= " <script type=\"text/javascript\" src=\""._webpath_."/core/module_system/admin/scripts/ckeditor/ckeditor.js\"></script>\n";
        $strReturn .= " <script type=\"text/javascript\">\n";
        $strReturn .= "
            var ckeditorConfig = {
                customConfig : ".$strConfigFile.",
                toolbar : '".$strToolbarset."',
                ".$strTemplateInit."
                language : '".$strLanguage."',
                filebrowserBrowseUrl : '".uniStrReplace("&amp;", "&", getLinkAdminHref("folderview", "browserChooser", "&form_element=ckeditor"))."',
                filebrowserImageBrowseUrl : '".uniStrReplace("&amp;", "&", getLinkAdminHref("mediamanager", "folderContentFolderviewMode", "systemid="._mediamanager_default_imagesrepoid_."&form_element=ckeditor&bit_link=1"))."'
	        };
            CKEDITOR.replace('".$strName."', ckeditorConfig);
        ";
        $strReturn .= "</script>\n";

        return $strReturn;
    }


    /**
     * Returns a divider to split up a page in logical sections
     *
     * @param string $strClass
     * @return string
     */
    public function divider($strClass = "divider") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "divider");
        $arrTemplate = array();
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Creates a percent-beam to illustrate proportions
     *
     * @param float $floatPercent
     * @param int|string $intLength
     * @return string
     */
    public function percentBeam($floatPercent, $intLength = "300")  {
        //Calc width
        $intWidth = $intLength - 50;
        $intBeamLength = ceil($intWidth * $floatPercent / 100);
        if($intBeamLength == 0)
            $intBeamLength = 1;

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "percent_beam");
        $arrTemplate = array();
        $arrTemplate["length"] = $intLength;
        $arrTemplate["percent"] = number_format($floatPercent, 2);
        $arrTemplate["width"] = $intWidth;
        if($arrTemplate["percent"] == "100.00")
            $arrTemplate["beamwidth"] = $intBeamLength;
        else
            $arrTemplate["beamwidth"] = $intBeamLength-1;
        if(($intWidth - $intBeamLength) <= 0 || $arrTemplate["percent"] == "100.00")
            $arrTemplate["transTillEnd"] = "";
         else
            $arrTemplate["transTillEnd"] = "<img src=\"_skinwebpath_/trans.gif\" width=\"".($intWidth - $intBeamLength-1)."\" height=\"1\" />";
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /*"*****************************************************************************************************/
    // --- FORM-Elements ------------------------------------------------------------------------------------

    /**
     * Returns a checkbox
     *
     * @param string $strName
     * @param string $strTitle
     * @param bool $bitChecked
     * @param string $strClass
     * @return string
     */
    public function formInputCheckbox($strName, $strTitle, $bitChecked = false, $strClass = "") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_checkbox");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["checked"] = ($bitChecked ? "checked=\"checked\"" : "");
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a regular hidden-input-field
     *
     * @param string $strName
     * @param string $strValue
     * @return string
     */
    public function formInputHidden($strName, $strValue = "") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_hidden");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a regular text-input field
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @param string $strClass
     * @param string $strOpener
     * @param bool $bitReadonly
     * @return string
     */
    public function formInputText($strName, $strTitle = "", $strValue = "", $strClass = "", $strOpener = "", $bitReadonly = false) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_text");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["opener"] = $strOpener;
        $arrTemplate["readonly"] = ($bitReadonly ? "readonly=\"readonly\"" : "");

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

    /**
     * Returns a regular text-input field
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @param string $strClass
     * @param bool $bitElements
     * @return string
     */
    public function formInputPageSelector($strName, $strTitle = "", $strValue = "", $strClass = "", $bitElements = true) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_pageselector");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["opener"] = getLinkAdminDialog(
            "pages",
            "pagesFolderBrowser",
            "&pages=1&form_element=".$strName.(!$bitElements ? "&elements=false" : ""),
            class_carrier::getInstance()->getObjLang()->getLang("select_page", "pages"),
            class_carrier::getInstance()->getObjLang()->getLang("select_page", "pages"),
            "icon_externalBrowser",
            class_carrier::getInstance()->getObjLang()->getLang("select_page", "pages")
        );

        $strJsVarName = uniStrReplace(array("[", "]"), array("", ""), $strName);

        $arrTemplate["ajaxScript"] = "
	        <script type=\"text/javascript\">
                    $(function() {
                        KAJONA.admin.".$strJsVarName." = $('#".uniStrReplace(array("[", "]"), array("\\\[", "\\\]"), $strName)."').autocomplete({
                            source: function(request, response) {
                                $.ajax({
                                    url: '".getLinkAdminXml("pages", "getPagesByFilter")."',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        filter: request.term
                                    },
                                    success: response
                                });
                            },
                            minLength: 1,
                            delay: KAJONA.util.isTouchDevice() ? 2000 : 0,
                            messages: {
                                noResults: '',
                                results: function() {}
                            }
                        });
                    });
	        </script>
        ";

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }


    /**
     * Returns a regular text-input field.
     * The param $strValue expects a system-id.
     *
     * The element creates two fields:
     * a text-field, and a hidden field for the selected systemid.
     * The hidden field is names as $strName, appended by "_id".
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @param string $strClass
     * @param bool $bitUser
     * @param bool $bitGroups
     * @param bool $bitBlockCurrentUser
     *
     * @return string
     */
    public function formInputUserSelector($strName, $strTitle = "", $strValue = "", $strClass = "", $bitUser = true, $bitGroups = false, $bitBlockCurrentUser = false) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_userselector");

        $strUserName = "";
        $strUserId = "";

        //value is a systemid
        if(validateSystemid($strValue)) {
            $objUser = new class_module_user_user($strValue);
            $strUserName = $objUser->getStrDisplayName();
            $strUserId = $strValue;
        }


        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = htmlspecialchars($strUserName, ENT_QUOTES, "UTF-8", false);
        $arrTemplate["value_id"] = htmlspecialchars($strUserId, ENT_QUOTES, "UTF-8", false);
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["opener"] = getLinkAdminDialog(
            "user",
            "userBrowser",
            "&form_element=".$strName.($bitGroups ? "&allowGroup=1" : "").($bitBlockCurrentUser ? "&filter=current" : ""),
            class_carrier::getInstance()->getObjLang()->getLang("user_browser", "user"),
            class_carrier::getInstance()->getObjLang()->getLang("user_browser", "user"),
            "icon_externalBrowser",
            class_carrier::getInstance()->getObjLang()->getLang("user_browser", "user")
        );

        $strJsVarName = uniStrReplace(array("[", "]"), array("", ""), $strName);

        $strName = uniStrReplace(array("[", "]"), array("\\\[", "\\\]"), $strName);
        $arrTemplate["ajaxScript"] = "
	        <script type=\"text/javascript\">
                    $(function() {

                        $('#".$strName."').autocomplete({
                            source: function(request, response) {
                                $.ajax({
                                    url: '".getLinkAdminXml("user", "getUserByFilter")."',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        filter: request.term,
                                        user: ".($bitUser ? "'true'" : "'false'").",
                                        group: ".($bitGroups ? "'true'" : "'false'").",
                                        block: ".($bitBlockCurrentUser ? "'current'" : "''")."
                                    },
                                    success: response
                                });
                            },

                            select: function( event, ui ) {
                                if(ui.item) {
                                    $( '#".$strName."' ).val( ui.item.title );
                                    if($( '#".$strName."_id' ))
                                        $( '#".$strName."_id' ).val( ui.item.systemid );
                                }
                                return false;
                            },
                            minLength: 1,
                            delay: KAJONA.util.isTouchDevice() ? 2000 : 0,
                            messages: {
                                noResults: '',
                                results: function() {return ''}
                            }
                        })
                        .data( 'ui-autocomplete' )._renderItem = function( ul, item ) {
                            return $( '<li></li>' )
                                .data('ui-autocomplete-item', item)
                                .append( '<a class=\'ui-autocomplete-item userSelectorAC\' style=\'background-image: url('+item.icon+'); background-repeat: no-repeat;\' >'+item.title+'</a>' )
                                .appendTo( ul );
                        } ;
                    });
	        </script>
        ";

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

    /**
     * Returns a regular text-input field with a file browser button.
     * Use $strRepositoryId to set a specific filemanager repository id
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @param string $strRepositoryId
     * @param string $strClass
     * @return string
     * @since 3.3.4
     */
    public function formInputFileSelector($strName, $strTitle = "", $strValue = "", $strRepositoryId = "", $strClass = "") {
        $strOpener = getLinkAdminDialog(
            "mediamanager",
            "folderContentFolderviewMode",
            "&form_element=".$strName."&systemid=".$strRepositoryId,
            class_carrier::getInstance()->getObjLang()->getLang("filebrowser", "system"),
            class_carrier::getInstance()->getObjLang()->getLang("filebrowser", "system"),
            "icon_externalBrowser",
            class_carrier::getInstance()->getObjLang()->getLang("filebrowser", "system")
        );

        return $this->formInputText($strName, $strTitle, $strValue, $strClass, $strOpener);
    }


    /**
     * Returns a regular text-input field with a file browser button.
     * The repository is set to the images-repo by default.
     * In addition, a button to edit the image is added by default.
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @param string $strClass
     * @return string
     * @since 3.4.0
     */
    public function formInputImageSelector($strName, $strTitle = "", $strValue = "", $strClass = "") {
        $strOpener = getLinkAdminDialog(
            "mediamanager",
            "folderContentFolderviewMode",
            "&form_element=".$strName."&systemid="._mediamanager_default_imagesrepoid_,
            class_carrier::getInstance()->getObjLang()->getLang("filebrowser", "system"),
            class_carrier::getInstance()->getObjLang()->getLang("filebrowser", "system"),
            "icon_externalBrowser",
            class_carrier::getInstance()->getObjLang()->getLang("filebrowser", "system")
        );

        $strOpener .= " ".getLinkAdminDialog(
            "mediamanager",
            "imageDetails",
            "file='+document.getElementById('".$strName."').value+'",
            class_carrier::getInstance()->getObjLang()->getLang("action_edit_image", "mediamanager"),
            class_carrier::getInstance()->getObjLang()->getLang("action_edit_image", "mediamanager"),
            "icon_crop",
            class_carrier::getInstance()->getObjLang()->getLang("action_edit_image", "mediamanager"),
            true,
            false,
            " (function() {
             if(document.getElementById('".$strName."').value != '') {
                 KAJONA.admin.folderview.dialog.setContentIFrame('".getLinkAdminHref("mediamanager", "imageDetails", "file='+document.getElementById('".$strName."').value+'")."');
                 KAJONA.admin.folderview.dialog.setTitle('".$strTitle."');
                 KAJONA.admin.folderview.dialog.init();
             }
             return false; })(); return false;"
        );

        return $this->formInputText($strName, $strTitle, $strValue, $strClass, $strOpener);
    }

    /**
     * Returns a text-input field as textarea
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @param string $strClass = inputTextarea
     * @param bool $bitReadonly
     * @return string
     */
    public function formInputTextArea($strName, $strTitle = "", $strValue = "", $strClass = "", $bitReadonly = false) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_textarea");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["readonly"] = ($bitReadonly ? " readonly=\"readonly\" " : "");
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a password text-input field
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strValue
     * @param string $strClass
     *
     * @return string
     */
    public function formInputPassword($strName, $strTitle = "", $strValue = "", $strClass = "") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_password");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = htmlspecialchars($strValue, ENT_QUOTES, "UTF-8", false);
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a button to submit a form
     *
     * @param string $strValue
     * @param string $strName
     * @param string $strEventhandler
     * @param string $strClass
     * @param bool $bitEnabled
     * @return string
     */
    public function formInputSubmit($strValue = null, $strName = "Submit", $strEventhandler = "", $strClass = "", $bitEnabled = true) {
        if($strValue === null)
            $strValue = class_carrier::getInstance()->getObjLang()->getLang("commons_save", "system");

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_submit");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["value"] = $strValue;
        $arrTemplate["eventhandler"] = $strEventhandler;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["disabled"] = $bitEnabled ? "" : "disabled=\"disabled\"";
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a input-file element
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strClass
     * @return string
     */
    public function formInputUpload($strName, $strTitle = "", $strClass = "") {

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_upload");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;

        $objText = class_carrier::getInstance()->getObjLang();
        $arrTemplate["maxSize"] = $objText->getLang("max_size", "mediamanager")." ".bytesToString(class_config::getInstance()->getPhpMaxUploadSize());

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a input-file element for uploading multiple files with progress bar
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strAllowedFileTypes
     * @return string
     */
    public function formInputUploadMultiple($strName, $strTitle, $strAllowedFileTypes) {


        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_upload_multiple");
        $arrTemplate = array();
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["name"] = $strName;
        $arrTemplate["modalDialog"] = $this->jsDialog(0);

        $strAllowedFileTypes = uniStrReplace(array(".", ","), array("", "','"), $strAllowedFileTypes);

        $arrTemplate["allowedExtensions"] = $strAllowedFileTypes != "" ? "'".$strAllowedFileTypes."'" : $strAllowedFileTypes;


        $objConfig = class_carrier::getInstance()->getObjConfig();
        $objText = class_carrier::getInstance()->getObjLang();

        $arrTemplate["upload_multiple_errorFilesize"] = $objText->getLang("upload_multiple_errorFilesize", "mediamanager")." ".bytesToString($objConfig->getPhpMaxUploadSize());

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returning a complete Dropdown
     *
     * @param string $strName
     * @param mixed $arrKeyValues
     * @param string $strTitle
     * @param string $strKeySelected
     * @param string $strClass
     * @param bool $bitEnabled
     * @param string $strAddons
     * @return string
     */
    public function formInputDropdown($strName, array $arrKeyValues, $strTitle = "", $strKeySelected = "", $strClass = "", $bitEnabled = true, $strAddons = "") {
        $strOptions = "";
        $strTemplateOptionID = $this->objTemplate->readTemplate("/elements.tpl", "input_dropdown_row");
        $strTemplateOptionSelectedID = $this->objTemplate->readTemplate("/elements.tpl", "input_dropdown_row_selected");
        //Iterating over the array to create the options
        foreach ($arrKeyValues as $strKey => $strValue) {
            $arrTemplate = array();
            $arrTemplate["key"] = $strKey;
            $arrTemplate["value"] = $strValue;
            if((string)$strKey == (string)$strKeySelected)
                $strOptions .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateOptionSelectedID);
            else
                $strOptions .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateOptionID);
        }

        $arrTemplate = array();
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_dropdown");
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["disabled"] = ($bitEnabled ? "" : "disabled=\"disabled\"");
        $arrTemplate["options"] = $strOptions;
        $arrTemplate["addons"] = $strAddons;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }


    /**
     * Returning a complete dropdown but in multiselect-style
     *
     * @param string $strName
     * @param mixed $arrKeyValues
     * @param string $strTitle
     * @param array $arrKeysSelected
     * @param string $strClass
     * @param bool $bitEnabled
     * @param string $strAddons
     * @return string
     */
    public function formInputMultiselect($strName, array $arrKeyValues, $strTitle = "", $arrKeysSelected = array(), $strClass = "", $bitEnabled = true, $strAddons = "") {
        $strOptions = "";
        $strTemplateOptionID = $this->objTemplate->readTemplate("/elements.tpl", "input_multiselect_row");
        $strTemplateOptionSelectedID = $this->objTemplate->readTemplate("/elements.tpl", "input_multiselect_row_selected");
        //Iterating over the array to create the options
        foreach ($arrKeyValues as $strKey => $strValue) {
            $arrTemplate = array();
            $arrTemplate["key"] = $strKey;
            $arrTemplate["value"] = $strValue;
            if(in_array($strKey, $arrKeysSelected))
                $strOptions .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateOptionSelectedID);
            else
                $strOptions .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateOptionID);
        }

        $arrTemplate = array();
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_multiselect");
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;
        $arrTemplate["disabled"] = ($bitEnabled ? "" : "disabled=\"disabled\"");
        $arrTemplate["options"] = $strOptions;
        $arrTemplate["addons"] = $strAddons;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }


    /**
     * Creates a list of radio-buttons.
     * In difference to a dropdown a radio-button may not force the user to
     * make a selection / does not generate an implicit selection
     *
     * @param string $strName
     * @param mixed $arrKeyValues
     * @param string $strTitle
     * @param string $strKeySelected
     * @param string $strClass
     * @param bool $bitEnabled
     *
     * @return string
     */
    public function formInputRadiogroup($strName, array $arrKeyValues, $strTitle = "", $strKeySelected = "", $strClass = "", $bitEnabled = true) {
        $strOptions = "";
        $strTemplateRadioID = $this->objTemplate->readTemplate("/elements.tpl", "input_radiogroup_row");
        $strTemplateRadioSelectedID = $this->objTemplate->readTemplate("/elements.tpl", "input_radiogroup_row_selected");
        //Iterating over the array to create the options
        foreach ($arrKeyValues as $strKey => $strValue) {
            $arrTemplate = array();
            $arrTemplate["key"] = $strKey;
            $arrTemplate["value"] = $strValue;
            $arrTemplate["name"] = $strName;
            $arrTemplate["class"] = $strClass;
            $arrTemplate["disabled"] = ($bitEnabled ? "" : "disabled=\"disabled\"");
            if((string)$strKey == (string)$strKeySelected)
                $strOptions .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateRadioSelectedID);
            else
                $strOptions .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateRadioID);
        }

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_radiogroup");
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["radios"] = $strOptions;
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }


    /**
     * Creates the header needed to open a form-element
     *
     * @param string $strAction
     * @param string $strName
     * @param string $strEncoding
     * @param string $strOnSubmit
     * @return string
     */
    public function formHeader($strAction, $strName = "", $strEncoding = "", $strOnSubmit = "") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "form_start");
        $arrTemplate = array();
        $arrTemplate["name"] = ($strName != "" ? $strName : "form".generateSystemid());
        $arrTemplate["action"] = $strAction;
        $arrTemplate["enctype"] = $strEncoding;
        $arrTemplate["onsubmit"] = $strOnSubmit;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Creates a foldable wrapper around optional form fields
     *
     * @param string $strContent
     * @param string $strTitle
     * @param bool $bitVisible
     * @return string
     */
    public function formOptionalElementsWrapper($strContent, $strTitle = "", $bitVisible = false) {
        $strId = generateSystemid();
        $strCallbackVisible = "function() { $('#".$strId."').addClass('optionalElementsWrapperVisible'); }";
        $strCallbackInvisible = "function() { $('#".$strId."').removeClass('optionalElementsWrapperVisible'); }";
        $arrFolder = $this->getLayoutFolder($strContent, "<img src=\""._skinwebpath_."/pics/icon_folderClosed.png\" alt=\"\" /> ".$strTitle, $bitVisible, $strCallbackVisible, $strCallbackInvisible);
        return "<br /><div id=\"".$strId."\" class=\"optionalElementsWrapper".($bitVisible ? " optionalElementsWrapperVisible" : "")."\">".$this->getFieldset($arrFolder[1], $arrFolder[0])."</div>";
    }

    /**
     * Returns a single TextRow in a form
     *
     * @param string $strText
     * @param string $strClass
     *
     * @return string
     */
    public function formTextRow($strText, $strClass = "") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "text_row_form");
        $arrTemplate = array();
        $arrTemplate["text"] = $strText;
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

    /**
     * Returns a headline in a form
     *
     * @param string $strText
     * @param string $strClass
     * @return string
     */
    public function formHeadline($strText, $strClass = "") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "headline_form");
        $arrTemplate = array();
        $arrTemplate["text"] = $strText;
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }

    /**
     * Returns the tags to close an open form.
     * Includes the hidden fields for a passed pe param and a passed pv param by default.
     *
     * @param bool $bitIncludePeFields
     * @return string
     */
    public function formClose($bitIncludePeFields = true) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "form_close");
        $strPeFields = "";
        if($bitIncludePeFields) {
            $arrParams = class_carrier::getAllParams();
            if(array_key_exists("pe", $arrParams))
                $strPeFields .= $this->formInputHidden("pe", $arrParams["pe"]);
            if(array_key_exists("pv", $arrParams))
                $strPeFields .= $this->formInputHidden("pv", $arrParams["pv"]);
        }
        return $strPeFields.$this->objTemplate->fillTemplate(array(), $strTemplateID);
    }


    /*"*****************************************************************************************************/
    // --- GRID-Elements ------------------------------------------------------------------------------------

    /**
     * Creates the code to start a sortable grid.
     * By default, a grid is sortable.
     * @return string
     */
    public function gridHeader() {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "grid_header");
        return $this->objTemplate->fillTemplate(array(), $strTemplateID);
    }

    /**
     * Renders a single entry of the current grid.
     *
     * @param interface_admin_gridable|class_model|interface_model $objEntry
     * @param $strActions
     *
     * @return string
     */
    public function gridEntry(interface_admin_gridable $objEntry, $strActions) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "grid_entry");
        $arrTemplate = array(
            "title" => $objEntry->getStrDisplayName(),
            "image" => $objEntry->getStrGridIcon(),
            "actions" => $strActions,
            "systemid" => $objEntry->getSystemid(),
            "subtitle" => $objEntry->getStrLongDescription(),
            "info" => $objEntry->getStrAdditionalInfo()
        );

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Renders the closing elements of a grid.
     * @return string
     */
    public function gridFooter() {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "grid_footer");
        return $this->objTemplate->fillTemplate(array(), $strTemplateID);
    }

    /*"*****************************************************************************************************/
    // --- LIST-Elements ------------------------------------------------------------------------------------

    /**
     * Returns the htmlcode needed to start a proper list
     *
     * @return string
     */
    public function listHeader() {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "list_header");
        return $this->objTemplate->fillTemplate(array(), $strTemplateID);
    }

    /**
     * Returns the htmlcode needed to start a proper list, supporting drag n drop to
     * reorder list-items
     *
     * @param string $strListId
     * @param bool $bitOnlySameTable dropping only allowed within the same table or also in other tables
     * @param bool $bitAllowDropOnTree
     *
     * @return string
     */
    public function dragableListHeader($strListId, $bitOnlySameTable = false, $bitAllowDropOnTree = false) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "dragable_list_header");
        return $this->objTemplate->fillTemplate(
            array(
                "listid" => $strListId,
                "sameTable" => $bitOnlySameTable? "true" : "false",
                "jsInject" => "bitMoveToTree = ".($bitAllowDropOnTree ? "true" : "false").";"
            ),
            $strTemplateID
        );
    }



    /**
     * Returns the code to finish the opened list
     *
     * @return string
     */
    public function listFooter() {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "list_footer");
        return $this->objTemplate->fillTemplate(array(), $strTemplateID);
    }

    /**
     * Returns the code to finish the opened list
     *
     * @param string $strListId
     * @return string
     */
    public function dragableListFooter($strListId) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "dragable_list_footer");
        return $this->objTemplate->fillTemplate(array("listid" => $strListId), $strTemplateID);
    }

    /**
     * Renders a simple admin-object, implementing interface_model
     *
     * @param interface_admin_listable|interface_model|class_model $objEntry
     * @param $strActions
     * @param $intCount
     * @param bool $bitCheckbox
     *
     * @return string
     */
    public function simpleAdminList(interface_admin_listable $objEntry, $strActions, $intCount, $bitCheckbox = false) {
        $strImage = $objEntry->getStrIcon();
        if(is_array($strImage))
            $strImage = getImageAdmin($strImage[0], $strImage[1]);
        else
            $strImage = getImageAdmin($strImage);
        return $this->genericAdminList(
            $objEntry->getSystemid(),
            $objEntry->getStrDisplayName(),
            $strImage,
            $strActions,
            $intCount,
            $objEntry->getStrAdditionalInfo(),
            $objEntry->getStrLongDescription(),
            $bitCheckbox
        );
    }

    /**
     * Renders a single admin-row, takes care of selecting the matching template-sections.
     *
     * @param $strId
     * @param $strName
     * @param $strIcon
     * @param $strActions
     * @param $intCount
     * @param string $strAdditionalInfo
     * @param string $strDescription
     * @param bool $bitCheckbox
     *
     * @return string
     */
    public function genericAdminList($strId, $strName, $strIcon, $strActions, $intCount, $strAdditionalInfo = "", $strDescription = "", $bitCheckbox = false) {
        $arrTemplate = array();
        $arrTemplate["listitemid"] = $strId;
        $arrTemplate["image"] = $strIcon;
        $arrTemplate["title"] = $strName;
        $arrTemplate["center"] = $strAdditionalInfo;
        $arrTemplate["actions"] = $strActions;
        $arrTemplate["description"] = $strDescription;

        if($bitCheckbox) {
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "generallist_checkbox");
            $arrTemplate["checkbox"] = $this->objTemplate->fillTemplate(array("systemid" => $strId), $strTemplateID);
        }

        if($strDescription != "")
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "generallist_desc_".(($intCount % 2)+1));
        else
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "generallist_".(($intCount % 2)+1));

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     *
     * @param \class_admin_batchaction[] $arrActions
     *
     * @return string
     */
    public function renderBatchActionHandlers(array $arrActions) {
        $strEntries = "";
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "batchactions_entry");

        foreach($arrActions as $objOneAction) {
            $strEntries .= $this->objTemplate->fillTemplate(
                array(
                    "title" => $objOneAction->getStrTitle(),
                    "icon" => $objOneAction->getStrIcon(),
                    "targeturl" => $objOneAction->getStrTargetUrl()
                ),
                $strTemplateID
            );
        }

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "batchactions_wrapper");
        $strReturn = $this->objTemplate->fillTemplate(array("entries" => $strEntries), $strTemplateID);
        $strReturn .= $this->jsDialog(1);
        return $strReturn;
    }

    /**
     * Returns a table filled with infos
     *
     * @param mixed $arrHeader the first row to name the columns
     * @param mixed $arrValues every entry is one row
     * @return string
     */
    public function dataTable(array $arrHeader, array $arrValues) {
        $strReturn = "";
        $intCounter = "";
        //The Table header & the templates
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_header");
        $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateID);

        $strTemplateHeaderHeaderID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_head_header");
        $strTemplateHeaderContentID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_head");
        $strTemplateHeaderFooterID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_head_footer");
        $strTemplateContentHeaderID1 = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_header_1");
        $strTemplateContentHeaderID2 = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_header_2");
        $strTemplateContentContentID1 = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_1");
        $strTemplateContentContentID2 = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_1");
        $strTemplateContentFooterID1 = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_footer_2");
        $strTemplateContentFooterID2 = $this->objTemplate->readTemplate("/elements.tpl", "datalist_column_footer_2");
        //Iterating over the rows

        //Starting with the header, column by column
        if(is_array($arrHeader)) {
            $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateHeaderHeaderID);

            foreach ($arrHeader as $strHeader)
                $strReturn .= $this->objTemplate->fillTemplate(array("value" => $strHeader), $strTemplateHeaderContentID);

            $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateHeaderFooterID);
        }

        //And the content, row by row, column by column
        foreach ($arrValues as $arrValueRow) {
            if(++$intCounter % 2 == 0)
                $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateContentHeaderID1);
            else
                $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateContentHeaderID2);
            foreach($arrValueRow as $strValue) {
                if($intCounter % 2 == 0)
                    $strReturn .= $this->objTemplate->fillTemplate(array("value" => $strValue), $strTemplateContentContentID1);
                else
                    $strReturn .= $this->objTemplate->fillTemplate(array("value" => $strValue), $strTemplateContentContentID2);
            }
            if($intCounter % 2 == 0)
                $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateContentFooterID1);
            else
                $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateContentFooterID2);
        }

        //And the footer
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "datalist_footer");
        $strReturn .= $this->objTemplate->fillTemplate(array(), $strTemplateID);
        return $strReturn;
    }


    /*"*****************************************************************************************************/
    // --- Action-Elements ----------------------------------------------------------------------------------

    /**
     * Creates a action-Entry in a list
     *
     * @param string $strContent
     * @return string
     */
    public function listButton($strContent) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "list_button");
        $arrTemplate = array();
        $arrTemplate["content"] = $strContent;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Generates a delete-button. The passed element name and question is shown as a modal dialog
     * when the icon was clicked. So set the link-href-param for the final deletion, otherwise the
     * user has no more chance to delete the record!
     *
     * @param $strElementName
     * @param $strQuestion
     * @param $strLinkHref
     * @return string
     */
    public function listDeleteButton($strElementName, $strQuestion, $strLinkHref) {
        //place it into a standard-js-dialog
        $strDialog = $this->jsDialog(1);

        $strElementName = uniStrReplace(array('\''), array('\\\''), $strElementName);

        $strQuestion = uniStrReplace("%%element_name%%", htmlToString($strElementName, true), $strQuestion);

        //create the list-button and the js code to show the dialog
        $strButton = getLinkAdminManual("href=\"#\" onclick=\"javascript:jsDialog_1.setTitle('".class_carrier::getInstance()->getObjLang()->getLang("dialog_deleteHeader", "system")."'); jsDialog_1.setContent('".$strQuestion."', '".class_carrier::getInstance()->getObjLang()->getLang("dialog_deleteButton", "system")."',  '".$strLinkHref."'); jsDialog_1.init(); return false;\"",
                                         "",
                                         class_carrier::getInstance()->getObjLang()->getLang("commons_delete", "system"),
                                         "icon_delete" );

        return $this->listButton($strButton).$strDialog;
    }

    /**
     * Generates a button allowing to change the status of the record passed.
     * Therefore an ajax-method is called.
     *
     * @param class_model|string $objInstance or a systemid
     * @param bool $bitReload triggers a page-reload afterwards
     *
     * @throws class_exception
     * @return string
     */
    public function listStatusButton($objInstance, $bitReload = false) {

        if(is_object($objInstance) && $objInstance instanceof class_model )
            $objRecord = $objInstance;

        else if(validateSystemid($objInstance) && class_objectfactory::getInstance()->getObject($objInstance) !== null)
            $objRecord = class_objectfactory::getInstance()->getObject($objInstance);

        else
            throw new class_exception("failed loading instance for ".(is_object($objInstance) ? " @ ".get_class($objInstance) : $objInstance), class_exception::$level_ERROR);

        if($objRecord->getIntRecordStatus() == 1) {
            $strImage = "icon_enabled";
            $strText = class_carrier::getInstance()->getObjLang()->getLang("status_active", "system");
        }
        else {
            $strImage = "icon_disabled";
            $strText = class_carrier::getInstance()->getObjLang()->getLang("status_inactive", "system");
        }

        $strJavascript = "";

        //output texts and image paths only once
        if(class_carrier::getInstance()->getObjSession()->getSession("statusButton", class_session::$intScopeRequest) === false) {
            $strJavascript .= "<script type=\"text/javascript\">
                var strActiveText = '".class_carrier::getInstance()->getObjLang()->getLang("status_active", "system")."';
                var strInActiveText = '".class_carrier::getInstance()->getObjLang()->getLang("status_inactive", "system")."';
                var strActiveImageSrc = '"._skinwebpath_."/pics/icon_enabled.png';
                var strInActiveImageSrc = '"._skinwebpath_."/pics/icon_disabled.png';

            </script>";
            class_carrier::getInstance()->getObjSession()->setSession("statusButton", "true", class_session::$intScopeRequest);
        }

        $strButton = getLinkAdminManual(
            "href=\"javascript:KAJONA.admin.ajax.setSystemStatus('".$objRecord->getSystemid()."', ".($bitReload ? "true" : "false").");\"",
            "",
            $strText,
            $strImage,
            "statusImage_".$objRecord->getSystemid(),
            "statusLink_".$objRecord->getSystemid()
        );

        return $this->listButton($strButton).$strJavascript;
    }

    /*"*****************************************************************************************************/
    // --- Misc-Elements ------------------------------------------------------------------------------------

    /**
     * Returns a warning box, e.g. shown before deleting a record
     *
     * @param string $strContent
     * @param string $strClass
     * @return string
     */
    public function warningBox($strContent, $strClass = "warnbox") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "warning_box");
        $arrTemplate = array();
        $arrTemplate["content"] = $strContent;
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a single TextRow
     *
     * @param string $strText
     * @param string $strClass
     *
     * @return string
     */
    public function getTextRow($strText, $strClass = "text") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "text_row");
        $arrTemplate = array();
        $arrTemplate["text"] = $strText;
        $arrTemplate["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Creates the mechanism to fold parts of the site / make them visible or invisible
     *
     * @param string $strContent
     * @param string $strLinkText The text / content,
     * @param bool $bitVisible
     * @param string $strCallbackVisible JS function
     * @param string $strCallbackInvisible JS function
     * @return mixed 0: The html-layout code
     *               1: The link to fold / unfold
     */
    public function getLayoutFolder($strContent, $strLinkText, $bitVisible = false, $strCallbackVisible = "", $strCallbackInvisible = "") {
        $arrReturn = array();
        $strID = str_replace(array(" ", "."), array("", ""), microtime());
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "layout_folder");
        $arrTemplate = array();
        $arrTemplate["id"] = $strID;
        $arrTemplate["content"] = $strContent;
        $arrTemplate["display"] = ($bitVisible ? "block" : "none");
        $arrReturn[0] = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
        $arrReturn[1] = "<a href=\"javascript:KAJONA.util.fold('".$strID."', ". ($strCallbackVisible != "" ? $strCallbackVisible : "null") .", ". ($strCallbackInvisible != "" ? $strCallbackInvisible : "null") .");\">".$strLinkText."</a>";
        return $arrReturn;
    }

    /**
     * Creates the mechanism to fold parts of the site / make them vivsible or invisible
     *
     * @param string $strContent
     * @param string $strLinkText Mouseovertext
     * @param string $strImageVisible clickable
     * @param string $strImageInvisible clickable
     * @param bool $bitVisible
     * @return string
     */
    public function getLayoutFolderPic($strContent, $strLinkText = "", $strImageVisible = "icon_folderOpen.gif", $strImageInvisible = "icon_folderClosed.png", $bitVisible = true) {
        $strID = str_replace(array(" ", "."), array("", ""), microtime());
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "layout_folder_pic");
        $arrTemplate = array();
        $arrTemplate["id"] = $strID;
        $arrTemplate["content"] = $strContent;
        $arrTemplate["display"] = ($bitVisible ? "block" : "none");
        $arrTemplate["link"] = "<a href=\"javascript:KAJONA.util.foldImage('".$strID."', '".$strID."_img', '"._skinwebpath_."/pics/".$strImageVisible."', '"._skinwebpath_."/pics/".$strImageInvisible."')\" title=\"".$strLinkText."\">".getImageAdmin(($bitVisible ? $strImageVisible : $strImageInvisible), $strLinkText, false, $strID."_img")."</a>";
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Returns a infobox about the page being edited
     *
     * @param mixed $arrContent
     * @return string
     */
    public function getPageInfobox($arrContent) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "page_infobox");
        return $this->objTemplate->fillTemplate($arrContent, $strTemplateID);
    }

    /**
     * Creates the page to view & manipulate image.
     *
     * @since 3.2
     * @replace class_toolkit_admin::getFileDetails()
     * @param array $arrContent
     * @return string
     *
     */
    public function getMediamanagerImageDetails(array $arrContent) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "mediamanager_image_details");
        return $this->objTemplate->fillTemplate($arrContent, $strTemplateID);
    }


    /**
     * Creates a fieldset to structure elements
     *
     * @param string $strTitle
     * @param string $strContent
     * @param string $strClass
     * @return string
     */
    public function getFieldset($strTitle, $strContent, $strClass = "fieldset") {
        //remove old placeholder from content
        $this->objTemplate->setTemplate($strContent);
        $this->objTemplate->deletePlaceholder();
        $strContent = $this->objTemplate->getTemplate();
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "misc_fieldset");
        $arrContent = array();
        $arrContent["title"] = $strTitle;
        $arrContent["content"] = $strContent;
        $arrContent["class"] = $strClass;
        return $this->objTemplate->fillTemplate($arrContent, $strTemplateID);
    }

    /**
     * Creates a tab-list out of the passed tabs.
     * The params is expected as
     * arraykey => tabname
     * arrayvalue => tabcontent
     *
     * @param $arrTabs array(key => content)
     * @param bool$bitFullHeight whether the tab content should use full height
     *
     * @return string
     */
    public function getTabbedContent(array $arrTabs, $bitFullHeight = false) {

        $strWrapperID = $this->objTemplate->readTemplate("/elements.tpl", "tabbed_content_wrapper");
        $strHeaderID = $this->objTemplate->readTemplate("/elements.tpl", "tabbed_content_tabheader");
        $strContentID = $this->objTemplate->readTemplate("/elements.tpl", "tabbed_content_tabcontent");

        $strTabs = "";
        $strTabContent = "";
        $strClassaddon = "active in ";
        foreach($arrTabs as $strTitle => $strContent) {
            $strTabId = generateSystemid();
            $strTabs .= $this->objTemplate->fillTemplate(array("tabid" => $strTabId, "tabtitle" => $strTitle, "classaddon" => $strClassaddon), $strHeaderID);
            $strTabContent .= $this->objTemplate->fillTemplate(array("tabid" => $strTabId, "tabcontent" => $strContent, "classaddon" => $strClassaddon), $strContentID);
            $strClassaddon = "";
        }

        return $this->objTemplate->fillTemplate(array("tabheader" => $strTabs, "tabcontent" => $strTabContent, "classaddon" => ($bitFullHeight === true ? 'fullHeight' : '')), $strWrapperID);
    }

    /**
     * Container for graphs, e.g. used by stats.
     *
     * @param string $strImgSrc
     * @return string
     */
    public function getGraphContainer($strImgSrc) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "graph_container");
        $arrContent = array();
        $arrContent["imgsrc"] = $strImgSrc;
        return $this->objTemplate->fillTemplate($arrContent, $strTemplateID);
    }

    /**
     * Includes an IFrame with the given URL
     *
     * @param string $strIFrameSrc
     * @param string $strIframeId
     *
     * @return string
     */
    public function getIFrame($strIFrameSrc, $strIframeId = "") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "iframe_container");
        $arrContent = array();
        $arrContent["iframesrc"] = $strIFrameSrc;
        $arrContent["iframeid"] = $strIframeId !== "" ? $strIframeId : generateSystemid();
        return $this->objTemplate->fillTemplate($arrContent, $strTemplateID);
    }

    /**
     * Renders the login-status and corresponding links
     * @param array $arrElements
     * @return string
     * @since 3.4.0
     */
    public function getLoginStatus(array $arrElements) {
        //Loading a small login-form
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "logout_form");
        $strReturn = $this->objTemplate->fillTemplate($arrElements, $strTemplateID);
        return $strReturn;
    }

    /*"*****************************************************************************************************/
    // --- Navigation-Elements ------------------------------------------------------------------------------

    /**
     * The v4 way of generating a backend-navigation.
     *
     * @param string $strCurrentModule
     *
     * @return string
     */
    public function getAdminSitemap($strCurrentModule = "") {
        $strWrapperID = $this->objTemplate->readTemplate("/elements.tpl", "sitemap_wrapper");
        $strModuleID = $this->objTemplate->readTemplate("/elements.tpl", "sitemap_module_wrapper");
        $strModuleActiveID = $this->objTemplate->readTemplate("/elements.tpl", "sitemap_module_wrapper_active");
        $strActionID = $this->objTemplate->readTemplate("/elements.tpl", "sitemap_action_entry");
        $strDividerID = $this->objTemplate->readTemplate("/elements.tpl", "sitemap_divider_entry");
        $strModules = "";

        if($strCurrentModule == "elemente")
            $strCurrentModule = "pages";

        $arrModules = class_module_system_module::getModulesInNaviAsArray(class_module_system_aspect::getCurrentAspectId());

        /** @var $arrAdminInstances class_admin[] */
        $arrAdminInstances = array();
        foreach ($arrModules as $arrModule) {
            $objModule = class_module_system_module::getModuleBySystemid($arrModule["module_id"]);
            if($objModule->rightView()) {
                $arrAdminInstances[] = $objModule->getAdminInstanceOfConcreteModule();
            }
        }


        foreach ($arrAdminInstances as $objOneInstance) {

            $arrActions = class_admin_helper::getModuleActionNaviHelper($objOneInstance);

            $strActions = "";
            foreach($arrActions as $strOneAction) {
                if(trim($strOneAction) != "") {
                    $arrActionEntries = array(
                        "action" => $strOneAction
                    );
                    $strActions .= $this->objTemplate->fillTemplate($arrActionEntries, $strActionID);
                }
                else {
                    $strActions .= $this->objTemplate->fillTemplate(array(), $strDividerID);
                }
            }


            $arrModuleLevel = array(
                "module" => getLinkAdmin($objOneInstance->getObjModule()->getStrName(), "", "", class_carrier::getInstance()->getObjLang()->getLang("modul_titel", $objOneInstance->getObjModule()->getStrName())),
                "actions" => $strActions,
                "systemid" => $objOneInstance->getObjModule()->getSystemid(),
                "moduleTitle" => $objOneInstance->getObjModule()->getStrName(),
                "moduleName" => class_carrier::getInstance()->getObjLang()->getLang("modul_titel", $objOneInstance->getObjModule()->getStrName()),
                "moduleHref" => getLinkAdminHref($objOneInstance->getObjModule()->getStrName(), "")
            );

            if($strCurrentModule == $objOneInstance->getObjModule()->getStrName())
                $strModules .= $this->objTemplate->fillTemplate($arrModuleLevel, $strModuleActiveID);
            else
                $strModules .= $this->objTemplate->fillTemplate($arrModuleLevel, $strModuleID);

        }

        return $this->objTemplate->fillTemplate(array("level" => $strModules), $strWrapperID);
    }

    /*"*****************************************************************************************************/
    // --- Path Navigation ----------------------------------------------------------------------------------

    /**
     * Generates the layout for a small navigation
     *
     * @param mixed $arrEntries
     * @return string
     */
    public function getPathNavigation(array $arrEntries) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "path_container");
        $strTemplateRowID = $this->objTemplate->readTemplate("/elements.tpl", "path_entry");
        $strRows = "";
        foreach ($arrEntries as $strOneEntry) {
            $strRows .= $this->objTemplate->fillTemplate(array("pathlink" => $strOneEntry), $strTemplateRowID);
        }
        return $this->objTemplate->fillTemplate(array("pathnavi" => $strRows), $strTemplateID);

    }

    /*"*****************************************************************************************************/
    // --- Content Toolbar ----------------------------------------------------------------------------------

    /**
     * A content toolbar can be used to group a subset of actions linking different views
     *
     * @param mixed $arrEntries
     * @param int $intActiveEntry Array-counting, so first element is 0, last is array-length - 1
     * @return string
     */
    public function getContentToolbar(array $arrEntries, $intActiveEntry = -1) {
        $strTemplateWrapperID = $this->objTemplate->readTemplate("/elements.tpl", "contentToolbar_wrapper");
        $strTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contentToolbar_entry");
        $strTemplateActiveEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contentToolbar_entry_active");
        $strRows = "";
        foreach ($arrEntries as $intI => $strOneEntry) {
            if($intI == $intActiveEntry)
                $strRows .= $this->objTemplate->fillTemplate(array("entry" => $strOneEntry), $strTemplateActiveEntryID);
            else
                $strRows .= $this->objTemplate->fillTemplate(array("entry" => $strOneEntry), $strTemplateEntryID);
        }
        return $this->objTemplate->fillTemplate(array("entries" => $strRows), $strTemplateWrapperID);

    }

    /*"*****************************************************************************************************/
    // --- Validation Errors --------------------------------------------------------------------------------

    /**
     * Generates a list of errors found by the form-validation
     *
     * @param class_admin|class_admin_formgenerator $objCalling
     * @param string $strTargetAction
     * @return string
     */
    public function getValidationErrors($objCalling, $strTargetAction = null) {
        $strRendercode = "";
        //render mandatory fields?
        if(method_exists($objCalling, "getRequiredFields") && is_callable(array($objCalling, "getRequiredFields")) ) {
            if($objCalling instanceof class_admin_formgenerator) {
                $arrFields = $objCalling->getRequiredFields();
            }
            else {
                $strTempAction = $objCalling->getAction();
                $objCalling->setAction($strTargetAction);
                $arrFields = $objCalling->getRequiredFields();
                $objCalling->setAction($strTempAction);
            }

            if(count($arrFields) > 0 ) {

                $strRendercode .= "<script type=\"text/javascript\">$(document).ready(function () {
                        KAJONA.admin.forms.renderMandatoryFields([";

                foreach($arrFields as $strName => $strType) {
                    $strRendercode .= "[ '".$strName."', '".$strType."' ], ";
                }
                $strRendercode .= " [] ]); });</script>";
            }
        }

        $arrErrors = $objCalling->getValidationErrors();
        if(count($arrErrors) == 0)
            return $strRendercode;

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "error_container");
        $strTemplateRowID = $this->objTemplate->readTemplate("/elements.tpl", "error_row");
        $strRows = "";
        $strRendercode .= "<script type=\"text/javascript\">$(document).ready(function () {
            KAJONA.admin.forms.renderMissingMandatoryFields([";

        foreach ($arrErrors as $strKey => $strOneError) {
            $strRows .= $this->objTemplate->fillTemplate(array("field_errortext" => $strOneError), $strTemplateRowID);
            $strRendercode .= "[ '".$strKey."' ], ";
        }
        $strRendercode .= " [] ]); });</script>";
        $arrTemplate = array();
        $arrTemplate["errorrows"] = $strRows;
        $arrTemplate["errorintro"] = class_lang::getInstance()->getLang("errorintro", "system");
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID).$strRendercode;
    }


    /*"*****************************************************************************************************/
    // --- Pre-formatted ------------------------------------------------------------------------------------


    /**
     * Returns a simple <pre>-Element to display pre-formatted text such as logfiles
     *
     * @param array $arrLines
     * @param int $nrRows number of rows to display
     * @return string
     */
    public function getPreformatted($arrLines, $nrRows = 0) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "preformatted");
        $strRows = "";
        $intI = 0;
        foreach ($arrLines as $strOneLine) {
            if($nrRows != 0 && $intI++ > $nrRows)
                break;
            $strOneLine = str_replace(array("<pre>", "</pre>", "\n"), array(" ", " ", "\r\n"), $strOneLine);
            $strRows .= htmlToString($strOneLine, true);
        }
        return $this->objTemplate->fillTemplate(array("pretext" => $strRows), $strTemplateID);
    }

    /*"*****************************************************************************************************/
    // --- Language handling --------------------------------------------------------------------------------

    /**
     * Creates the sourrounding code of a language switch, places the buttons
     *
     * @param string $strLanguageButtons
     * @param $strOnChangeHandler
     *
     * @return string
     */
    public function getLanguageSwitch($strLanguageButtons, $strOnChangeHandler) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "language_switch");
        $arrTemplate = array();
        $arrTemplate["languagebuttons"] = $strLanguageButtons;
        $arrTemplate["onchangehandler"] = $strOnChangeHandler;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Creates the code for one button for a specified language, part of a language switch
     *
     * @param string $strKey
     * @param string $strLanguageName  The full name of the language
     * @param bool $bitActive
     * @return string
     */
    public function getLanguageButton($strKey, $strLanguageName, $bitActive = false) {
        //active language?
        if($bitActive)
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "language_switch_button_active");
        else
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "language_switch_button");
        $arrTemplate = array();
        $arrTemplate["languageKey"] = $strKey;
        $arrTemplate["languageName"] = $strLanguageName;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /*"*****************************************************************************************************/
    // --- Pageview mechanism ------------------------------------------------------------------------------

    /**
     * Creates a pageview
     *
     * @param array $arrData
     * @param int $intCurrentpage
     * @param string $strModule
     * @param string $strAction
     * @param string $strLinkAdd
     * @param int $intElementPerPage
     * @return mixed a one-dimensional array: ["elements"] and ["pageview"]
     *
     * @deprecated migrate to getSimplePageview instead!
     */
    public function getPageview(array $arrData, $intCurrentpage, $strModule, $strAction, $strLinkAdd = "", $intElementPerPage = 15) {
        $arrReturn = array();

        if($intCurrentpage <= 0)
            $intCurrentpage = 1;

        if($intElementPerPage <= 0)
            $intElementPerPage = 1;

        $objArrayIterator = new class_array_iterator($arrData);
        $objArrayIterator->setIntElementsPerPage($intElementPerPage);
        $intNrOfPages = $objArrayIterator->getNrOfPages();
        $intNrOfElements = $objArrayIterator->getNumberOfElements();

        $arrReturn["elements"] = $objArrayIterator->getElementsOnPage($intCurrentpage);
        //read templates
        $strTemplateBodyID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_body");
        $strTemplateForwardID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_link_forward");
        $strTemplateBackwardID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_link_backward");
        $strTemplateListID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_page_list");
        $strTemplateListItemActiveID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_list_item_active");
        $strTemplateListItemID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_list_item");
        //build layout
        $arrTemplate = array();

        $strListItems = "";

        //just load the current +-4 pages and the first/last +-2
        $intCounter2 = 1;
        for($intI = 1; $intI <= $intNrOfPages; $intI++) {
            $bitDisplay = false;
            if($intCounter2 <= 2) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($intNrOfPages-1)) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($intCurrentpage-2) && $intCounter2 <= ($intCurrentpage+2)) {
                $bitDisplay = true;
            }


            if($bitDisplay) {
                $arrLinkTemplate = array();
                $arrLinkTemplate["href"] = getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".$intI);
                $arrLinkTemplate["pageNr"] = $intI;

                if($intI == $intCurrentpage)
                    $strListItems .= $this->objTemplate->fillTemplate($arrLinkTemplate, $strTemplateListItemActiveID);
                else
                    $strListItems .= $this->objTemplate->fillTemplate($arrLinkTemplate, $strTemplateListItemID);
            }
            $intCounter2++;
        }
        $arrTemplate["pageList"] = $this->objTemplate->fillTemplate(array("pageListItems" => $strListItems), $strTemplateListID);
        $arrTemplate["nrOfElementsText"] = class_carrier::getInstance()->getObjLang()->getLang("pageview_total", "system");
        $arrTemplate["nrOfElements"] = $intNrOfElements;
        if($intCurrentpage < $intNrOfPages)
            $arrTemplate["linkForward"] = $this->objTemplate->fillTemplate(array("linkText" => class_carrier::getInstance()->getObjLang()->getLang("pageview_forward", "system"),
                                                                                 "href" => getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".($intCurrentpage+1))), $strTemplateForwardID);
        if($intCurrentpage > 1)
            $arrTemplate["linkBackward"] = $this->objTemplate->fillTemplate(array("linkText" => class_carrier::getInstance()->getObjLang()->getLang("commons_back", "commons"),
                                                                                  "href" => getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".($intCurrentpage-1))), $strTemplateBackwardID);


        $arrReturn["pageview"] = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateBodyID);
        return $arrReturn;
    }

    /**
     * Creates a pageview
     *
     * @param class_array_section_iterator $objArraySectionIterator
     * @param string $strModule
     * @param string $strAction
     * @param string $strLinkAdd
     * @return mixed a two-dimensional array: ["elements"] and ["pageview"]
     * @since 3.3.0
     */
    public function getSimplePageview($objArraySectionIterator, $strModule, $strAction, $strLinkAdd = "") {
        $arrReturn = array();

        $intCurrentpage = $objArraySectionIterator->getPageNumber();
        $intNrOfPages = $objArraySectionIterator->getNrOfPages();
        $intNrOfElements = $objArraySectionIterator->getNumberOfElements();


        $arrReturn["elements"] = $objArraySectionIterator->getArrayExtended(true);

        //read templates
        $strTemplateBodyID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_body");
        $strTemplateForwardID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_link_forward");
        $strTemplateBackwardID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_link_backward");
        $strTemplateListID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_page_list");
        $strTemplateListItemActiveID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_list_item_active");
        $strTemplateListItemID = $this->objTemplate->readTemplate("/elements.tpl", "pageview_list_item");
        //build layout
        $arrTemplate = array();

        $strListItems = "";

        //just load the current +-4 pages and the first/last +-2
        $intCounter2 = 1;
        for($intI = 1; $intI <= $intNrOfPages; $intI++) {
            $bitDisplay = false;
            if($intCounter2 <= 2) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($intNrOfPages-1)) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($intCurrentpage-2) && $intCounter2 <= ($intCurrentpage+2)) {
                $bitDisplay = true;
            }


            if($bitDisplay) {
                $arrLinkTemplate = array();
                $arrLinkTemplate["href"] = getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".$intI);
                $arrLinkTemplate["pageNr"] = $intI;

                if($intI == $intCurrentpage)
                    $strListItems .= $this->objTemplate->fillTemplate($arrLinkTemplate, $strTemplateListItemActiveID);
                else
                    $strListItems .= $this->objTemplate->fillTemplate($arrLinkTemplate, $strTemplateListItemID);
            }
            $intCounter2++;
        }
        $arrTemplate["pageList"] = $this->objTemplate->fillTemplate(array("pageListItems" => $strListItems), $strTemplateListID);
        $arrTemplate["nrOfElementsText"] = class_carrier::getInstance()->getObjLang()->getLang("pageview_total", "system");
        $arrTemplate["nrOfElements"] = $intNrOfElements;
        if($intCurrentpage < $intNrOfPages)
            $arrTemplate["linkForward"] = $this->objTemplate->fillTemplate(
                array(
                    "linkText" => class_carrier::getInstance()->getObjLang()->getLang("pageview_forward", "system"),
                    "href" => getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".($intCurrentpage+1))
                ),
                $strTemplateForwardID
            );
        if($intCurrentpage > 1)
            $arrTemplate["linkBackward"] = $this->objTemplate->fillTemplate(
                array(
                    "linkText" => class_carrier::getInstance()->getObjLang()->getLang("commons_back", "commons"),
                    "href" => getLinkAdminHref($strModule, $strAction, $strLinkAdd."&pv=".($intCurrentpage-1))
                ),
                $strTemplateBackwardID
            );


        $arrReturn["pageview"] = $this->objTemplate->fillTemplate($arrTemplate, $strTemplateBodyID);
        return $arrReturn;
    }


    /*"*****************************************************************************************************/
    // --- Adminwidget / Dashboard --------------------------------------------------------------------------


    public function getMainDashboard(array $arrColumns) {
        return $this->objTemplate->fillTemplate(
            array("entries" => implode("", $arrColumns)),
            $this->objTemplate->readTemplate("/elements.tpl", "dashboard_wrapper")
        );
    }

    /**
     * Generates the header for a column on the dashboard.
     * Inits the ajax-componentes for this list
     *
     * @param string $strColumnId
     * @return string
     */
    public function getDashboardColumnHeader($strColumnId) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "dashboard_column_header");
        return $this->objTemplate->fillTemplate(array("column_id" => $strColumnId), $strTemplateID);
    }

    /**
     * The footer of a dashboard column.
     *
     * @return string
     */
    public function getDashboardColumnFooter() {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "dashboard_column_footer");
        return $this->objTemplate->fillTemplate(array(), $strTemplateID);
    }

    /**
     * The widget-enclose is the code-fragment to be built around the widget itself.
     * Used to handle the widget on the current column.
     *
     * @param string $strDashboardEntryId
     * @param string $strWidgetContent
     * @return string
     */
    public function getDashboardWidgetEncloser($strDashboardEntryId, $strWidgetContent) {
         $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "dashboard_encloser");
        $arrTemplate = array();
        $arrTemplate["entryid"] = $strDashboardEntryId;
        $arrTemplate["content"] = $strWidgetContent;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Builds the widget out of its main components.
     *
     * @param string $strSystemid
     * @param string $strName
     * @param string $strContent
     * @param string $strEditLink
     * @param string $strDeleteLink
     * @param string $strLayoutSection
     * @return string
     */
    public function getAdminwidget($strSystemid, $strName, $strContent, $strEditLink = "", $strDeleteLink = "", $strLayoutSection = "adminwidget_widget") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", $strLayoutSection);
        $arrTemplate = array();
        $arrTemplate["widget_name"] = $strName;
        $arrTemplate["widget_content"] = $strContent;
        $arrTemplate["widget_id"] = $strSystemid;
        $arrTemplate["widget_edit"] = $strEditLink;
        $arrTemplate["widget_delete"] = $strDeleteLink;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Generates a text-row in a widget
     *
     * @param string $strText
     * @return string
     */
    public function adminwidgetText($strText) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "adminwidget_text");
        return $this->objTemplate->fillTemplate(array("text" => $strText), $strTemplateID);
    }

    /**
     * Generate a separator / divider in a widget
     *
     * @return string
     */
    public function adminwidgetSeparator() {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "adminwidget_separator");
        return $this->objTemplate->fillTemplate(array(""), $strTemplateID);
    }

    //--- modal dialog --------------------------------------------------------------------------------------

    /**
     * Creates a modal dialog on the page. By default, the dialog is hidden, so has to be set visible.
     * The type-param decides what template is used for the dialog-layout. The name of the dialog is built via jsDialog_$intTypeNr.
     * Set the contents via js-calls.
     *
     * @param int $intDialogType (0 = regular modal dialog, 1 = confirmation dialog, 2 = rawDialog, 3 = loadingDialog)
     * @return string
     */
    public function jsDialog($intDialogType) {
        $strContent = "";
        //create the html-part
        $arrTemplate = array();
        $strContainerId = generateSystemid();
        $arrTemplate["dialog_id"] = $strContainerId;

        $strTemplateId = null;
        if($intDialogType == 0 && class_carrier::getInstance()->getObjSession()->getSession("jsDialog_".$intDialogType, class_session::$intScopeRequest) === false) {
            $strTemplateId = $this->objTemplate->readTemplate("/elements.tpl", "dialogContainer");
            class_carrier::getInstance()->getObjSession()->setSession("jsDialog_".$intDialogType, "true",  class_session::$intScopeRequest);
        }
        else if($intDialogType == 1 && class_carrier::getInstance()->getObjSession()->getSession("jsDialog_".$intDialogType, class_session::$intScopeRequest) === false) {
            $arrTemplate["dialog_cancelButton"] = class_carrier::getInstance()->getObjLang()->getLang("dialog_cancelButton", "system");

            $strTemplateId = $this->objTemplate->readTemplate("/elements.tpl", "dialogConfirmationContainer");
            class_carrier::getInstance()->getObjSession()->setSession("jsDialog_".$intDialogType, "true",  class_session::$intScopeRequest);
        }
        else if($intDialogType == 2 && class_carrier::getInstance()->getObjSession()->getSession("jsDialog_".$intDialogType, class_session::$intScopeRequest) === false) {
            $strTemplateId = $this->objTemplate->readTemplate("/elements.tpl", "dialogRawContainer");
            class_carrier::getInstance()->getObjSession()->setSession("jsDialog_".$intDialogType, "true",  class_session::$intScopeRequest);
        }
        else if($intDialogType == 3 && class_carrier::getInstance()->getObjSession()->getSession("jsDialog_".$intDialogType, class_session::$intScopeRequest) === false) {
            $arrTemplate["dialog_title"] = class_carrier::getInstance()->getObjLang()->getLang("dialog_loadingHeader", "system");
            $strTemplateId = $this->objTemplate->readTemplate("/elements.tpl", "dialogLoadingContainer");
            class_carrier::getInstance()->getObjSession()->setSession("jsDialog_".$intDialogType, "true",  class_session::$intScopeRequest);
        }

        if($strTemplateId != null) {
            $strContent .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateId);

            //and create the java-script
            $strContent .="<script type=\"text/javascript\">
                var jsDialog_".$intDialogType." = null;
                KAJONA.admin.loader.loadFile('_skinwebpath_/js/kajona_dialog.js', function() {
                    jsDialog_".$intDialogType." = new KAJONA.admin.ModalDialog('".$strContainerId."', ".$intDialogType.");
                }, true);
            </script>";
        }

        return $strContent;
    }


    //--- misc ----------------------------------------------------------------------------------------------

    /**
     * Sets the users browser focus to the element with the given id
     *
     * @param string $strElementId
     * @return string
     */
    public function setBrowserFocus($strElementId) {
        $strReturn = "
            <script type=\"text/javascript\">
                KAJONA.util.setBrowserFocus(\"".$strElementId."\");
            </script>";
        return $strReturn;
    }

    /**
     * Create a tree-view UI-element.
     * The nodes are loaded via AJAX by calling the url passed as the first arg.
     * The optional third param is an ordered list of systemid identifying the nodes to expand initially.
     * The tree may be wrapped into a two-column view.
     *
     * @param string $strLoadNodeDataUrl, systemid is appended automatically
     * @param string $strRootNodeSystemid
     * @param array $arrNodesToExpand
     * @param string $strSideContent
     *
     * @return string
     */
    public function getTreeview($strLoadNodeDataUrl, $strRootNodeSystemid = "", $arrNodesToExpand = array(), $strSideContent = "") {
        $arrTemplate = array();
        $arrTemplate["sideContent"] = $strSideContent;
        $arrTemplate["treeContent"] = $this->getTree($strLoadNodeDataUrl, $strRootNodeSystemid, $arrNodesToExpand);
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "treeview");
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Create a tree-view UI-element.
     * The nodes are loaded via AJAX by calling the url passed as the first arg.
     * The optional third param is an ordered list of systemid identifying the nodes to expand initially.
     * Renders only the tree, so no other content
     *
     * @param string $strLoadNodeDataUrl, systemid is appended automatically
     * @param string $strRootNodeSystemid
     * @param array $arrNodesToExpand
     * @param bool $bitOrderingEnabled
     * @param bool $bitHierachicalSortEnabled
     *
     *
     * @return string
     */
    public function getTree($strLoadNodeDataUrl, $strRootNodeSystemid = "", $arrNodesToExpand = array(), $bitOrderingEnabled = false, $bitHierachicalSortEnabled = false) {
        $arrTemplate = array();
        $arrTemplate["rootNodeSystemid"] = $strRootNodeSystemid;
        $arrTemplate["loadNodeDataUrl"] = $strLoadNodeDataUrl;
        $arrTemplate["treeId"] = generateSystemid();
        $arrTemplate["orderingEnabled"] = $bitOrderingEnabled ? "true" : "false";
        $arrTemplate["hierarchialSortEnabled"] = $bitHierachicalSortEnabled ? "true" : "false";
        $arrTemplate["treeviewExpanders"] = "";
        for($intI = 0; $intI < count($arrNodesToExpand); $intI++) {
            $arrTemplate["treeviewExpanders"] .= "\"".$arrNodesToExpand[$intI]."\"";
            if($intI < count($arrNodesToExpand)-1)
                $arrTemplate["treeviewExpanders"] .= ",";
        }
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "tree");
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Renderes the quickhelp-button and the quickhelp-text passed
     *
     * @param string $strText
     * @return string
     */
    public function getQuickhelp($strText) {
        $strReturn = "";
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "quickhelp");
        $arrTemplate = array();
        $arrTemplate["title"] = class_carrier::getInstance()->getObjLang()->getLang("quickhelp_title", "system");
        $arrTemplate["text"] = addslashes($strText);
        $strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);

        //and the button
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "quickhelp_button");
        $arrTemplate = array();
        $arrTemplate["text"] = class_carrier::getInstance()->getObjLang()->getLang("quickhelp_title", "system");
        $strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);

        return $strReturn;
    }

    /**
     * Generates the wrapper required to render the list of tags.
     *
     * @param string $strWrapperid
     * @param string $strTargetsystemid
     * @param string $strAttribute
     * @return string
     */
    public function getTaglistWrapper($strWrapperid, $strTargetsystemid, $strAttribute) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "tags_wrapper");
        $arrTemplate = array();
        $arrTemplate["wrapperId"] = $strWrapperid;
        $arrTemplate["targetSystemid"] = $strTargetsystemid;
        $arrTemplate["attribute"] = $strAttribute;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Renders a single tag (including the options to remove the tag again)
     *
     * @param string $strTagname
     * @param string $strTagId
     * @param string $strTargetid
     * @param string $strAttribute
     * @param bool $bitDelete
     *
     * @return string
     */
    public function getTagEntry($strTagname, $strTagId, $strTargetid, $strAttribute, $bitDelete = true) {

        if($bitDelete)
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "tags_tag_delete");
        else
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "tags_tag");

        $arrTemplate = array();
        $arrTemplate["tagname"] = $strTagname;
        $arrTemplate["strTagId"] = $strTagId;
        $arrTemplate["strTargetSystemid"] = $strTargetid;
        $arrTemplate["strAttribute"] = $strAttribute;
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }


    /**
     * Returns a regular text-input field
     *
     * @param string $strName
     * @param string $strTitle
     * @param string $strClass
     * @return string
     */
    public function formInputTagSelector($strName, $strTitle = "", $strClass = "inputText") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "input_tagselector");
        $arrTemplate = array();
        $arrTemplate["name"] = $strName;
        $arrTemplate["title"] = $strTitle;
        $arrTemplate["class"] = $strClass;

        $arrTemplate["ajaxScript"] = "
	        <script type=\"text/javascript\">
                    $(function() {
                        function split( val ) {
                            return val.split( /,\s*/ );
                        }

                        function extractLast( term ) {
                            return split( term ).pop();
                        }

                        KAJONA.admin.".$strName." = $('#".uniStrReplace(array("[", "]"), array("\\\[", "\\\]"), $strName)."').autocomplete({
                            source: function(request, response) {
                                $.ajax({
                                    url: '".getLinkAdminXml("tags", "getTagsByFilter")."',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        filter:  extractLast( request.term )
                                    },
                                    success: response
                                });
                            },
//                            focus: function() {
//                                return false;
//                            },
                            select: function( event, ui ) {
                                var terms = split( this.value );
                                terms.pop();
                                terms.push( ui.item.value );
                                terms.push( '' );
                                this.value = terms.join( ', ' );
                                return false;
                            },
                            minLength: 1,
                            delay: KAJONA.util.isTouchDevice() ? 2000 : 0,
                            messages: {
                                noResults: '',
                                results: function() {}
                            }
                        });
                    });
	        </script>
        ";

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID, true);
    }


    /**
     * Renders the list of aspects available
     * @param $strLastModule
     * @param $strLastAction
     * @param $strLastSystemid
     *
     * @return string
     * @todo param handling? remove params?
     */
    public function getAspectChooser($strLastModule, $strLastAction, $strLastSystemid) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "aspect_chooser");
        $strTemplateRowID = $this->objTemplate->readTemplate("/elements.tpl", "aspect_chooser_entry");

        $arrTemplate = array();
        $arrTemplate["options"] = "";

        //process rows
        $strCurrentId = class_module_system_aspect::getCurrentAspectId();
        $arrAspects = class_module_system_aspect::getObjectList(true);

        $intNrOfAspects = 0;
        foreach($arrAspects as $objSingleAspect) {
            if($objSingleAspect->rightView()) {
                $arrSubtemplate = array();
                //start on dashboard since the current module may not be visible in another aspect
                $arrSubtemplate["value"] = getLinkAdminHref("dashboard", "", "&aspect=".$objSingleAspect->getSystemid());
                $arrSubtemplate["name"] = $objSingleAspect->getStrDisplayName();
                $arrSubtemplate["selected"] = $strCurrentId == $objSingleAspect->getSystemid() ? "selected=\"selected\"" : "";

                $arrTemplate["options"] .= $this->objTemplate->fillTemplate($arrSubtemplate, $strTemplateRowID);
                $intNrOfAspects++;
            }
        }

        if($arrTemplate["options"] == "" || $intNrOfAspects < 2)
            return "";

        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }

    /**
     * Creates a tooltip shown on hovering the passed text.
     * If both are the same, text and tooltip, only the plain text is returned.
     *
     * @param string $strText
     * @param string $strTooltip
     * @return string
     * @since 3.4.0
     */
    public function getTooltipText($strText, $strTooltip) {
        if($strText == $strTooltip)
            return $strText;

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "tooltip_text");
        return $this->objTemplate->fillTemplate(array("text" => $strText, "tooltip" => $strTooltip), $strTemplateID);
    }

    // --- Calendar Fields ----------------------------------------------------------------------------------

    /**
     * Renders a legend below the current calendar in order to illustrate the different event-types.
     *
     * @param array $arrEntries
     * @return string
     */
    public function getCalendarLegend(array $arrEntries) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_legend");
        $strTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_legend_entry");

        $strEntries = "";
        foreach($arrEntries as $strName => $strClass)
            $strEntries .= $this->objTemplate->fillTemplate(array("name" => $strName, "class" => $strClass), $strTemplateEntryID);

        return $this->objTemplate->fillTemplate(array("entries" => $strEntries), $strTemplateID);
    }

    /**
     * Renders a legend below the current calendar in order to illustrate the different event-types.
     *
     * @param array $arrEntries
     * @return string
     */
    public function getCalendarFilter(array $arrEntries) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_filter");
        $strTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_filter_entry");

        $strEntries = "";
        foreach($arrEntries as $strId => $strName) {
            $strChecked = class_carrier::getInstance()->getObjSession()->getSession($strId) == "disabled" ? "" : "checked";
            $strEntries .= $this->objTemplate->fillTemplate(array("filterid" => $strId, "filtername" => $strName, "checked" => $strChecked), $strTemplateEntryID);
        }

        return $this->objTemplate->fillTemplate(array("entries" => $strEntries, "action" => getLinkAdminHref("dashboard", "calendar")), $strTemplateID);
    }

    /**
     * Creates a pager for the calendar, used to switch the current month.
     *
     * @param string $strBackwards
     * @param string $strCenter
     * @param string $strForwards
     * @return string
     * @since 3.4
     */
    public function getCalendarPager($strBackwards, $strCenter, $strForwards) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_pager");
        return $this->objTemplate->fillTemplate(array("backwards" => $strBackwards, "forwards" => $strForwards, "center" => $strCenter), $strTemplateID);
    }

    /**
     * Renders a container used to place the calender via ajax into.
     *
     * @param string $strContainerId
     * @return string
     * @since 3.4
     */
    public function getCalendarContainer($strContainerId) {
       $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_container");
        return $this->objTemplate->fillTemplate(array("containerid" => $strContainerId), $strTemplateID);
    }

    /**
     * Creates the wrapper to embedd the calendar.
     *
     * @param string $strContent
     * @return string
     * @since 3.4
     */
    public function getCalendarWrapper($strContent) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_wrapper");
        return $this->objTemplate->fillTemplate(array("content" => $strContent), $strTemplateID);
    }

    /**
     * Renders the header-row of the calendar. In general those are the days.
     *
     * @param array $arrHeader
     * @return string
     * @since 3.4
     */
    public function getCalendarHeaderRow(array $arrHeader) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_header_row");
        $strTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_header_entry");

        $strEntries = "";
        foreach($arrHeader as $strOneHeader)
            $strEntries .= $this->objTemplate->fillTemplate(array("name" => $strOneHeader), $strTemplateEntryID);

        return $this->objTemplate->fillTemplate(array("entries" => $strEntries), $strTemplateID);
    }

    /**
     * Renders a complete row of days.
     *
     * @param string $strContent
     * @return string
     * @since 3.4
     */
    public function getCalendarRow($strContent) {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_row");
        return $this->objTemplate->fillTemplate(array("entries" => $strContent), $strTemplateID);
    }

    /**
     * Renders a single entry within the calendar. In most cases this is a single day.
     *
     * @param string $strContent
     * @param string $strDate
     * @param string $strClass
     * @return string
     * @since 3.4
     */
    public function getCalendarEntry($strContent, $strDate, $strClass = "calendarEntry") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_entry");
        return $this->objTemplate->fillTemplate(array("content" => $strContent, "date" => $strDate, "class" => $strClass), $strTemplateID);
    }

    /**
     * Renders a single calendar-event
     *
     * @param string $strContent
     * @param string $strId
     * @param string $strHighlightId
     * @param string $strClass
     * @return string
     * @since 3.4
     */
    public function getCalendarEvent($strContent, $strId = "", $strHighlightId = "", $strClass = "calendarEvent") {
        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "calendar_event");
        if($strId == "")
            $strId = generateSystemid();
        return $this->objTemplate->fillTemplate(array("content" => $strContent, "class" => $strClass, "systemid" => $strId, "highlightid" =>$strHighlightId), $strTemplateID);
    }

    //---contect menues ---------------------------------------------------------------------------------

    /**
     * Creates the markup to render a js-based contex-menu.
     * Each entry is an array with the keys
     *   array("name" => "xx", "link" => "xx", "submenu" => array());
     * The support of submenus depends on the current implementation, so may not be present everywhere!
     *
     * @since 3.4.1
     * @param string $strIdentifier
     * @param string[] $arrEntries
     * @return string
     */
    public function registerMenu($strIdentifier, array $arrEntries) {
        $strTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contextmenu_entry");
        $strTemplateFullEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contextmenu_entry_full");
        $strDividerTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contextmenu_divider_entry");
        $strSubmenuTemplateEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contextmenu_submenucontainer_entry");
        $strSubmenuTemplateFullEntryID = $this->objTemplate->readTemplate("/elements.tpl", "contextmenu_submenucontainer_entry_full");
        $strEntries = "";
        foreach($arrEntries as $arrOneEntry) {


            if(!isset($arrOneEntry["link"])) $arrOneEntry["link"] = "";
            if(!isset($arrOneEntry["name"])) $arrOneEntry["name"] = "";
            if(!isset($arrOneEntry["onclick"])) $arrOneEntry["onclick"] = "";
            if(!isset($arrOneEntry["fullentry"])) $arrOneEntry["fullentry"] = "";

            $arrTemplate = array(
                "elementName" => $arrOneEntry["name"],
                "elementAction" => $arrOneEntry["onclick"],
                "elementLink" => $arrOneEntry["link"],
                "elementActionEscaped" => uniStrReplace("'", "\'", $arrOneEntry["onclick"]),
                "elementFullEntry" => $arrOneEntry["fullentry"]
            );

            if($arrTemplate["elementFullEntry"] != "")
                $strCurTemplate = $strTemplateFullEntryID;
            else
                $strCurTemplate = $strTemplateEntryID;


            if(isset($arrOneEntry["submenu"]) && count($arrOneEntry["submenu"]) > 0) {
                $strSubmenu = "";
                foreach($arrOneEntry["submenu"] as $arrOneSubmenu) {
                    $strCurSubTemplate = $strTemplateEntryID;

                    if(!isset($arrOneEntry["link"])) $arrOneEntry["link"] = "";
                    if(!isset($arrOneEntry["name"])) $arrOneEntry["name"] = "";
                    if(!isset($arrOneEntry["onclick"])) $arrOneEntry["onclick"] = "";
                    if(!isset($arrOneEntry["fullentry"])) $arrOneEntry["fullentry"] = "";

                    if($arrOneSubmenu["name"] == "") {
                        $arrSubTemplate = array();
                        $strCurSubTemplate = $strDividerTemplateEntryID;
                    }
                    else {
                        $arrSubTemplate = array(
                            "elementName" => $arrOneSubmenu["name"],
                            "elementAction" => $arrOneSubmenu["onclick"],
                            "elementLink" => $arrOneSubmenu["link"],
                            "elementActionEscaped" => uniStrReplace("'", "\'", $arrOneSubmenu["onclick"]),
                            "elementFullEntry" => $arrOneEntry["fullentry"]
                        );

                        if($arrSubTemplate["elementFullEntry"] != "")
                            $strCurSubTemplate = $strTemplateFullEntryID;

                    }

                    $strSubmenu .= $this->objTemplate->fillTemplate($arrSubTemplate,  $strCurSubTemplate);
                }
                $arrTemplate["entries"] = $strSubmenu;


                if($arrTemplate["elementFullEntry"] != "")
                    $strCurTemplate = $strSubmenuTemplateFullEntryID;
                else
                    $strCurTemplate = $strSubmenuTemplateEntryID;
            }


            $strEntries .= $this->objTemplate->fillTemplate($arrTemplate, $strCurTemplate);
        }

        $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "contextmenu_wrapper");
        $arrTemplate = array();
        $arrTemplate["id"] = $strIdentifier;
        $arrTemplate["entries"] = uniSubstr($strEntries, 0, -1);
        return $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    }
}
