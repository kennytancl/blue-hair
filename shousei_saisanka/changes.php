<?php
include "inclu.php";

$returnId = "";
$check_box = LoadPostFieldStr("check_box");
$update_small = LoadPostFieldStr("update_small");
echo $update_small;
if ($check_box || $update_small) {
    if ($check_box) $liStr = $check_box;
    if ($update_small) $liStr = $update_small;
    $liStr = explode(",", $liStr);
    LoadPY("CRUD", "UPDATE".$tableName, $liStr);
    $returnId = end($liStr);
}

$delete_document = LoadPostFieldStr("delete_document");
if ($delete_document) {
    $delOutput = LoadPY("CRUD", "DELETE".$tableName, [$delete_document]);
    $delOutput = DecodeArrOutput($delOutput);
    if($delOutput) $delOutput = $delOutput[0];
    if($delOutput["pwTrue"] == "False") {
        $returnId = $delOutput["id"];
    }
}
if($returnId) {
    $mainOutput = LoadPY("CRUD", "READ".$tableName, ["id", $returnId]);
    $mainOutput = DecodeArrOutput($mainOutput);
    $isRowOnly = true; include "recordTable.php";
}

// $output = LoadPY("CRUD", "READ".$tableName, 
// ["furnace_no", "DAY({})", "MONTH({})", "YEAR({})", $searchFurnaceNo, 
// $searchDay, array_search($searchMonth, $months) + 1, $searchYear]);
// echo $output;
// $output = DecodeArrOutput($output);

?>