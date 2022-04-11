<?php session_start();
$seikei_tables = array("seikei_tms,4", "seikei_zy3,4", "seikei_zy3,6");
$seikei_json_caches = array("seikei_tms_4", "seikei_zy3_4", "seikei_zy3_6");
$url = $_SERVER['REQUEST_URI'];
$backPure = "history.go(-1)";
$backHref = "javascript:" . $backPure;
$factory = GetUrlPara("factory");

function Back()
{
    global $backPure;
    PrintScript($backPure);
}

function str_contains($haystack, $needle)
{
    return '' === $needle || false !== strpos($haystack, $needle);
}

function LoadPY($filename, $status = "None", $data = "None")
{
    $command = escapeshellcmd("py\\$filename.py $status $data");
    return shell_exec($command);
}

function GetDecStr($json, $field)
{
    if ($json == null) return "";
    return json_decode($json)->{$field};
}

function GetDecArr($json, $field): array
{
    if ($json == null) return array();
    return json_decode($json)->{$field};
}

function LoadFieldArr($nameStr): array
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
    echo '<script>' . $data . '</script>';
}

function GetUrlPara($name)
{
    global $url;
    $url_components = parse_url($url);
    if (!isset($url_components['query'])) return "";
    parse_str($url_components['query'], $params);
    if (!isset($params[$name])) return "";
    return $params[$name];
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

function GetDataByInd($i)
{
    global $seikei_datas, $seikei_tables;
    return $seikei_datas[$seikei_tables[$i]];
}
?>