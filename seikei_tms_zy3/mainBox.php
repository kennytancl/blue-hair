<?php
include "inclu.php";
$seikei_datas = array();
$i = 0;
foreach ($seikei_tables as $st) {
    $output = LoadPY("read", "normal", $st);
    // echo $output;
    $seikei_datas[$st] = DecodeArrOutput($output);
    // $f = fopen("cache/map_$seikei_json_caches[$i].json", "r");
    // $seikei_datas[$st] = DecodeArrOutput(fgets($f));
    // fclose($f);
    // $i++;
}

function WorkRateBox($status, $desc)
{
    echo '
    <div class="workRateBox ' . $status . '">
        <h3>No.</h3>
        <div class="level"><div class="wave"></div></div>
    </div>
    <p>' . $desc . '</p>';
}

function MainBox($seikei_table, $data)
{
    echo '<div class="mainBox ' . $seikei_table . '">';
    foreach ($data as $o) {
        $idTable = $o['id'] . $seikei_table;
        echo
        '<div class="workRateBox wrSlide wrSlide' . $idTable . ' ' . $o["className"] . '" 
            onmouseover="DisplayDetail(\'' . $idTable . '\', true)"
            onmouseout="HideDetail(true)">
            <h3>' . $o["gouki"] . '</h3>
            <div class="level-box">
                <div class="level-box-inner">
                    <div class="level"><div class="wave"></div></div>
                    <div class="level-back ' . $o["wrLT95"] . '">
                        <span>' . $o["workRateNoDec"] . '%</span>
                    </div>
                </div>
            </div>
            <div class="event">
                <img src="img/running.png">
            </div>
        </div>
        <div class="workRateDetail displayNone" id="' . $idTable . '">
            <h3>' . $o["gouki"] . '</h3>
            <p>Data date: ' . $o["datadate"] . '</p>
            <p>Lot No: ' . $o["lotno"] . '</p>
            <p>Item: ' . $o["item"] . '</p>
            <p>Work Rate: ' . $o["workRate"] . '%</p>
        </div>';
    }
    echo '</div>';
}
?>

<div class="legend">
    <div class="levelBox">
        <?= WorkRateBox("noData", "No Data") ?>
        <?= WorkRateBox("noRun", "Not Running /<br>Work Rate<br>= 0%") ?>
        <?= WorkRateBox("wr20", "Work Rate<br>0% ~ 20%") ?>
        <?= WorkRateBox("wr40", "Work Rate<br>20% ~ 40%") ?>
        <?= WorkRateBox("wr60", "Work Rate<br>40% ~ 60%") ?>
        <?= WorkRateBox("wr80", "Work Rate<br>60% ~ 80%") ?>
        <?= WorkRateBox("wr100", "Work Rate<br>80% ~ 100%") ?>
        <?= WorkRateBox("wrMax", "Work Rate<br>= 100%") ?>
    </div>
    <div id="legendDetail">
        <div class="workRateDetail visibleHide" id="wrdPrototype">
            <h3>Head</h3>
            <p>Data date: </p>
            <p>Lot No: </p>
            <p>Item: </p>
            <p>Work Rate: </p>
        </div>
    </div>
    <table class="autoDisplay">
        <tr>
            <td>
                <label class="switch">
                    <input type="checkbox" id="autoDisplayCx" onchange="ChangeAutoSlide(this.checked)">
                    <span class="slider round"></span>
                </label>
            </td>
            <td>Auto Display</td>
        </tr>
    </table>
    <p>Reloaded by: <span id="reloadTime"></span></p>
</div>

<table class="mainTable">
    <tr>
        <td></td>
        <td colspan="2" class="seikei_tms">
            <h1>Seikei TMS (F4)</h1>
        </td>
    </tr>
    <tr>
        <td></td>
        <td colspan="2" class="seikei_tms"><?= MainBox("seikei_tms", GetDataByInd(0)) ?></td>
    </tr>
    <tr>
        <td></td>
        <td class="seikei_zy3_4">
            <h1>Seikei ZY (F4)</h1>
        </td>
        <td class="seikei_zy3_6">
            <h1>Seikei ZY (F6)</h1>
        </td>
    </tr>
    <tr>
        <td></td>
        <td class="seikei_zy3_4"><?= MainBox("seikei_zy3_4", GetDataByInd(1)) ?></td>
        <td class="seikei_zy3_6"><?= MainBox("seikei_zy3_6", GetDataByInd(2)) ?></td>
    </tr>
</table>
<!-- <h1 class="seikei_tms">Seikei TMS</h1>
<h1 class="seikei_zy3">Seikei ZY</h1> -->
<!-- onclick="location.href = 'popUpGraph.html'"> -->
<!-- <div class="wr"><p>< $o["workRateNoDec"]</p></div> -->