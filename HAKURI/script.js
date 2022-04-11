class RecItem {
    constructor(item_id, tank_no, lot_no) {
        this.item_id = item_id;
        this.tank_no = tank_no;
        this.lot_no = lot_no;
    }
}

class RecBatch {
    constructor(
        machine_no, rec_items, grinding_mins,
        rpm, start_pic
    ) {
        this.machine_no = machine_no;
        this.rec_items = rec_items;
        this.grinding_mins = grinding_mins;
        this.rpm = rpm;
        this.start_pic = start_pic;
    }

    others(rec_batch_id, start_dt, finish_dt, end_pic) {
        this.rec_batch_id = rec_batch_id;
        this.start_dt = start_dt;
        this.finish_dt = finish_dt;
        this.end_pic = end_pic;
    }
}

class Item {
    constructor(
        item_name, type, item_type, item_grp, item_lot
    ) {
        this.item_name = item_name;
        this.type = type;
        this.item_type = item_type;
        this.item_grp = item_grp;
        this.item_lot = item_lot;
    }
}

let DocGetEID = id => document.getElementById(id);
let DocGetEIDVal = id => DocGetEID(id).value;
let DocGetEIDIHtml = id => DocGetEID(id).innerHTML;

function Reset(field = ['itemName', 'lotNo', 'result']) {
    field.forEach(f => {
        for (let i = 1; i <= 4; i++) {
            var doc = DocGetEID(f + i);
            if (doc !== null) doc.value = doc.innerHTML = "";
        }
    });
}

function ResetAll() {
    Reset();
    DocGetEID("start_pic").value = "";
    if (DocGetEID("end_pic")!= null)
        DocGetEID("end_pic").value = "";
    InputItemNameAll();
    DocGetEID("itemName1").focus();
}

function UpdateTankNum() {
    var tankLoop = { start: 0, end: 0 };
    switch (DocGetEIDVal("machine_no")) {
        case "4":
            tankLoop.start = 1; tankLoop.end = 4;
            break;
        case "5":
            tankLoop.start = 5; tankLoop.end = 8;
            break;
    }
    var pos = 1;
    for (let num = tankLoop.start; num <= tankLoop.end; num++) {
        var doc = DocGetEID("tankNo" + pos);
        doc.innerHTML = num;
        doc = DocGetEID("tankImgNo" + pos);
        doc.src = "img/tank" + num + ".png"
        pos++;
    }
}

function FormatDate(date) {
    const months = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"];
    return date.getDate() + " " + months[date.getMonth()] + " " + date.getFullYear();
}

function FormatTime(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    // var sec = date.getSeconds();
    // var ampm = hours >= 12 ? 'PM' : 'AM';
    // hours = hours % 12;
    // hours = hours ? hours : 12;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    // sec = sec < 10 ? '0' + sec : sec;
    // return hours + ':' + minutes + ' ' + ampm;
    return hours + ':' + minutes;
}

let FormatDateTime = date => FormatDate(date) + " " + FormatTime(date);

function DisplayDT() {
    var date = new Date();
    DocGetEID('StartDate').innerHTML = FormatDate(date);
    DocGetEID('StartTime').innerHTML = FormatTime(date);
    mytime = setTimeout('DisplayDT()', 1000); // Refresh rate in milli seconds
}

function InputItemName(pos, isResetResult = true) {
    if (isResetResult) Reset(['result']);
    if (pos == 1) return;
    DocGetEID("lotNo" + pos).required = DocGetEID("itemName" + pos).value != "";
}

function InputItemNameAll(isResetResult = false) {
    for (let i = 1; i <= 4; i++)
        InputItemName(i, isResetResult);
}

function InputItem(label, pos) {
    switch (label) {
        case 'itemName':
            InputItemName(pos); break;
        case 'lotNo':
            Reset(['result']); break;
    }
}

