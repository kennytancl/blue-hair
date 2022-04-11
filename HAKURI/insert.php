<?php session_start(); ?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    include 'inclu.php';
    $addEdit = $_SESSION["addEdit"];
    $isAdd = $addEdit == "add";
    $isEdit = $addEdit == "edit";
    
    $isSubmit = false;
    $submission = LoadFieldStr('submission');
    if ($submission && $isAdd) {
        $isSubmit = true;
        LoadPY("CRUD", "CREATE", [$submission]);
    }

    $verify = "";
    $errorItems = $itemIDs = array();
    $itemNames = LoadFieldArr("itemName");
    $canVerify = isset($_SESSION["verify"]) || 
        ($itemNames && !$submission && !$_SESSION["firstLoad"]);
    if ($canVerify) {
        if (isset($_SESSION["verify"])) {
            $verify = $_SESSION["verify"]; unset($_SESSION["verify"]);
        } else {
            $verify = LoadPY("verify", LoadFieldStr("start_pic"), $itemNames);
        }
        if (GetDecStr($verify, "isError")) {
            PrintScript('alert("' . GetDecStr($verify, "msg") . '")');
            $errorItems = GetDecArr($verify, "errorItems");
        } else {
            $itemIDs = GetDecArr($verify, "itemIDs");
        }
    }
    $itemNames = LoadFieldArr("itemName");
    $lotNos = LoadFieldArr("lotNo");
    $machine_no = LoadFieldStr("machine_no");
    $start_pic = LoadFieldStr("start_pic");
    $end_pic = LoadFieldStr("end_pic");
    $selectAll = 'onClick="this.setSelectionRange(0, this.value.length)"';

    function ExtractForm($status, $data)
    {
        global $itemNames, $lotNos, $machine_no, $start_pic, $end_pic,
        $start_date, $start_time, $finish_date, $finish_time;

        $itemNames = array();
        $lotNos = array();
        $dataStr = "";
        switch ($status) {
            case 'NotSubmit':
                $dataStr = "items"; break;
            case 'IsSubmit':
                $dataStr = "rec_items"; break;
        }

        foreach($data[$dataStr] as $i) {
            if($status == 'NotSubmit' || (isset($i["item_name"]) && isset($i["lot_no"]))) {
                array_push($itemNames, $i["item_name"]);
                array_push($lotNos, $i["lot_no"]);
            }
        }
        $machine_no = $data["machine_no"];
        $start_pic = $data["start_pic"];
        $end_pic = $data["end_pic"];
        $start_dt = explode(',', $data["start_dt"]);
        $start_date = $start_dt[0]; $start_time = $start_dt[1];
        $finish_dt = explode(',', $data["finish_dt"]);
        $finish_date = $finish_dt[0]; $finish_time = $finish_dt[1];
    }

    $editRec = array();
    if($isEdit && !$submission && !isset($_SESSION["submission"])){
        $editRec = json_decode(LoadPY("CRUD", "READ_id", [$_SESSION["batchId"]]), true)[0];
        ExtractForm("NotSubmit", $editRec);
    }
    else if (isset($_SESSION["submission"])) {
        $submission = $_SESSION["submission"]; unset($_SESSION["submission"]);
        ExtractForm("IsSubmit", $submission);
    }
    ?>
    <script>
        localStorage.setItem('action', '<?= $addEdit ?>');
    </script>
    <div class="insert">
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="recordForm"
        <?php if($isEdit): ?>onsubmit="SubmitRecord()"<?php endif ?>>
            <table>
                <tr>
                    <th colspan="5">
                        HAKURI RECORD <span class="headAddEdit">(<?= strtoupper($addEdit) ?>)</span>
                    </th>
                </tr>
                <tr>
                    <td colspan="5">
                        Machine No:
                        <select name="machine_no" id="machine_no" onchange="UpdateTankNum()">
                            <option value="4" <?php if ($machine_no == "4"): ?>
                                selected <?php endif ?>>
                                HAKURI 04</option>
                            <option value="5" <?php if ($machine_no == "5"): ?>
                                selected <?php endif ?>>
                                HAKURI 05</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">Tank No:</th>
                    <th>Item Name:</th>
                    <th>Lot No:</th>
                    <th>Result:</th>
                </tr>
                <?php for ($x = 1; $x <= 4; $x++) : ?>
                    <input type="hidden" id="itemID<?= $x ?>" name="itemID<?= $x ?>"
                    value="<?php if (count($itemIDs) > $x - 1) {
                        echo $itemIDs[$x - 1];
                    } ?>">
                    <tr>
                        <td><img class="tankImgNo" id="tankImgNo<?= $x ?>"></td>
                        <td id="tankNo<?= $x ?>"><?= $x ?></td>
                        <td>
                            <input type="text" id="itemName<?= $x ?>" name="itemName<?= $x ?>"
                            <?= $selectAll ?> value="<?php if (count($itemNames) > $x - 1) { 
                                echo $itemNames[$x - 1]; } ?>" <?php if ($x == 1) { echo 'required'; } ?> 
                                oninput="InputItem('itemName', <?= $x ?>)" 
                                onkeydown="KeyDownItem(event, 'itemName', <?= $x ?>)" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" id="lotNo<?= $x ?>" name="lotNo<?= $x ?>" 
                            <?= $selectAll ?> value="<?php if (count($lotNos) > $x - 1) {
                                echo $lotNos[$x - 1]; } ?>" <?php if ($x == 1) { echo 'required'; } ?>
                                oninput="InputItem('lotNo', <?= $x ?>)" 
                                onkeydown="KeyDownItem(event, 'lotNo', <?= $x ?>)" autocomplete="off">
                        </td>
                        <td id="result<?= $x ?>">
                            <?php if (count($itemNames) > $x - 1) {
                                if (in_array($itemNames[$x - 1], $errorItems)) echo 'NG';
                                else echo 'OK';
                            } ?>
                        </td>
                    </tr>
                <?php endfor ?>
                <tr>
                    <th colspan="2">Grinding Time:</th>
                    <th>RPM:</th>
                    <th colspan="2"></th>
                </tr>
                <tr>
                    <td colspan="2">~ 7 minutes</td>
                    <input type="hidden" id="grindingMins" value="7">
                    <td>~ 185</td>
                    <input type="hidden" id="rpm" value="185">
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <th colspan="2">Start Date:</th>
                    <th>Finish Date:</th>
                    <th colspan="2">HAKURI PIC (start):</th>
                </tr>
                <tr>
                    <td id="StartDate" colspan="2">
                        <?php if ($isEdit) : ?>
                            <input type="date" id="startDate" name="startDate" value="<?= $start_date ?>" required>
                        <?php endif ?>
                    </td>
                    <td id="FinishDate">
                        <?php if ($isEdit) : ?>
                            <input type="date" id="finishDate" name="finishDate" value="<?= $finish_date ?>"
                            oninput="FinishDTRequired()">
                        <?php else : ?>-<?php endif ?>
                    </td>
                    <td colspan="2">
                        <input type="text" id="start_pic" name="start_pic" value="<?= $start_pic; ?>" required autocomplete="off">
                    </td>
                </tr>
                <tr>
                    <th colspan="2">Start Time:</th>
                    <th>Finish Time:</th>
                    <th colspan="2">HAKURI PIC (end):</th>
                </tr>
                <tr>
                    <td id="StartTime" colspan="2">
                        <?php if ($isEdit) : ?>
                            <input type="time" id="startTime" name="startTime" value="<?= $start_time ?>" required>
                        <?php endif ?>
                    </td>
                    <td>
                        <?php if ($isEdit) : ?>
                            <input type="time" id="finishTime" name="finishTime" value="<?= $finish_time ?>"
                            oninput="FinishDTRequired()">
                        <?php else : ?>-<?php endif ?>
                    </td>
                    <td colspan="2">
                        <?php if ($isEdit) : ?>
                            <input type="text" id="end_pic" name="end_pic" value="<?= $end_pic; ?>" autocomplete="off">
                        <?php else : ?>-<?php endif ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        <input type="submit" class="save" value="SAVE">
                        <?php if($isAdd) : ?>
                            <input type="button" class="reset" value="RESET" onclick="ResetAll()">
                        <?php endif ?>
                        <a href="index.php">
                            <input type="button" class="back" value="BACK">
                        </a>
                    </td>
                </tr>
            </table>
            <input type="hidden" id="rec_batch_id" name="rec_batch_id" value="<?= $_SESSION["batchId"] ?>">
            <input type="hidden" id="submission" name="submission">
        </form>
    </div>

    <script type="text/javascript" src="script.js"></script>
    <?php $isError = GetDecStr($verify, "isError");
    $isError = ($isError == "" && !empty($verify)) ? false : $isError;
    if ($itemNames && !$isError && !$isSubmit && !$_SESSION["firstLoad"]) : ?>
        <script>SubmitRecord();</script>
    <?php $isSubmit = false; endif ?>
    <script>
        window.onload = function () {
            <?php if (!$isEdit): ?> DisplayDT(); <?php endif ?>
            UpdateTankNum();
            InputItemNameAll();
            DocGetEID("itemName1").focus();
        }
        function ReturnHome() { location.href = "index.php"; }
    </script>
    <?php 
    $_SESSION["firstLoad"] = false; 
    if($isSubmit) PrintScript("setTimeout(ReturnHome, 1500)")
    ?>
</body>

</html>