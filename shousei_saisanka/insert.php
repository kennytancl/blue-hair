<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style/style.css">
    <script type="text/javascript" src="js/script.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/cleave.min.js"></script>
</head>

<body class="insert">
    <?php
    include 'inclu.php';
    $tableName = GetUrlPara("tableName");
    $addEdit = GetUrlPara("addEdit");

    $isAdd = $addEdit == "add";
    $isEdit = $addEdit == "edit";
    $id = $isEdit ? GetUrlPara("id") : "";

    $submission = LoadFieldStr('submission');
    PrintScript("SessSetItem('isSubmission', false)");
    if ($submission) {
        // echo $submission;
        $document = json_decode($submission, true);
        $actionSQL = array("add" => "CREATE", "edit" => "UPDATEall");
        $updateOutput = LoadPY("CRUD", $actionSQL[$addEdit] . $tableName, [$submission]);
        // echo $updateOutput;
        $updateOutput = DecodeArrOutput($updateOutput);
        if($updateOutput) {
            $updateOutput = $updateOutput[0];
            $isInsertSuccess = true;
            foreach ($updateOutput as $key => $val) {
                if($val === "False"){ $isInsertSuccess = false; break; }
            }
            if($isInsertSuccess) {
                if($isAdd) { // Reset search date
                    foreach(["searchDay", "searchMonth", "searchYear"] as $resetSearch)
                        unset($_SESSION[$resetSearch]);
                }
                $_SESSION["searchFurnaceNo"] = $document["furnace_no"];
                PrintScript("InsertSuccess('$addEdit', '$submission')");
            }
            // echo "success";
        } else $isInsertSuccess = false;
        PrintScript("SessSetItem('isSubmission', true)");
    }

    if ($isEdit) {
        if (!$submission) {
            $document = LoadPY("CRUD", "READ" . $tableName, ["id", $id]);
            $document = DecodeArrOutput($document)[0];
        }
    } if(!isset($document)) $document = array();
    // print_r($document);

    function InputField($type, $field, $isReq = true, $options = [])
    {
        global $document; // $isAdd, $isEdit,
        $idName = 'id="' . $field . '" name="' . $field . '" ';
        // $fieldIsFurnace = $field == "furnace_no";

        $data = "";
        if (array_key_exists($field, $document)) $data = $document[$field];
        if ($type == "select") {
            echo '<select ' . $idName . '>';
            foreach ($options as $o) {
                echo '<option value="' . $o . '" ';
                if ($data == $o) echo 'selected';
                echo '>' . $o . '</option>';
            }
        } else {
            echo '<input type="' . $type . '" ' . $idName;
            if ($type == "checkbox" && $data) echo 'checked '. $data.' ' ;
            if ($type != "checkbox") {
                echo 'value="' . $data . '" ';
                if ($isReq) echo 'required ';
            }
            echo 'autocomplete="off">';
        }
    }

    function EditOnlyInputField($type, $field)
    {
        global $isAdd, $isEdit;
        InputField($type, $field, false);
        if ($isAdd) {
            PrintScript("DocGetEID('$field').type = 'hidden';");
            PrintScript("DocGetEID('$field').value = 'NULL';");
            echo '-';
        }
    }
    ?>
    <script>SessSetItem('action', '<?= $addEdit ?>');</script>
    <div class="back-container">
        <a href="<?= GetJumpPage("insert.php", "record.html", "", ["addEdit", "id"]) ?>">
            <button class="top blue back"><img src="img/back.png"></button>
        </a>
    </div>
    <h1><?= $tables[$tableName]->title ?> <span class="headAddEdit">(<?= strtoupper($addEdit) ?>)</span></h1>
    <div id="popup"></div>
    <div class="main">
        <div class="insert">
            <form action="<?= $url ?>" method="post" id="recordForm" onsubmit="return ConfirmInsert();">
                <table>
                    <tr>
                        <th class="tall" colspan="3">
                            INPUT & COLLECTING RECORD
                        </th>
                    </tr>
                    <tr>
                        <th colspan="3">Furnace No:</th>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <!-- //InputField("select", "furnace_no", true, $_SESSION["furnaceNos"]); ?> -->
                            <?php $furnaceNos = $_SESSION["furnaceNos"]; $i = 0;
                               foreach ($furnaceNos as $furnace) : 
                                if ($i != 0 && $i % 6 == 0) { echo "<br>"; } ?>
                                <input type="radio" id="<?= $furnace ?>" name="furnace_no" value="<?= $furnace ?>" 
                                <?php if (
                                    (isset($document["furnace_no"]) && $furnace == $document["furnace_no"]) || 
                                    ($isAdd && !$submission && isset($_SESSION["searchFurnaceNo"]) && $furnace == $_SESSION["searchFurnaceNo"]) ||
                                    ($furnace == $furnaceNos[0])
                                ) { echo "checked"; } ?>>
                                <label for="<?= $furnace ?>"><?= $furnace ?></label>
                            <?php $i++; endforeach ?>
                        </td>
                    </tr>
                    <?php if ($tableName == $QS_FURNACE) : ?>
                        <tr>
                            <th colspan="3">Inspected By:</th>
                        </tr>
                        <tr>
                            <td colspan="3"><?php InputField("datetime-local", "inspection_dt"); ?></td>
                        </tr>

                        
                        <tr>
                            <th colspan="2">Item Name: (TPN)</th>
                            <th>Lot No:</th>
                        </tr>
                        <tr>
                            <td colspan="2"><?php InputField("text", "item_type"); ?></td>
                            <td><?php InputField("text", "lot_no"); ?></td>
                        </tr>
                        <tr>
                            <th colspan="2">Item Name: (PDN)</th>
                            <th>Lot Quantity:</th>
                        </tr>
                        <tr>
                            <td colspan="2" id="pdn_name"></td>
                            <td><?php InputField("number", "lot_quantity"); ?></td>
                        </tr>
                        <tr>
                            <th colspan="3">Input Feeder Unit</th>
                        <tr>
                        <tr>
                            <th>Item:</th>
                            <th>Slit:</th>
                            <th>Slit Plate NFMD:</th>
                        </tr>
                        <tr>
                            <td><?php
                                InputField("select", "item", false, ["063", "105"]);
                                PrintScript('DocGetEID("item").setAttribute("onchange", "ChangeItemSetting()");');
                                ?></td>
                            <td><?php InputField("select", "slit", false, ["G-H6", "G-H4", "SW4"]); ?></td>
                            <td><?php InputField("checkbox", "slit_nfmd"); ?></td>
                        </tr>
                        <tr>
                            <th colspan="3">Furnace Tube Unit</th>
                        </tr>
                        <tr>
                            <th colspan="2"><span id="item_setting">
                                    <?php if ($isAdd) {
                                        echo "063";
                                    } elseif ($isEdit) {
                                        echo $document["item"];
                                    } ?>
                                </span> Setting:</th>
                            <th>Furnace tube NFMD:</th>
                        </tr>
                        <tr>
                            <td colspan="2"><?php InputField("number", "temp"); ?></td>
                            <td><?php InputField("checkbox", "tube_nfmd"); ?></td>
                        </tr>
                        <tr>
                            <th rowspan="2">Cooling Feeder Unit</th>
                            <th>Cooling Feeder NFMD:</th>
                            <td><?php InputField("checkbox", "feeder_nfmd"); ?></td>
                        </tr>
                        <tr>
                            <th>Collecting Tray NFMD:</th>
                            <td><?php InputField("checkbox", "tray_nfmd"); ?></td>
                        </tr>
                        <tr>
                            <th>Done By:</th>
                            <th>Checked By:</th>
                            <th>Verified By:</th>
                        </tr>
                        <tr>
                            <td><?php InputField("number", "done_by"); ?></td>
                            <td><?php InputField("number", "checked_by"); ?></td>
                            <td><?php InputField("number", "verified_by"); ?></td>
                        </tr>
                        <tr>
                            <th colspan="3">Remark:</th>
                        </tr>
                        <tr>
                            <td colspan="3"><?php InputField("text", "remark", false); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php if ($tableName != $MRS) : ?>
                            <tr>
                                <?php if ($tableName == $N2_SAISANKA) : ?>
                                    <th colspan="2">Ticket No:</th>
                                    <th><?= $o2 ?> Free:</th>
                                <?php elseif ($tableName != $QS_FURNACE) : ?>
                                    <th colspan="2">Ticket No:</th>
                                    <th>Confirmation:</th>
                                <?php endif ?>
                            </tr>
                            <tr>
                                <?php if ($tableName == $N2_SAISANKA) : ?>
                                    <td colspan="2"><?php InputField("number", "ticket_no"); ?></td>
                                    <td><?php InputField("checkbox", "o2_free"); ?></td>
                                <?php elseif ($tableName != $QS_FURNACE) : ?>
                                    <?php if ($tableName != $MRS) : ?>
                                        <td colspan="2"><?php InputField("number", "ticket_no"); ?></td>
                                    <?php endif ?>
                                    <td><?php InputField("checkbox", "confirmation"); ?></td>
                                <?php endif ?>
                            </tr>
                        <?php endif ?>
                        <tr>
                            <th colspan="2">Item Name: (TPN)</th>
                            <th>Lot No:</th>
                        </tr>
                        <tr>
                            <td colspan="2"><?php InputField("text", "item_name"); ?></td>
                            <td><?php InputField("text", "lot_no"); ?></td>
                        </tr>
                        <tr>
                            <th colspan="2">Item Name: (PDN)</th>
                            <th>Lot Size:</th>
                        </tr>
                        <tr>
                            <td colspan="2" id="pdn_name"></td>
                            <td><?php InputField("text", "lot_size"); ?></td>
                        </tr>
                        <tr>
                            <?php if ($tableName == $MRS) : ?>
                                <th>Key No:</th>
                            <?php else : ?>
                                <th>Chip Layer:</th>
                            <?php endif ?>
                            <?php if ($tableName == $MRS) : ?>
                                <th colspan="2">Date:</th>
                            <?php else : ?>
                                <th>Input Date:</th>
                                <th>Output Date:</th>
                            <?php endif ?>
                        </tr>
                        <tr>
                            <td>
                                <?php if ($tableName == $MRS) : ?>
                                    <?php InputField("text", "key_no"); ?>
                                <?php else : ?>
                                    <?php InputField("select", "chip_layer", false, ["CC", "1 Layer"]); ?>
                                <?php endif ?>
                            </td>
                            <?php if ($tableName == $MRS) : ?>
                                <td colspan="2"><?php InputField("date", "date"); ?></td>
                            <?php else : ?>
                                <td><?php InputField("date", "input_date"); ?></td>
                                <td><?php EditOnlyInputField("date", "output_date"); ?></td>
                            <?php endif ?>
                        </tr>
                        <tr>
                            <?php if ($tableName == $MRS) : ?>
                                <th>Pattern No.:</th>
                                <th>Start Time:</th>
                                <th>End Time:</th>
                            <?php else : ?>
                                <th>
                                    <?php if (!in_array($tableName, $negativeNav)) : ?>
                                        Setter Pilling:
                                    <?php endif ?>
                                </th>
                                <th>Input Time:</th>
                                <th>Output Time:</th>
                            <?php endif ?>
                        </tr>
                        <tr>
                            <?php if ($tableName == $MRS) : ?>
                                <td>
                                    <span id="pattern_no_dis"></span>
                                    <?php InputField("hidden", "pattern_no"); ?>
                                </td>
                                <td><?php InputField("time", "start_time"); ?></td>
                                <td><?php EditOnlyInputField("time", "end_time"); ?></td>
                            <?php else : ?>
                                <td>
                                    <?php if (!in_array($tableName, $negativeNav)) : ?>
                                        <?php InputField("text", "setter_pilling"); ?>
                                    <?php endif ?>
                                </td>
                                <td><?php InputField("time", "input_time"); ?></td>
                                <td><?php EditOnlyInputField("time", "output_time"); ?></td>
                            <?php endif ?>
                        </tr>
                        <?php if ($tableName != $MRS) : ?>
                            <tr>
                                <th colspan="3">
                                    <?php if ($tableName == $N2_SAISANKA) : ?>
                                        <?= $n2 ?> SETTER
                                    <?php else : ?>
                                        <?= $o2 ?> SETTER / Wire Mesh
                                    <?php endif ?>
                                </th>
                            </tr>
                        <?php endif ?>
                        <tr>
                            <?php if ($tableName == $MRS) : ?>
                                <th colspan="3">Quantity</th>
                            <?php else : ?>
                                <th colspan="2">Narabe</th>
                                <th>Collecting</th>
                            <?php endif ?>
                        </tr>
                        <tr>
                            <?php if ($tableName == $MRS) : ?>
                                <th>Setter Plan:</th>
                                <th>Setter Actual:</th>
                                <th>Stick Chip:</th>
                            <?php else : ?>
                                <th>Quantity:</th>
                                <th>PIC:</th>
                                <th>PIC:</th>
                            <?php endif ?>
                        </tr>
                        <tr>
                            <?php if ($tableName == $MRS) : ?>
                                <td><?php InputField("number", "quantity_plan"); ?></td>
                                <!-- <td>?php InputField("number", "quantity_actual"); ?></td> -->
                                <td><?php EditOnlyInputField("number", "quantity_actual"); ?></td>
                                <td><?php EditOnlyInputField("number", "quantity_stick"); ?></td>
                            <?php else : ?>
                                <td><?php InputField("number", "narabe_quantity"); ?></td>
                                <td><?php InputField("number", "narabe_pic"); ?></td>
                                <td><?php EditOnlyInputField("number", "collect_pic"); ?></td>
                            <?php endif ?>
                        </tr>
                        <?php if ($tableName == $MRS) : ?>
                            <tr>
                                <th>Group</th>
                                <th>Narabe PIC</th>
                                <th>Collecting PIC</th>
                            </tr>
                            <tr>
                                <td><?php InputField("text", "grp"); ?></td>
                                <td><?php InputField("number", "narabe_pic"); ?></td>
                                <td><?php EditOnlyInputField("number", "collect_pic"); ?></td>
                            </tr>
                        <?php endif ?>
                    <?php endif ?>
                    <tr class="confirm">
                        <td colspan="3">Confirm all details are correct?</td>
                    </tr>
                    <tr>
                        <th colspan="3">
                            <?php if ($isAdd) : ?>
                                <input type="reset" class="save reset orange" value="RESET" onclick="location.reload();">
                            <?php endif ?>
                            <input type="submit" class="save green" value="SAVE">
                            <input type="button" class="confirm orange" value="CANCEL" onclick="RevertBackForm()">
                            <input type="button" class="confirm green" value="CONFIRM" onclick="SubmitInsertForm()">
                        </th>
                    </tr>
                </table>
                <?php if ($isEdit) : ?>
                    <input type="hidden" id="id" name="id" value="<?= GetUrlPara("id") ?>">
                <?php endif ?>
                <input type="hidden" id="submission" name="submission">
            </form>
        </div>
    </div>

    <script type="text/javascript" src="js/script.js"></script>
    <script>InsertInit();</script>
    <?php
        if ($submission && !$isInsertSuccess) {
            $savingFailed = "Saving failed !!!\\n";
            $itemNameInp = $updateOutput["itemNameInp"];
            if ($updateOutput["itemNameFound"] === "False") 
                PrintScript("alert('".$savingFailed."Could not find item \"$itemNameInp\"')");
            else if ($updateOutput["itemNameMetCondi"] === "False") 
                PrintScript("alert('".$savingFailed."Item $itemNameInp\\ndoes not belongs to this checksheet')");
            else if ($updateOutput["pwTrue"] === "False") 
                PrintScript('ShowPopUp("edit");');
        }
    ?>
</body>

</html>