// https://stackoverflow.com/questions/41512891/simple-html-form-input-field-with-multiple-values-from-barcode-scanner?rq=1
function KeyDownItem(event, label, pos) {
    var letter = event.key;
    if (letter === 'Enter') {
        event.preventDefault();
        var labelPos = label + pos;
        if (labelPos != "lotNo4") {
            switch (label) {
                case 'itemName':
                    DocGetEID("lotNo" + pos).focus(); break;
                case 'lotNo':
                    DocGetEID("itemName" + (pos + 1)).focus(); break;
            }
        }
    }
}

function SubmitRecord() {
    UpdateTankNum();
    var items = [];
    for (let i = 1; i <= 4; i++) {
        var itemID = DocGetEIDVal('itemID' + i);
        if (itemID !== null)
            items.push(new RecItem(
                itemID,
                DocGetEID('tankNo' + i).innerHTML,
                DocGetEIDVal('lotNo' + i)
            ));
    }
    var batch = new RecBatch(
        DocGetEIDVal('machine_no'),
        items,
        DocGetEIDVal('grindingMins'),
        DocGetEIDVal('rpm'),
        DocGetEIDVal('start_pic')
    );
    if (localStorage.getItem('action') == "edit") {
        var start_dt = DocGetEIDVal("startDate") + "," + DocGetEIDVal("startTime");
        var finish_dt = DocGetEIDVal("finishDate") + "," + DocGetEIDVal("finishTime");
        if (DocGetEIDVal("startTime").length != 8) start_dt += ":00";
        if (finish_dt != ",") finish_dt += ":00";
        batch.others(DocGetEIDVal("rec_batch_id"), start_dt, finish_dt, DocGetEIDVal("end_pic"));
        DocGetEID("recordForm").action = "edit.php";
    }
    DocGetEID("submission").value = JSON.stringify(batch);
    DocGetEID("recordForm").submit();
}

function AssignValueSubmit(status, data, formName = "indexForm") {
    DocGetEID(status).value = data;
    DocGetEID(formName).submit();
}

function IndexSubmit(status, batchId) {
    switch (status) {
        case "delete":
            if (confirm("Confirm to delete?"))
                AssignValueSubmit(status, batchId);
            break;
        case "now":
            AssignValueSubmit(status, batchId);
            break;
        case "endPic":
            var end_pic = DocGetEIDVal("end_pic_" + batchId) + "," + batchId
            DocGetEID("hid_end_pic_" + batchId).value = end_pic;
            break;
    }
}

function AddEditSubmit(status, batchId = null) {
    DocGetEID("addEdit").value = status;
    DocGetEID("batchId").value = batchId;
    DocGetEID("addEditForm").submit();
}

function SubmitSearch() {
    DocGetEID("search").submit();
}

function FinishDTRequired() {
    var dateEmpty = DocGetEIDVal("finishDate") == "";
    var timeEmpty = DocGetEIDVal("finishTime") == "";
    var isRequired = dateEmpty || timeEmpty;
    DocGetEID("finishDate").required = isRequired;
    DocGetEID("finishTime").required = isRequired;
}

function SubmitItemNew() {
    var item = new Item(
        DocGetEIDVal("itemName"),
        DocGetEIDVal("type"),
        DocGetEIDVal("itemType"),
        DocGetEIDVal("itemGroup"),
        DocGetEIDVal("itemLot")
    );
    DocGetEID("submission").value = JSON.stringify(item);
}

function DeleteItem(id) {
    if (confirm("Confirm to delete?"))
        AssignValueSubmit("delete", id);
}

function ReqPICid() {
    let picId = prompt("Enter PIC ID");
    AssignValueSubmit("picId", picId, "generalForm");
}

function SaveLoadScroll() {
    window.onscroll = function() {
        localStorage.setItem("scroll", document.documentElement.scrollTop);
    };

    window.onload = function() {
        if (localStorage.getItem("scroll") != null)
            document.documentElement.scrollTop = localStorage.getItem("scroll");
    };
}

window.history.replaceState(null, null, window.location.href);