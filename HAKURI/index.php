<?php session_start(); ?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    include 'inclu.php';
    $addEdit = LoadFieldStr("addEdit");
    $batchId = LoadFieldStr("batchId");
    if ($addEdit) {
        $_SESSION["addEdit"] = $addEdit;
        $_SESSION["batchId"] = $batchId;
        $_SESSION["firstLoad"] = true;
        header("Location: insert.php");
    }

    $endPic = LoadFieldStr("endPic");
    if ($endPic) echo LoadPY("CRUD", "UPDATE_end_pic", [$endPic]);

    $delete = LoadFieldStr("delete");
    if ($delete) LoadPY("CRUD", "DELETE", [$delete]);

    $searchStartBy = LoadFieldStr("searchStartBy");
    if ($searchStartBy) $_SESSION["searchStartBy"] = $searchStartBy;
    else $searchStartBy = trim(preg_replace('/\s\s+/', ' ', LoadPY("DATE")));
    if (isset($_SESSION["searchStartBy"]) && 
        $_SESSION["searchStartBy"] != $searchStartBy) {
        $searchStartBy = $_SESSION["searchStartBy"];
    }

    $export = LoadFieldStr("export");
    if ($export) {
        LoadPY("export", $searchStartBy . "," . $export);
        $_SESSION["exportMac"] = $export;
        $_SESSION["exportDate"] = $searchStartBy;
        header("Location: export.php");
    }
    $output = LoadPY("CRUD", "READ_start_dt", [$searchStartBy]);
    $output = json_decode($output, true);
    $output = empty($output) ? array() : $output;
    ?>
    <div class="index indexHome">
        <button class="top add" onclick="AddEditSubmit('add')">ADD</button>
        <button class="top itemAll" onclick="location.href = 'itemAll.php';">ALL ITEMS</button>
        <button class="top export4" onclick="AssignValueSubmit('export', 4, 'exportForm')">
            Export M.4</button>
        <button class="top export5" onclick="AssignValueSubmit('export', 5, 'exportForm')">
            Export M.5</button>
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="search">
            <label>Search (Start By):</label><br>
            <input type="date" id="searchStartBy" name="searchStartBy" value="<?= $searchStartBy ?>" onchange="SubmitSearch()">
        </form>
        <h1>HAKURI RECORD</h1>
        <?php if ($output) : ?>
            <table>
                <tr>
                    <th>Mac.</th>
                    <th>Tank</th>
                    <th>Item Name</th>
                    <th>Lot No.</th>
                    <th>Grinding</th>
                    <th>RPM</th>
                    <th>Start By</th>
                    <th>Finish By</th>
                    <th>PIC (Start)</th>
                    <th>PIC (End)</th>
                    <th>Button</th>
                </tr>
                <?php $outputInd = 0;
                foreach ($output as $o) : ?>
                    <?php $itemsInd = 0;
                    foreach ($o["items"] as $i) :
                        $isNewBatch = $itemsInd == 0;
                        $rs = 'rowspan="' . count($o["items"]) . '"';
                        $batch_id = $o['rec_batch_id']; ?>
                        <tr class="<?php if ($outputInd % 2 != 0) echo 'even'; ?>">
                            <?php if ($isNewBatch) : ?>
                                <td <?= $rs ?>><?= $o["machine_no"] ?></td>
                            <?php endif ?>
                            <td><?= $i["tank_no"] ?></td>
                            <td><?= $i["item_name"] ?></td>
                            <td><?= $i["lot_no"] ?></td>
                            <?php if ($isNewBatch) : ?>
                                <td <?= $rs ?>><?= $o["grinding_mins"] ?> mins</td>
                                <td <?= $rs ?>><?= $o["rpm"] ?></td>
                                <td <?= $rs ?> class="dt"><?= $o["start_dt"] ?></td>
                                <td <?= $rs ?> class="dt">
                                    <?php if (empty($o["finish_dt"])) : ?>
                                        <img class="working" src="img/working.png">
                                    <?php else : ?>
                                        <?= $o["finish_dt"] ?>
                                    <?php endif ?>
                                </td>
                                <td <?= $rs ?>><?= $o["start_pic"] ?></td>
                                <td <?= $rs ?>>
                                    <?php if (empty($o["end_pic"])) : ?>
                                        <form id="form_end_pic_<?= $batch_id ?>" method="post" 
                                        onsubmit="IndexSubmit('endPic',<?= $batch_id ?>)">
                                            <input type="text" id="end_pic_<?= $batch_id ?>" class="endPic" 
                                            required autocomplete="off"><br>
                                            <input type="hidden" id="hid_end_pic_<?= $batch_id ?>" name="endPic">
                                            <button class="save">SAVE</button>
                                        </form>
                                    <?php else : ?>
                                        <?= $o["end_pic"] ?>
                                    <?php endif ?>
                                </td>
                                <td <?= $rs ?>>
                                    <button class="edit" onclick="AddEditSubmit('edit',<?= $batch_id ?>)">EDIT</button><br>
                                    <button class="delete" onclick="IndexSubmit('delete',<?= $batch_id ?>)">DEL</button>
                                </td>
                            <?php endif ?>
                        </tr>
                        <?php $itemsInd++; ?>
                    <?php endforeach ?>
                    <?php if ($o["items"]) $outputInd++; ?>
                <?php endforeach ?>
            </table>
        <?php else : ?>
            <p class="noRecord">No record found</p>
        <?php endif ?>
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="indexForm">
            <input type="hidden" id="delete" name="delete">
        </form>
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="addEditForm">
            <input type="hidden" id="addEdit" name="addEdit">
            <input type="hidden" id="batchId" name="batchId">
        </form>
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="exportForm">
            <input type="hidden" id="export" name="export">
        </form>
    </div>
    <script type="text/javascript" src="script.js"></script>
    <script>
        SaveLoadScroll();
    </script>
</body>

</html>