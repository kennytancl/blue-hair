<?php
include 'search.php';
$newFileName = GetUrlPara("newFileName");
echo $newFileName ;
if($newFileName) {
    $exportFNPath = "export/$newFileName";
    $jsonFilePath = "$exportFNPath.json";
    $excelFilePath = "$exportFNPath.xlsx";
    ob_clean();
    ob_end_clean();
    if (file_exists($excelFilePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($excelFilePath).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($excelFilePath));
        flush(); // Flush system output buffer
        readfile($excelFilePath);
        ob_clean();
        unlink($excelFilePath);
        unlink($jsonFilePath);
        PrintScript("window.close()");
    }
} else {
    $newFileName = trim(LoadPY("export", $tableName, array_merge($searchLi, ["true"])));
    $fp = fopen("export\\".$newFileName.".json", 'w');
    fwrite($fp, json_encode($mainOutput));
    fclose($fp);
    LoadPY("export", $tableName, array_merge($searchLi, ["false"]));
    echo $newFileName;
}
?>