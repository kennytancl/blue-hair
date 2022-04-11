<?php include 'search.php'; ?>
<div class="back-container">
    <a href="index.php">
        <button class="top blue back"><img src="img/back.png"></button>
    </a>
</div>
<div class="add-container">
    <a href="<?= GetJumpPage("recordHead", "insert", '&addEdit=add') ?>" id="insertAnchor">
        <button class="top blue add">+</button>
    </a>
</div>
<h1><span id="tableButtonName"><?= $tables[$tableName]->button ?></span> <?= strtoupper($process) ?> 
    CHECK SHEET</h1>
<?php if (!in_array($tableName, $negativeNav)) : ?>
    <nav>
        <?php foreach ($tables as $name => $value) : ?>
            <?php if (!in_array($name, $negativeNav)) : 
                $isTable = $name == $tableName; 
                if(!$isTable) : ?>
                <a class="ancBtn" href="<?= GetJumpPage("recordHead.php", "record.html", "", ["tableName"])."&tableName=".$name ?>">
                <?php endif ?>
                    <button id="nav<?= $name ?>" class="navBtn <?php if($isTable) echo "active"; ?>" 
                        <?php if($isTable) echo "disabled"; ?>>
                        <?= $value->button ?>
                    </button>
                <?php if(!$isTable) : ?>
                    </a>
                <?php endif ?>
            <?php endif ?>
        <?php endforeach ?>
    </nav>
<?php endif ?>

<form id="search">
<table>
    <tr>
        <td rowspan="2">
            <label>Furnace No.</label><br>
            <button class="blue" type="button" onclick="ChangeSearch('-', 'searchFurnaceNo')"><</button>
            <?php SelectField("searchFurnaceNo",  $searchFurnaceNo, $furnaceNos); ?>
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
            <button class="searchBtn downloadImgBtn green" type="button" onclick="DownloadRecord()"><img src="img/download.png"></button>
            <button class="searchBtn searchImgBtn green" type="button" onclick="SearchRecord()"><img src="img/search.png"></button>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <button class="searchBtn green" type="button" onclick="SearchRecord('today')">TODAY</button>
            <button class="searchBtn green" type="button" onclick="SearchRecord('thisMonth')">THIS MONTH</button>
        </td>
    </tr>
</table>
</form>