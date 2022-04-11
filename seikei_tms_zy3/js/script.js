var DocGetEID = (id) => document.getElementById(id);
var DocGetEClass = (name) => document.getElementsByClassName(name);
var DocGetEIDVal = (id) => DocGetEID(id).value;
var DocGetEIDIHtml = (id) => DocGetEID(id).innerHTML;
var SessSetItem = (key, val) => sessionStorage.setItem(key, val);
var SessGetItem = (key) => sessionStorage.getItem(key);
var delay = (ms) => new Promise(res => setTimeout(res, ms));
var currUrl = new URL(window.location);
var tempUrl = new URL(window.location);
var GetUrlPara = (p) => currUrl.searchParams.get(p);

const slideInd = "slideInd";
const flip = "flip";
const slideSec = 2000;
const autoSlideShowSec = 5000;
// Refresh rate for mapping and work rate
const reloadSec = 180000; // 300000
var slideInterval = "";
var autoSlideShowInterval = "";

function GetChangeUrlPara(targetUrl, addField = "", addValue = "", removeParams = []) {
    tempUrl.searchParams.set(addField, addValue);
    let params = new URLSearchParams(tempUrl.search);
    removeParams.forEach(function Rem(item) {
        params.delete(item);
    });
    return targetUrl + "?" + params;
}

function FormatDate(date) {
    const months = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"];
    return date.getDate() + " " + months[date.getMonth()] + " " + date.getFullYear();
}

function FormatTime(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var sec = date.getSeconds();
    //var ampm = hours >= 12 ? 'PM' : 'AM';
    //hours = hours % 12;
    //hours = hours ? hours : 12;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    sec = sec < 10 ? '0' + sec : sec;
    return hours + ':' + minutes + ':' + sec;
}

function FormatJSONDate(date) {
    var month = date.getMonth() + 1;
    month = month < 10 ? '0' + month : month;
    var day = date.getDate() + 1;
    day = day < 10 ? '0' + day : day;
    return date.getFullYear() + "-" + month + "-" + day;
}

function FormatSpaceDateTime(date) {
    return FormatJSONDate(date) + " " + FormatTime(date);
}

function DisplayDetail(idTable, isMouseOver) {
    if (isMouseOver) {
        if (slideInterval) {
            RemoveSlideInterval();
        } HideDetail(isMouseOver);
    }
    DocGetEID("wrdPrototype").style.display = "none";
    DocGetEID("legendDetail").innerHTML += DocGetEID(idTable).innerHTML;
}

function HideDetail(isMouseOver) {
    DocGetEID("wrdPrototype").style.display = "block";
    DocGetEID("wrdPrototype").style.visibility = "hidden";
    DocGetEID("legendDetail").innerHTML = DocGetEID("wrdPrototype").outerHTML;
    if (isMouseOver) {
        DocGetEID("autoDisplayCx").checked = false;
        RemoveAllHov();
    }
}

function ReloadMain() {
    $.ajax({
        type: 'GET',
        url: "mainBox.php",
        success: function (response) {
            $('#index').html(response);
            DocGetEID("reloadTime").innerHTML = FormatSpaceDateTime(new Date());
            SetAutoDisplaySw();
        }
    });
}

function RemoveAllHov() {
    var wrSlide = DocGetEClass("wrSlide");
    for (let i = 0; i < wrSlide.length; i++) {
        wrSlide[i].classList.remove("hov");
    }
}

function InitSlideInd() {
    if (!SessGetItem(slideInd))
        SessSetItem(slideInd, 0);
}

function Dec1SlideInd() {
    InitSlideInd();
    var ind = parseInt(SessGetItem(slideInd));
    if (ind != 0)
        SessSetItem(slideInd, ind - 1);
}

function RemoveSlideInterval() {
    Dec1SlideInd();
    clearInterval(slideInterval);
    slideInterval = "";
    clearInterval(autoSlideShowInterval);
    autoSlideShowInterval = setInterval(StartAutoSlideShow, autoSlideShowSec);
}

