<?php session_start(); ?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<?php
include 'inclu.php';

$_SESSION["firstLoad"] = true;
$itemNames = LoadFieldArr("itemName");
$verify = LoadPY("verify", "", $itemNames);
$itemIDs = GetDecArr($verify, "itemIDs");
$submission = json_decode(LoadFieldStr('submission'), true);
$i = 0;
foreach ($submission["rec_items"] as &$item) {
    if (count($itemIDs) > $i) 
        $item["item_id"] = $itemIDs[$i];
    if (count($itemNames) > $i) 
        $item["item_name"] = $itemNames[$i];
    $i++;
}
print_r(json_encode($submission));
if (GetDecStr($verify, "isError")) {
    $_SESSION["verify"] = $verify;
    $_SESSION["submission"] = $submission;
    PrintScript('alert("' . GetDecStr($verify, "msg") . '")');
    header("Location: insert.php");
} else {
    LoadPY("CRUD", "UPDATE_all", [json_encode($submission)]);
    header("Location: index.php");
}
?>
<script type="text/javascript" src="script.js"></script>
</body>

</html>