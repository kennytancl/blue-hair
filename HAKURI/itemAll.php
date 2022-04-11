<?php session_start(); ?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    include 'inclu.php';
    $state = 'start';
    include 'verifyPIC.php';

    $firstLoad = false;
    if (isset($_SESSION["fields"]))
        $fields = $_SESSION["fields"];
    else {
        $fields = array(
            new Field("item_name", false),
            new Field("type"),
            new Field("item_type"),
            new Field("item_grp"),
            new Field("item_lot")
        );
        $firstLoad = true;
    }

    $delete = LoadFieldStr("delete");
    if ($delete) {
        $state = 'start'; include 'verifyPIC.php';
        LoadPY("CRD_item", "DELETE", [$delete]);
    }

    $sort = LoadFieldStr("sort");
    $isSaveSortSession = !isset($_SESSION["sort"]) ||
        ($sort && $sort != $_SESSION["sort"]);
    if ($isSaveSortSession)
        $_SESSION["sort"] = $sort;
    else if (isset($_SESSION["sort"]))
        $sort = $_SESSION["sort"];

    $isASC = 'true';
    foreach ($fields as $f) {
        if ($f->name == $sort) {
            if(LoadFieldStr("sort") || $firstLoad)
                $f->ToggleSort();
            $isASC = $f->sortASC ? 'true' : 'false';
            break;
        }
    }
    $_SESSION["fields"] = $fields;

    $output = LoadPY("CRD_item", "READ_" . $isASC, [$sort]);
    $output = json_decode($output, true);
    $output = empty($output) ? array() : $output;

    class Field
    {
        public $name;
        public $sortASC;
        public function __construct($name, $sortASC = true)
        {
            $this->name = $name;
            $this->sortASC = $sortASC;
        }
        public function ToggleSort()
        {
            $this->sortASC = !!!$this->sortASC;
        }
    }
    ?>
    <?php if (PICverified()) : ?>
        <div class="index itemAll">
            <button class="top new" onclick="location.href = 'itemNew.php'">NEW</button>
            <button class="top back" onclick="location.href = 'index.php'">BACK</button>
            <h1>ALL ITEMS</h1>
            <?php if ($output) : ?>
                <table>
                    <tr>
                        <th>Item Name</th>
                        <th>Type</th>
                        <th>Item Type</th>
                        <th>Item Group</th>
                        <th>Item Lot</th>
                        <th>Button</th>
                    </tr>
                    <tr>
                        <?php foreach ($fields as $f) : ?>
                            <td>
                                <button class="sort" onclick="AssignValueSubmit('sort', '<?= $f->name ?>')">
                                    SORT</button>
                            </td>
                        <?php endforeach ?>
                        <td></td>
                    </tr>
                    <?php foreach ($output as $o) : ?>
                        <tr class="unique<?= $o["isUnique"] ?>">
                            <td><?= $o["item_name"] ?></td>
                            <td><?= $o["type"] ?></td>
                            <td><?= $o["item_type"] ?></td>
                            <td><?= $o["item_grp"] ?></td>
                            <td><?= $o["item_lot"] ?></td>
                            <td>
                                <button class="delete" onclick="DeleteItem(<?= $o['rcd_id'] ?>)">DEL</button>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </table>
                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="indexForm">
                    <input type="hidden" id="delete" name="delete">
                    <input type="hidden" id="sort" name="sort">
                </form>
            <?php else : ?>
                <p class="noRecord">No record found</p>
            <?php endif ?>
        </div>
    <?php endif ?>
    <script type="text/javascript" src="script.js"></script>
    <script>
        SaveLoadScroll();
    </script>
    <?php $state = 'end';
    include 'verifyPIC.php'; ?>
</body>

</html>