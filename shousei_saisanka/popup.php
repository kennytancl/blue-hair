<!-- https://github.com/solodev/onclick-form-popup -->

<!-- <link rel="stylesheet" href="style/bootstrap.min.css"> -->
<?php 
include 'inclu.php';
$title = LoadPostFieldStr("title"); 
?> 
<link rel="stylesheet" href="style/popup.css">
<link rel="stylesheet" href="style/style.css">

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/popup.js"></script>

<div id="popup-container">
    <div id="popup-window">
        <div class="modal-content">
            <div class="row text-center">
                <p>Enter <?= $title ?> password</p>
            </div>
            <form id="footer-form" onsubmit="return SubmitPopUp('<?= $title ?>', $('#pw').val())">
                <div class="row">
                    <div class="col-md">
                        <input type="password" name="pw" id="pw" class="form-control" placeholder="***" required>
                    </div>
                </div>
                <button type="button" class="cancel orange back" onclick="HidePopUp(); ClearLotNo();">CANCEL</button>
                <button type="submit" class="ok blue back">OK</button>
            </form>
        </div>
    </div>
</div>
<script>DisplayPopUp();</script>