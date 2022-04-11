<?php session_start(); ?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    include 'inclu.php';
    $state = 'start'; include 'verifyPIC.php';

    $submission = LoadFieldStr('submission');
    if ($submission && PICverified()) {
        LoadPY("CRD_item", "CREATE", [$submission]);
        header("Location: itemAll.php");
    }
    ?>
    <?php if (PICverified()) : ?>
        <div class="insert">
            <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="recordForm" onsubmit="SubmitItemNew()">
                <table>
                    <tr>
                        <th colspan="2">
                            NEW ITEM <span class="headAddEdit">(ADD)</span>
                        </th>
                    </tr>
                    <tr>
                        <th>Item Name:</th>
                        <td><input type="text" id="itemName" name="itemName" required></td>
                    </tr>
                    <tr>
                        <th>Type:</th>
                        <td><input type="text" id="type" name="type" required></td>
                    </tr>
                    <tr>
                        <th>Item Type:</th>
                        <td><input type="text" id="itemType" name="itemType" required></td>
                    </tr>
                    <tr>
                        <th>Item Group:</th>
                        <td><input type="text" id="itemGroup" name="itemGroup" required></td>
                    </tr>
                    <tr>
                        <th>Item Lot:</th>
                        <td><input type="text" id="itemLot" name="itemLot" required></td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <input type="submit" class="save" value="SAVE">
                            <input type="reset" class="reset" value="RESET" onclick="ResetAll()">
                            <a href="itemAll.php">
                                <input type="button" class="back" value="BACK">
                            </a>
                        </td>
                    </tr>
                </table>
                <input type="hidden" id="submission" name="submission">
            </form>
        </div>
    <?php endif ?>
    <script type="text/javascript" src="script.js"></script>
    <?php $state = 'end'; include 'verifyPIC.php'; ?>
</body>

</html>