<?php
session_start();
$file = 'excel/HAKURI_RECORD(' . $_SESSION["exportDate"] . ')(Mac ' . $_SESSION["exportMac"] . ').xlsx';
ob_end_clean();
if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: "'.filetype($file).'"');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    unlink($file);
}
unset($_SESSION["exportMac"]);
unset($_SESSION["exportDate"]);
header("Location: index.php");
?>