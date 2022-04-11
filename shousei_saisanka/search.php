<?php
include "inclu.php";

$searchLi = ["searchFurnaceNo", "searchDay", "searchMonth", "searchYear"];
foreach ($searchLi as $sl) {
    $searchData = LoadPostFieldStr($sl);
    if($searchData) $_SESSION[$sl] = $searchData;
}
$furnaceNos = DecodeArrOutput(LoadPY("CRUD", "get_machines", [$tableName]));
$_SESSION["furnaceNos"] = $furnaceNos;
$days = array('X');
for ($x = 1; $x <= 31; $x++) array_push($days, $x);
$months = explode(",", LoadPY("dt", "months"));

$searchFurnaceNo = SearchSession("searchFurnaceNo", $furnaceNos[0]);
if(!in_array($searchFurnaceNo, $furnaceNos)) 
    $searchFurnaceNo = $furnaceNos[0];

$searchDay = SearchSession("searchDay", LoadPY("dt", "day"));
$searchMonth = SearchSession("searchMonth", LoadPY("dt", "month"));
$searchYear = SearchSession("searchYear", LoadPY("dt", "year"));

$searchLi = [$searchFurnaceNo, $searchDay, array_search($searchMonth, $months) + 1, $searchYear];
$output = LoadPY("CRUD", "READ".$tableName, 
    array_merge(["furnace_no", "DAY({})", "MONTH({})", "YEAR({})"], $searchLi));
// echo $output;
$mainOutput = DecodeArrOutput($output);
?>