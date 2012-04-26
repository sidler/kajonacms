<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_tags_admin.php 4485 2012-02-07 12:48:04Z sidler $                                  *
********************************************************************************************************/

/**
 * Config-file for the package-manager. Contains the list of manager available
 * @package module_packagemanager
 */

$config = array();

//comma-separated list of registered content-providers
$config["contentproviders"] = "class_module_packagemanager_contentprovider_local,class_module_packagemanager_contentprovider_kajona";