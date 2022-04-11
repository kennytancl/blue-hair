<!-- <!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    // include 'inclu.php';
    // $tableName = GetUrlPara("tableName");
    // $process = GetUrlPara("process");
    
    // $checkBox = LoadFieldStr("checkBox");
    // $update_small = LoadFieldStr("update_small");
    // if ($checkBox || $update_small) {
    //     if ($checkBox) $liStr = $checkBox;
    //     if ($update_small) $liStr = $update_small;
    //     echo LoadPY("CRUD", "UPDATE".$tableName,
    //         explode(",", $liStr));
    // }

    // $delete_document = LoadFieldStr("delete_document");
    // if ($delete_document) {
    //     $delete_id = $delete_document;
    //     LoadPY("CRUD", "DELETE".$tableName, [$delete_id]);
    // }

    // $days = array('X');
    // for ($x = 1; $x <= 31; $x++) array_push($days, $x);
    // $months = explode(",", LoadPY("dt", "months"));
    // $searchFurnaceNo = SearchSession("searchFurnaceNo", 1);
    
    // $jump_date = LoadFieldStr("jump_date");
    // if ($jump_date) {
    //     if($jump_date == "thisMonth") {
    //         $_SESSION["searchDay"] = "X";
    //     } elseif($jump_date == "today") {
    //         $_SESSION["searchDay"] = LoadPY("dt", "day");
    //     } 
    //     $_SESSION["searchMonth"] = LoadPY("dt", "month");
    //     $_SESSION["searchYear"] = LoadPY("dt", "year");
    //     $searchDay = $_SESSION["searchDay"];
    //     $searchMonth = $_SESSION["searchMonth"];
    //     $searchYear = $_SESSION["searchYear"];
    // } else {
    //     $searchDay = SearchSession("searchDay", LoadPY("dt", "day"));
    //     $searchMonth = SearchSession("searchMonth", LoadPY("dt", "month"));
    //     $searchYear = SearchSession("searchYear", LoadPY("dt", "year"));
    // }
    // $output = LoadPY("CRUD", "READ".$tableName, 
    //     ["furnace_no", "DAY({})", "MONTH({})", "YEAR({})", $searchFurnaceNo, 
    //     $searchDay, array_search($searchMonth, $months) + 1, $searchYear]);
    // // echo $output;
    // $output = DecodeArrOutput($output);
    ?>
    <div class="record">
        <div class="back-container">
            <a href="index.php">
                <button class="top blue back"><img src="img/back.png"></button>
            </a>
        </div>
        <div class="add-container">
            <a href="<?= GetJumpPage("record", "insert", '&addEdit=add') ?>">
                <button class="top blue add" onclick="IndexSubmit('add_document', <?= $searchFurnaceNo ?>)">
                    +
                </button>
            </a>
        </div>
        <h1><?= $tables[$tableName]->button ?> <?= strtoupper($process) ?> 
            CHECK SHEET</h1>
        <?php if (!in_array($tableName, $negativeNav)) : ?>
            <nav>
                <?php foreach ($tables as $name => $value) : ?>
                    <?php 
                        if (!in_array($name, $negativeNav)) : ?>
                        <button class="navBtn" id="nav<?= $name ?>" 
                        onclick="IndexLoad('<?= $name ?>')"><?= $value->button ?></button>
                    <?php endif ?>
                <?php endforeach ?>
            </nav>
        <?php endif ?>
        <form action="<?= $url ?>" method="post" id="search">
            <table>
                <tr>
                    <td rowspan="2">
                        <label>Furnace No.</label><br>
                        <button class="blue" type="button" onclick="ChangeSearch('-', 'searchFurnaceNo')"><</button>
                        <input type="number" id="searchFurnaceNo" name="searchFurnaceNo" value="<?= $searchFurnaceNo ?>" min="1">
                        <button class="blue" type="button" onclick="ChangeSearch('+', 'searchFurnaceNo')">></button>
                    </td>
                    <td>
                        <label>Day</label><br>
                        <button class="blue" type="button" onclick="ChangeSearch('-', 'searchDay')"><</button>
                        <?php SelectField("searchDay",  $searchDay, $days); ?>
                        <button class="blue" type="button" onclick="ChangeSearch('+', 'searchDay')">></button>
                    </td>
                    <td>
                        <label>Month</label><br>
                        <button class="blue" type="button" onclick="ChangeSearch('-', 'searchMonth')"><</button>
                        <?php SelectField("searchMonth",  $searchMonth, $months); ?>
                        <button class="blue" type="button" onclick="ChangeSearch('+', 'searchMonth')">></button>
                    </td>
                    <td>
                        <label>Year</label><br>
                        <button class="blue" type="button" onclick="ChangeSearch('-', 'searchYear')"><</button>
                        <input type="number" id="searchYear" name="searchYear" value="<?= $searchYear ?>" min="1000">
                        <button class="blue" type="button" onclick="ChangeSearch('+', 'searchYear')">></button>
                    </td>
                    <td rowspan="2">
                        <button class="green" type="submit">SEARCH</button>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <button class="green" type="button" 
                        onclick="AssignValueSubmit('jump_date','today','search')">TODAY</button>
                        <button class="green" type="button" 
                        onclick="AssignValueSubmit('jump_date','thisMonth','search')">THIS MONTH</button>
                    </td>
                </tr>
            </table>
            <input type="hidden" id="jump_date" name="jump_date">
        </form>
        <div class="lotNoFilter">
            <label>Lot No.:</label>
            <input type="text" id="lotNoFilter" name="lotNoFilter" oninput="FilterLotNo(this)">
            <button class="blue" type="button" onclick="ClearLotNo()">CLEAR</button>
        </div>
        <h3>
            Furnace No: <?= $searchFurnaceNo ?>
            (Date: <?php if($searchDay != "X") echo $searchDay.' '; ?><?= $searchMonth ?> <?= $searchYear ?>)
        </h3>
        <?php if ($output) : ?>
            <table id="mainTable">
                <?php if ($tableName == $QS_FURNACE) : ?>
                    <tr>
                        <th class="right" colspan="100">*NFMD = No foreign material and damage</th>
                    </tr>
                    <tr>
                        <th rowspan="4">No.</th>
                        <th class="right" colspan="3">Functioning Unit ></th>
                        <th colspan="3">Input Feeder U.</th>
                        <th colspan="3">Furnace Tube U.</th>
                        <th colspan="2">Cooling Feeder U.</th>
                        <th rowspan="4">Done By</th>
                        <th rowspan="4">Checked By</th>
                        <th rowspan="4">Verified By</th>
                        <th rowspan="4">Remark</th>
                    </tr>
                    <tr>
                        <th class="right" colspan="3">Area / Part ></th>
                        <th colspan="2">Input Feeder</th>
                        <th>Slit Plate</th>
                        <th colspan="2">Temp. Zone 4</th>
                        <th class="small">Furnace Tube</th>
                        <th class="small">Cooling Feeder</th>
                        <th class="small">Collecting Tray</th>
                    </tr>
                    <tr>
                        <th class="right" colspan="3">Judgement Standard ></th>
                        <th colspan="2">Slit Type</th>
                        <th class="small" rowspan="2">NFMD</th>
                        <th rowspan="2">063</th>
                        <th rowspan="2">105</th>
                        <th class="small" rowspan="2">NFMD</th>
                        <th class="small" rowspan="2">NFMD</th>
                        <th class="small" rowspan="2">NFMD</th>
                    </tr>
                    <tr>
                        <th>Item Type</th>
                        <th>Lot No</th>
                        <th>Lot Qty.</th>
                        <th class="small">Item</th>
                        <th class="small">Silt</th>
                    </tr>
                    <?php $docInd = 1; foreach ($output as $o) : ?>
                        <tr class="data_row_inspected">
                            <th colspan="100">
                                Inspected By: <?= $o["inspection_dt"] ?>&emsp;
                                <?php EditDelBtn($o['id'], false); ?>
                            </th>
                        </tr>
                        <tr class="data_row">
                            <td><?= $docInd; $docInd++; ?></td>
                            <td><?= $o["item_type"] ?></td>
                            <td class="lot_no"><?= $o["lot_no"] ?></td>
                            <td><?= $o["lot_quantity"] ?></td>
                            <td><?= $o["item"] ?></td>
                            <td><?= $o["slit"] ?></td>
                            <?php LoadCheckBox("slit_nfmd", $o); ?>
                            <td class="small">
                                <?php if($o["item"] == "063") : ?>
                                    <?= $o["temp"] ?> &#176;C
                                <?php else : ?>-<?php endif ?>
                            </td>
                            <td class="small">
                                <?php if($o["item"] == "105") : ?>
                                    <?= $o["temp"] ?> &#176;C
                                <?php else : ?>-<?php endif ?>
                            </td>
                            <?php LoadCheckBox("tube_nfmd", $o); ?>
                            <?php LoadCheckBox("feeder_nfmd", $o); ?>
                            <?php LoadCheckBox("tray_nfmd", $o); ?>
                            <td><?= $o["done_by"] ?></td>
                            <td><?= $o["checked_by"] ?></td>
                            <td><?= $o["verified_by"] ?></td>
                            <td><?= $o["remark"] ?></td>
                        </tr>
                    <?php endforeach ?>
                <?php else : ?>
                    <tr>
                        <th rowspan="3">No.</th>
                        <?php if ($tableName == $MRS) : ?>
                            <th rowspan="3">Date</th>
                            <th rowspan="3">Start Time</th>
                            <th rowspan="3">End Time</th>
                        <?php else : ?>
                            <th rowspan="3">Ticket</th>
                        <?php endif ?>
                        <th rowspan="3">Item Name</th>
                        <?php if ($tableName == $MRS) : ?>
                            <th rowspan="3">Key No</th>
                        <?php endif ?>
                        <th rowspan="3">Lot No.</th>
                        <th rowspan="3">Lot Size</th>
                        <?php if ($tableName == $MRS) : ?>
                            <th rowspan="2" colspan="3">Quantity</th>
                            <th rowspan="3">Stick Chip (%)</th>
                            <th rowspan="3">Pattern No</th>
                            <th rowspan="3">Narabe<br>PIC</th>
                            <th rowspan="3">Collecting<br>PIC</th>
                            <th rowspan="3">Group</th>
                        <?php elseif ($tableName != $N2_SAISANKA) : ?>
                            <th rowspan="3">Setter<br>Pilling</th>
                        <?php endif ?>
                        <?php if ($tableName != $MRS) : ?>
                            <th rowspan="3">Chip<br>Layer</th>
                            <th rowspan="3">Input By</th>
                            <th rowspan="3">Output By</th>
                        <?php endif ?>
                        <?php if ($tableName == $N2_SAISANKA) : ?>
                            <th colspan="3"><?= $n2 ?> Setter</th>
                            <th rowspan="3"><?= $o2 ?> Free</th>
                        <?php elseif ($tableName != $MRS) : ?>
                            <th colspan="4"><?= $o2 ?> Setter / Wire Mesh</th>
                        <?php endif ?>
                        <th rowspan="3">Button</th>
                    </tr>
                    <?php if ($tableName == $MRS) : ?>
                        <tr></tr>
                        <tr>
                            <th class="small">Setter<br>Plan</th>
                            <th class="small">Setter<br>Actual</th>
                            <th class="small">Stick<br>Chip</th>
                        </tr>
                    <?php else : ?>
                        <tr>
                            <th colspan="2">Narabe</th>
                            <th>Collecting</th>
                            <?php if ($tableName != $N2_SAISANKA) : ?>
                                <th rowspan="2">Confirm</th>
                            <?php endif ?>
                        </tr>
                        <tr>
                            <th class="small">Qty.</th>
                            <th class="small">PIC</th>
                            <th class="small">PIC</th>
                        </tr>
                    <?php endif ?>
                    <?php $docInd = 1; foreach ($output as $o) : 
                        if(isset($o["collect_pic"])) $id = $o["id"]; ?>
                        <tr class="data_row">
                            <td><?= $docInd; $docInd++; ?></td>
                            <?php if ($tableName == $MRS) : ?>
                                <td><?= $o["date"] ?></td>
                                <td><?= $o["start_dt"] ?></td>
                                <td>
                                    <?php if ($o["end_dt"] == '-') : ?>
                                        <?php Working() ?>
                                    <?php else : ?>
                                        <?= $o["end_dt"] ?>
                                    <?php endif ?>
                                </td>
                            <?php else : ?>
                                <td><?= $o["ticket_no"] ?></td>
                            <?php endif ?>
                            <td><?= $o["item_name"] ?></td>
                            <?php if ($tableName == $MRS) : ?>
                                <td><?= $o["key_no"] ?></td>
                            <?php endif ?>
                            <td class="lot_no"><?= $o["lot_no"] ?></td>
                            <td><?= $o["lot_size"] ?></td>
                            <?php if ($tableName == $MRS) : ?>
                                <td class="small"><?= $o["quantity_plan"] ?></td>
                                <td class="small"><?= $o["quantity_actual"] ?></td>
                                <td class="small"><?php SmallForm("number", $o, "quantity_stick", $id) ?></td>
                                <td>
                                    <?php if($o["quantity_stick"] || $o["quantity_stick"] === 0) {
                                        echo round($o["quantity_stick"] / $o["lot_size"] * 100, 2);
                                    } else {
                                        echo "-";
                                    } ?>
                                </td>
                                <td><?= $o["pattern_no"] ?></td>
                                <td><?= $o["narabe_pic"] ?></td>
                                <td><?php SmallForm("number", $o, "collect_pic", $id) ?></td>
                                <td><?= $o["grp"] ?></td>
                            <?php elseif ($tableName != $N2_SAISANKA) : ?>
                                <td><?= $o["setter_pilling"] ?></td>
                            <?php endif ?>
                            <?php if ($tableName != $MRS) : ?>
                                <td><?= $o["chip_layer"] ?></td>
                                <td><?= $o["input_dt"] ?></td>
                                <td>
                                    <?php if ($o["output_dt"] == '-') : ?>
                                        <?php Working() ?>
                                    <?php else : ?>
                                        <?= $o["output_dt"] ?>
                                    <?php endif ?>
                                </td>
                                <td class="small"><?= $o["narabe_quantity"] ?></td>
                                <td class="small"><?= $o["narabe_pic"] ?></td>
                                <td class="small"><?php SmallForm("number", $o, "collect_pic", $id) ?></td>
                            <?php endif ?>
                            <?php if ($tableName == $N2_SAISANKA) : ?>
                                <?php LoadCheckBox("o2_free", $o); ?>
                            <?php elseif ($tableName != $MRS) : ?>
                                <?php LoadCheckBox("confirmation", $o); ?>
                            <?php endif ?>
                            <?php EditDelBtn($o['id']); ?>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
            </table>
        <?php else : ?>
            <p class="noRecord">No record found</p>
        <?php endif ?>
        <form action="<?= $url ?>" method="post" id="indexForm">
            <input type="hidden" id="update_small" name="update_small">
            <input type="hidden" id="delete_document" name="delete_document">
            <input type="hidden" id="checkBox" name="checkBox">
        </form>
        <form action="<?= $url ?>" method="get" id="processTableForm">
            <input type="hidden" id="process" name="process" value="<?= $process ?>">
            <input type="hidden" id="tableName" name="tableName">
        </form>
    </div>
    <script type="text/javascript" src="js/script.js"></script>
    <script>
        IndexLoad('<?= $tableName ?>', false);
        SaveLoadScroll();
    </script>
</body>

</html> -->