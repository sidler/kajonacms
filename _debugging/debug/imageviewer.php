<?php
echo "<pre>\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Image Viewer for Admin Skin                                                   |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "|loading system kernel...                                                       |\n";

$objCarrier = class_carrier::getInstance();

echo "| loaded.                                                                   |\n";
echo "+-------------------------------------------------------------------------------+\n\n";

$strIconPath = "/module_v4skin/admin/skins/kajona_v4/pics/";
echo "\nIcon-Path = ". $strIconPath."\n";

$objFilesystem = new class_filesystem();
$arrFiles = $objFilesystem->getFilelist(_corepath_.$strIconPath, array(".png"));
echo "Found ".count($arrFiles)."\n";

echo "<table border=0 cellpadding=2>";
foreach ($arrFiles as $strOneFile) {
    echo "<tr><td><img src=\""._webpath_."/core/".$strIconPath.$strOneFile."\"></td>";
    echo "<td>".$strOneFile."</td></tr>";
}
echo "</table>";

echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) STB :-)                                                                   |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";

?>