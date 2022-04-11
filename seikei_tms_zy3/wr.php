<?php
include "inclu.php";
$factory = GetUrlPara("factory");
switch ($factory) {
    case 4: // F4
        unset($seikei_tables[2]);
        break;
    case 6: // F6
        unset($seikei_tables[0]);
        unset($seikei_tables[1]);
        break;
}
$seikei_datas = array();
foreach ($seikei_tables as $st) {
    $output = LoadPY("read", "wr", $st);
    // echo $output;
    $output = DecodeArrOutput($output)[0];
    // print_r($seikei_tms);
    $datadate = $output["datadate"];
    $seikei_datas[$st] = $output["machines"];
}
switch ($factory) {
    case 4: // F4
        $left = GetDataByInd(1);
        $right = GetDataByInd(0);
        break;
    case 6: // F6
        $F6 = GetDataByInd(2);
        $len = count($F6);
        $left = array_slice($F6, 0, $len / 2);
        $right = array_slice($F6, $len / 2);
        break;
}

$target = 95;
function GetIsRed($value)
{
    if ($value == "-") return;
    global $target;
    if ($value < $target) echo "red";
}
?>

<p class="wr datadate"><?= $datadate ?></p>
<p class="wr day">DAY</p>
<a id="headLink" target="_blank" title="Click to switch to other factory">
    <h1 class="wr">Factory <?= $factory ?></h1>
</a>
<div class="wrContainer">
    <?php foreach (array("left" => $left, "right" => $right) as
        $directionTxt => $direction) : ?>
        <table class="wr">
            <tr>
                <td class="selectMachine" colspan="100">
                    <select class="selectMachine" name="machine" id="machine<?= $directionTxt ?>" onchange="ChangeMachine('<?= $directionTxt ?>', this)">
                        <option value="all">- All -</option>
                        <?php foreach ($direction as $machine => $data) : ?>
                            <option value="<?= $data["macNum"] ?>"><?= $data["macNum"] ?></option>
                        <?php endforeach ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th class="machineNo">M.N.</th>
                <th class="target">Target</th>
                <th class="timeBlock">8 - 10</th>
                <th class="timeBlock">10 - 12</th>
                <th class="timeBlock">12 - 2</th>
                <th class="timeBlock">2 - 4</th>
                <th class="timeBlock">4 - 6</th>
                <th class="timeBlock">6 - 8</th>
            </tr>
            <?php foreach ($direction as $machine => $data) :
                $rowId = $directionTxt . $data["macNum"] ?>
                <tr class="<?= $directionTxt ?> <?= $rowId ?>">
                    <td class="machineNo" rowspan="2"><?= $data["macNum"] ?></td>
                    <td class="target" rowspan="2"><?= $target ?></td>
                    <?php foreach (range(8, 19, 2) as $t) : $t = (string)$t; ?>
                        <td class="timeBlock <?= GetIsRed($data[$t]) ?>"><?= $data[$t] ?></td>
                    <?php endforeach ?>
                </tr>
                <tr class="<?= $directionTxt ?> <?= $rowId ?>">
                    <?php foreach (range(20, 31, 2) as $t) : $t = (string)$t; ?>
                        <td class="timeBlock <?= GetIsRed($data[$t]) ?>"><?= $data[$t] ?></td>
                    <?php endforeach ?>
                </tr>
            <?php endforeach ?>
        </table>
    <?php endforeach ?>
</div>