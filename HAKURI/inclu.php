
<?php
function str_contains($haystack, $needle)
{
    return '' === $needle || false !== strpos($haystack, $needle);
}

function LoadPY($filename, $action = "", $arg = ["None"])
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

function PrintScript($data)
{
    echo '<script>' . $data . '</script>';
}

function PICverified()
{
    return isset($_SESSION["PIC"]) && $_SESSION["PIC"];
}

function PageAt($data)
{
    return str_contains($_SERVER['REQUEST_URI'], $data);
}

if(!PageAt("item")) $_SESSION["PIC"] = false;

?>