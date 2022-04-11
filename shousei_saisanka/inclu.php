
<?php session_start();
$url = $_SERVER['REQUEST_URI'];
$backPure = "history.go(-1)";
$backHref = "javascript:".$backPure;
$tableName = GetUrlPara("tableName");
$process = GetUrlPara("process");

function Back()
{
    global $backPure; PrintScript($backPure);
}

function Working()
{
    echo '<img class="working" src="img/working.png">';
}

function str_contains($haystack, $needle)
{
    return '' === $needle || false !== strpos($haystack, $needle);
}

function LoadPY($filename, $action, $arg = ["None"])
{
    $action = $action == "" ? "None": $action;
    $str = json_encode($arg);
    $command = escapeshellcmd("py\\$filename.py $action $str");
    return shell_exec($command);
}

function GetDecStr($json, $field)
{
    if($json == null) return "";
    return json_decode($json)->{$field};
}

function GetDecArr($json, $field)
{
    if($json == null) return array();
    return json_decode($json)->{$field};
}

function LoadFieldArr($nameStr)
{
    $arr = array();
    for ($i = 1; $i <= 4; $i++) {
        $nameStrComp = "{$nameStr}{$i}";
        if (!empty($_REQUEST[$nameStrComp])) {
            $temp = $_REQUEST[$nameStrComp];
            array_push($arr, $temp);
        }
    }
    return $arr;
}

function LoadFieldStr($nameStr)
{
    if (empty($_REQUEST[$nameStr])) return "";
    else return $_REQUEST[$nameStr];
}

function LoadPostFieldStr($nameStr)
{
    if (empty($_POST[$nameStr])) return "";
    else return $_POST[$nameStr];
}

function PrintScript($data)
{
    echo '<script>'.$data.'</script>';
}

// function LoadCheckBox($field, $d)
// {
//     echo '<td class="small"><input type="checkbox" id="'.$field.$d["id"].
//     '" name="'.$field.$d["id"].'"';
//     if($d[$field]) echo "checked"; 
//     echo ' onchange="ChangesSubmit(\'check_box\','.$d["id"].',\''.$field.',id,None,'.$d["id"].'\')"></td>';
// }

function LoadTick($field, $d)
{
    echo '<td class="small">';
    if($d[$field]) echo "<img class='tick' src='img/tick.png'>"; 
    echo '</td>';
}

function DecodeArrOutput($output)
{
    $output = json_decode($output, true);
    $output = empty($output) ? array() : $output;
    return $output;
}

function PageAt($data)
{
    return str_contains($_SERVER['REQUEST_URI'], $data);
}

function GetJumpPage($before, $after, $additional = "", $removeParams = [])
{
    global $url;
    $result = str_replace($before, $after, $url).$additional;
    foreach($removeParams as $rs) {
        $result = preg_replace('/(&|\?)'.preg_quote($rs).'=[^&]*$/', '', $result);
        $result = preg_replace('/(&|\?)'.preg_quote($rs).'=[^&]*&/', '$1', $result);
    }
    return $result;
}

function GetUrlPara($name)
{
    global $url;
    $url_components = parse_url($url);
    if(!isset($url_components['query'])) return "";
    parse_str($url_components['query'], $params);
    if(!isset($params[$name])) return "";
    return $params[$name];
}

function EditDelBtn($id, $lot_no, $hasTD = true)
{
    if (PageAt("recordTable")) $pageBefore = "recordTable";
    else if (PageAt("changes")) $pageBefore = "changes";
    if ($hasTD) echo "<td>";
    echo '<a href="'.GetJumpPage($pageBefore , "insert", '&addEdit=edit&id='.$id).'">
            <button class="edit">EDIT</button>
        </a>';
    if ($hasTD) echo "<br>";
    else echo "&emsp;";
    echo '<button class="delete" onclick="ChangesSubmit(\'delete_document\','.$id.', \''.$lot_no.'\')">
            DEL</button>';
    if ($hasTD) echo '</td>';
}

function SelectField($field, $selected, $options, $onchange = "")
{
    $idName = 'id="'.$field.'" name="'.$field.'" ';
    echo '<select '.$idName;
    if ($onchange) echo 'onchange="'.$onchange.'" ';
    echo '>';
    foreach ($options as $o) {
        echo '<option value="'.$o.'" ';
        if ($selected == $o) echo 'selected';
        echo '>'.$o.'</option>';
    }
    echo '</select>';
}

function SmallForm($type, $o, $field, $id)
{
    $fieldId = $field.'_'.$id;
    $fieldIdComma = "'".$field."',".$id;
    if (!$o[$field] && $o[$field] !== 0) {
        echo '<form id="form_'.$fieldId.'" onsubmit="return false;">
            <input type="'.$type.'" name="'.$field.'" id="'.$fieldId.'" class="'.$field.'" 
                autocomplete="off" min="0" onkeydown="ChangesSubmitIfEnter(event,'.$fieldIdComma.')">';
        if ($field == "collect_pic")
            echo '<br><button type="button" class="save" 
                onclick="ChangesSubmit('.$fieldIdComma.')">SAVE</button>';
        echo '</form>';
    } else echo $o[$field];
}

function GetItemHover($value){
    return '
    <td class="item_name" onmouseover="SearchPDN(\'get_pdn\', \''.$value.'\', this)">
        <div class="item_pdn">
            <div class="lds-ring">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
        <div>'.$value.'</div>
    </td>';
}

function SearchSession($str, $default)
{
    $var = LoadFieldStr($str);
    if ($var) $_SESSION[$str] = $var;
    else $var = $default;
    if (isset($_SESSION[$str])) $var = $_SESSION[$str];
    return $var;
}

class TBL {
    public $name;
    public $button;
    public $title;

    public function __construct($name, $button, $title) {
        $this->name = $name;
        $this->button = $button;
        $this->title = $title;
    }
}
$emp = "<sub> </sub>";
$o2 = "O<sub>2</sub>";
$n2 = "N<sub>2</sub>";
$O2_SAISANKA = "o2_saisanka";
$N2_SAISANKA = "n2_saisanka";
$MRS = "mrs";
$QS_FURNACE = "qs_furnace";
$tbls = array(
    new TBL("mrs", "MRS".$emp, "MRS SHOUSEI"),
    new TBL("n2_saisanka", $n2, $n2." SAISANKA (CE / RMW)"),
    new TBL("o2_saisanka", $o2." NORMAL", $o2." NORMAL SAISANKA (CE / RMW)"),
    new TBL("o2_fast_cooling", $o2." FAST COOLING", $o2." FAST COOLING (CE & RMW)"),
    new TBL("o2_low_temperature", $o2." LOW-LOW TEMP.", $o2." LOW-LOW TEMP. (CE, RMW & RMV)"),
    new TBL("qh_furnace", "QH".$emp."FURNACE", "QH FURNACE (CE, RMW & RMV)"),
    new TBL("qs_furnace", "QSS".$emp."FURNACE", "QSS FURNACE SAISANKA BEFORE LOT INSPECTION")
);

$tables = [];
foreach($tbls as $t) $tables[$t->name] = $t;
$negativeNav = [$MRS, $N2_SAISANKA];

?>