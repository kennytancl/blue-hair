<?php if (!isset($isRowOnly)) $isRowOnly = false;
    if (!$isRowOnly) : include 'search.php'; ?>
    <div class="lotNoFilter">
        <label>Lot No.:</label>
        <input type="text" id="lotNoFilter" name="lotNoFilter" oninput="FilterLotNo(this)" autocomplete="off">
        <button class="blue" type="button" onclick="ClearLotNo()">CLEAR</button>
    </div>
    <h3>
        Furnace No: <?= $searchFurnaceNo ?> 
        (Date: <?php if($searchDay != "X") echo $searchDay.' '; ?><?= $searchMonth ?> <?= $searchYear ?>)
    </h3>
<?php endif ?>
<?php if ($mainOutput) : ?>
    <?php if (!$isRowOnly) : ?><table id="mainTable"><?php endif ?>
        <?php if ($tableName == $QS_FURNACE) : ?>
            <?php if (!$isRowOnly) : ?>
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
                    <th class="small">Collect.g Tray</th>
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
                    <th>Item Name</th>
                    <th>Lot No</th>
                    <th>Lot Qty.</th>
                    <th class="small">Item</th>
                    <th class="small">Silt</th>
                </tr>
            <?php endif ?>
            <?php $docInd = 1; foreach ($mainOutput as $o) : $id = $o["id"]; ?>
                <tr id="data_row_<?= $id ?>" class="data_row">
                    <th colspan="100">
                        Inspected By: <?= $o["inspection_datetime"] ?>&emsp;
                        <?php EditDelBtn($o['id'], $o['lot_no'], false); ?>
                    </th>
                </tr>
                <tr>
                    <td id="doc_ind_<?= $id ?>" class="doc_ind"><?= $docInd; $docInd++; ?></td>
                    <?= GetItemHover($o["item_type"]) ?>
                    <td class="lot_no"><?= $o["lot_no"] ?></td>
                    <td><?= $o["lot_quantity"] ?></td>
                    <td><?= $o["item"] ?></td>
                    <td><?= $o["slit"] ?></td>
                    <?php LoadTick("slit_nfmd", $o); ?>
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
                    <?php LoadTick("tube_nfmd", $o); ?>
                    <?php LoadTick("feeder_nfmd", $o); ?>
                    <?php LoadTick("tray_nfmd", $o); ?>
                    <td><?= $o["done_by"] ?></td>
                    <td><?= $o["checked_by"] ?></td>
                    <td><?= $o["verified_by"] ?></td>
                    <td><?= $o["remark"] ?></td>
                </tr>
            <?php endforeach ?>
        <?php else : ?>
            <?php if (!$isRowOnly) : ?>
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
                        <th rowspan="3">Stick Chip<br>(%)</th>
                        <th rowspan="3">Pattern No</th>
                        <th rowspan="3">Narabe<br>PIC</th>
                        <th rowspan="3">Collect.g<br>PIC</th>
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
                        <th>Collect.g</th>
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
            <?php endif ?>
            <?php $docInd = 1; foreach ($mainOutput as $o) : 
                if(isset($o["collect_pic"])) $id = $o["id"]; ?>
                <tr id="data_row_<?= $id ?>" class="data_row">
                    <td id="doc_ind_<?= $id ?>" class="doc_ind"><?= $docInd; $docInd++; ?></td>
                    <?php if ($tableName == $MRS) : ?>
                        <td><?= $o["date_txt"] ?></td>
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
                    <?= GetItemHover($o["item_name"]) ?>
                    <?php if ($tableName == $MRS) : ?>
                        <td><?= $o["key_no"] ?></td>
                    <?php endif ?>
                    <td class="lot_no"><?= $o["lot_no"] ?></td>
                    <td><?= $o["lot_size_str"] ?></td>
                    <?php if ($tableName == $MRS) : ?>
                        <td class="small"><?= $o["quantity_plan"] ?></td>
                        <!-- <td class="small">?= $o["quantity_actual"] ?></td> -->
                        <td class="small"><?php SmallForm("number", $o, "quantity_actual", $id) ?></td>
                        <td class="small"><?php SmallForm("number", $o, "quantity_stick", $id) ?></td>
                        <td><?= $o["stick_rate"] ?></td>
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
                        <?php LoadTick("o2_free", $o); ?>
                    <?php elseif ($tableName != $MRS) : ?>
                        <?php LoadTick("confirmation", $o); ?>
                    <?php endif ?>
                    <?php EditDelBtn($o['id'], $o['lot_no']); ?>
                </tr>
            <?php endforeach ?>
        <?php endif ?>
    <?php if (!$isRowOnly) : ?></table><?php endif; ?>
<?php else : ?>
    <p class="noRecord">No record found</p>
<?php endif ?>