function AutoSlideShow() {
    var wrSlide = DocGetEClass("wrSlide");
    if (!wrSlide) return;
    InitSlideInd();
    var ind = parseInt(SessGetItem(slideInd));

    if (ind == wrSlide.length) ind = 0;
    if (wrSlide[ind] == undefined) return;
    var cl = wrSlide[ind].classList;
    RemoveAllHov();
    cl.add("hov");

    for (let i = 0; i < cl.length; i++) {
        var idTable = cl[i].replace("wrSlide", "");
        if (cl[i].includes("wrSlide") && idTable) {
            HideDetail(false);
            DisplayDetail(idTable, false);
        }
    }
    // document.documentElement.scrollTop = (ind > wrSlide.length / 2) ? 120 : 0;
    SessSetItem(slideInd, ind + 1);
}

function StartAutoSlideShow() {
    if (!slideInterval && DocGetEClass("wrSlide")) {
        AutoSlideShow();
        var autoDisplayCx = DocGetEID("autoDisplayCx");
        if (autoDisplayCx != undefined) autoDisplayCx.checked = true;
        slideInterval = setInterval(AutoSlideShow, slideSec);
    }
}

function ChangeAutoSlide(checked) {
    if (checked) {
        StartAutoSlideShow();
    } else {
        RemoveSlideInterval();
        RemoveAllHov();
        HideDetail(false);
    }
}

function ToggleFlip(emt) {
    if (emt.classList.contains(flip)) emt.classList.remove(flip);
    else emt.classList.add(flip);
}

function AutoFlip() {
    var wrSlide = DocGetEClass("wrSlide");
    for (let i = 0; i < wrSlide.length; i++) {
        ToggleFlip(wrSlide[i]);
    }
}

function SetAutoDisplaySw() {
    if (slideInterval) {
        DocGetEID("autoDisplayCx").checked = true;
        Dec1SlideInd();
        ChangeAutoSlide(true);
    } else DocGetEID("autoDisplayCx").checked = false;
}

function ReloadWR() {
    $.ajax({
        type: 'GET',
        url: "wr.php?factory=" + GetUrlPara("factory"),
        success: function (response) {
            $('#index').html(response);
            SetNextFactory();
            ["left", "right"].forEach(dirct => {
                $('#machine' + dirct).val(SessGetItem("machine" + dirct));
                ChangeMachine(dirct, DocGetEID("machine" + dirct));
            });
        }
    });
}

function ChangeMachine(directionTxt, emt) {
    var machineNum = emt.value;
    var directRows = DocGetEClass(directionTxt);
    for (let i = 0; i < directRows.length; i++) {
        var displayStyle = machineNum == "all" ? "" : "None"
        directRows[i].style.display = displayStyle;
    }
    var targetDisplay = DocGetEClass(directionTxt + machineNum);
    for (let i = 0; i < targetDisplay.length; i++) {
        targetDisplay[i].style.display = "";
    }
    SessSetItem(emt.id, machineNum);
}

function SetNextFactory() {
    var targetFactory = 0;
    if (GetUrlPara("factory") == 4) targetFactory = 6;
    else if (GetUrlPara("factory") == 6) targetFactory = 4;
    GetChangeUrlPara("index.html", "", "", ["factory"]);
    GetChangeUrlPara("index.html", "factory", targetFactory);
    DocGetEID("headLink").href = tempUrl;
}

window.onload = function () {
    if (GetUrlPara("wr") == "wr") {
        SessSetItem("machineleft", "all");
        SessSetItem("machineright", "all");
        ReloadWR();
        setInterval(ReloadWR, reloadSec);
    } else {
        ReloadMain();
        setInterval(ReloadMain, reloadSec);
        setInterval(AutoFlip, 2000);
        autoSlideShowInterval =
            setInterval(StartAutoSlideShow, autoSlideShowSec);
    }
}