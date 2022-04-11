<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style/style.css">
    <script type="text/javascript" src="js/script.js"></script>
</head>

<body class="index">
    <?php
    include 'inclu.php';
    function SubContainer($pos, $process, $tableName, $button)
    {
        $mouseEffect = 'onmouseover="OverContainer('.$pos.')" 
            onmouseout="OutContainer('.$pos.')"';
        echo '
        <a href="record.html?process='.$process.'&tableName='.$tableName.'" '.$mouseEffect.'>
            <div class="sub-container">
                <img src="img/'.$process.'.png">
                <h2>'.strtoupper($process).'</h2>
                <button class="blue">'.$button.'</button>
            </div>
        </a>';
    }
    ?>
    <h1>Checksheet</h1>
    <div class="container">
        <?php SubContainer(0, "Shousei", "mrs", "MRS") ?>
        <?php SubContainer(1, "Saisanka", "n2_saisanka", $n2) ?>
        <?php SubContainer(2, "Saisanka", "o2_saisanka", $o2) ?>
    </div>
    <script type="text/javascript" src="js/script.js"></script>
    <script>
        SessSetItem("filterLotNo", "");
        function ArrayRemove(arr, value) {         
            return arr.filter(function(ele){ 
                return ele != value; 
            });
        }
        
        function OverContainer(pos)
        {
            indexes = [0, 1, 2];
            indexes = ArrayRemove(indexes, pos);
            for (let i = 0; i < indexes.length; i++)
                DocGetEClass("sub-container")[indexes[i]].style.transform = "scale(0.5)";
        }

        function OutContainer(pos)
        {
            for (let i = 0; i < 3; i++)
                DocGetEClass("sub-container")[i].style.transform = "scale(1)";
        }
    </script>
</body>

</html>
