<?php
include "inclu.php";
$input_tpn = GetUrlPara("input_tpn");
$get_pdn = GetUrlPara("get_pdn");
if($input_tpn || $get_pdn) {
    if($input_tpn) {
        $action = "input_tpn";
        $arg = [$input_tpn, $tableName];
    } else if($get_pdn) {
        $action = "get_pdn";
        $arg = $get_pdn;
    }
    $output = LoadPY("CRUD", $action, $arg);
    $output = DecodeArrOutput($output);
    if(count($output) > 0) $output = $output[0];
    else die();
    if ($input_tpn && $output["metCondi"] === "False")
        PrintScript("alert('Item ".($input_tpn)."\\ndoes not belongs to this checksheet')");
    else if (isset($output["pdn_name"])) 
        print_r(json_encode($output));
}

?>
