<?php
if($state == "start" && PageAt("item")) {
    $picId = LoadFieldStr("picId");
    if ($picId === "6845")
        $_SESSION["PIC"] = true;
    else if(isset($_REQUEST["picId"])) {
        $_SESSION["PIC"] = false;
        PrintScript("alert('Wrong PIC ID')");
        PrintScript("location.href = 'index.php'");
    }
}
?>
<?php if($state == "end" && PageAt("item")) : ?>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="generalForm">
        <input type="hidden" id="picId" name="picId">
    </form>

    <?php if (!PICverified()) : ?>
        <script>ReqPICid();</script>
    <?php endif ?>
<?php endif ?>