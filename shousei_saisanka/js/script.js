var DocGetEID = (id) => document.getElementById(id);
var DocGetEClass = (className) => document.getElementsByClassName(className);
var DocGetETag = (tagName) => document.getElementsByTagName(tagName);
var DocGetEName = (name) => document.getElementsByName(name);

var DocGetEIDVal = (id) => DocGetEID(id).value;
var DocGetEIDIHtml = (id) => DocGetEID(id).innerHTML;
var SessSetItem = (key, val) => sessionStorage.setItem(key, val);
var SessGetItem = (key) => sessionStorage.getItem(key);
var GetScrollTop = () => document.documentElement.scrollTop;
var SetScrollTop = (val) => document.documentElement.scrollTop = val;

var JSONstringify = (form) => JSON.stringify($(form).serializeArray());
var FieldId = (field, id) => field + '_' + id; 
var currUrl = new URL(window.location);
var GetUrlPara = (p) => currUrl.searchParams.get(p);
var SaveTableName = (name) => SessSetItem("tableName", name);
var GetTableName = () => SessGetItem("tableName");

var ShowLoading = () => $("#loader-holder").show();
var HideLoading = () => $("#loader-holder").hide();

var GetTableNameProcessPara = 
    (tableName = GetTableName(), process = GetUrlPara("process")) =>
    "?process=" + process + "&tableName=" + tableName;

function GetChangeUrlPara(targetUrl, addField = "", addValue = "", removeParams = []) {
    currUrl.searchParams.set(addField, addValue);
    let params = new URLSearchParams(currUrl.search);
    removeParams.forEach(function Rem(item) {
        params.delete(item);
    });
    return targetUrl + "?" + params;
}


// https://technotrampoline.com/articles/how-to-convert-form-data-to-json-with-jquery/
function ConvertFormToJSON(form) {
    const array = $(form).serializeArray(); // Encodes the set of form elements as an array of names and values.
    const json = {};
    $.each(array, function () {
      json[this.name] = this.value || "";
    });
    return JSON.stringify(json);
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
    // var ampm = hours >= 12 ? 'PM' : 'AM';
    // hours = hours % 12;
    // hours = hours ? hours : 12;
    hours = hours < 10 ? '0' + hours : hours;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    sec = sec < 10 ? '0' + sec : sec;
    return hours + ':' + minutes;
}

function FormatJSONDate(date){
    var month = date.getMonth() + 1;
    month = month < 10 ? '0' + month : month;
    var day = date.getDate();
    day = day < 10 ? '0' + day : day;
    return date.getFullYear() + "-" + month + "-" + day;
}

function FormatJSONDateTime(date){
    return FormatJSONDate(date) + "T" + FormatTime(date);
}

var FormatDateTime = date => FormatDate(date) + " " + FormatTime(date);

function DisplayDT() {
    var date = new Date();
    var dateTimeLi = ["input_date", "date", 
        "input_time", "start_time", "inspection_dt"];
    for (let i = 0; i < dateTimeLi.length; i++) {
        var emt = DocGetEID(dateTimeLi[i]);
        if(emt) {
            if(dateTimeLi[i].includes("date"))
                emt.value = FormatJSONDate(date);
            else if(dateTimeLi[i].includes("time"))
                emt.value = FormatTime(date);
            else if(dateTimeLi[i].includes("dt"))
                emt.value = FormatJSONDateTime(date);
        }
    }
    mytime = setTimeout('DisplayDT()', 500); // Refresh rate in milli seconds
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

function SubmitStringify(form) {
    DocGetEID("submission").value = ConvertFormToJSON(form);
}

function ChangeItemSetting(){
    DocGetEID("item_setting").innerHTML = DocGetEID("item").value;
}

function AssignValueSubmit(status, data, formName = "indexForm") {
    DocGetEID(status).value = data;
    DocGetEID(formName).submit();
}

function ChangesSubmitIfEnter(event, status, id) {
    if (event.key === 'Enter') 
        ChangesSubmit(status, id);
}

function RefreshDocInd() {
    var doc_inds = DocGetEClass("doc_ind");
    for (var i = 0; i < doc_inds.length; i++) {
        doc_inds[i].innerHTML = i + 1;
    }
}

function ChangesSubmit(status, id, lot_no = "") {
    const tableName = GetTableName();
    const collect_pic = "collect_pic";
    const quantity_stick = "quantity_stick";
    const quantity_actual = "quantity_actual";
    // const isCheckBox = status.includes("check_box");
    const isQuantityActual = status.includes(quantity_actual);
    const isQuantityStick = status.includes(quantity_stick);
    const isCollectPic = status.includes(collect_pic);
    const isDelete = status.includes("delete");

    const human_lan = {
        collect_pic: "Collecting PIC",
        quantity_stick: "Stick Chip Quantity",
        quantity_actual: "Setter Actual Quantity"
    };
    var recordInputs = [collect_pic];
    if(tableName == "mrs") {
        recordInputs.push(quantity_stick);
        recordInputs.push(quantity_actual);
    }
    
    if (!isDelete || (isDelete && confirm("Confirm to delete ?\nItem Lot No: " + lot_no))) {
        var data = id;
        if (isQuantityActual || isQuantityStick || isCollectPic) {
            data = status + ',id,' + DocGetEID(FieldId(status, id)).value + ',' + id;
            status = "update_small";
            for (var i = 0; i < recordInputs.length; i++) {
                if(!DocGetEIDVal(FieldId(recordInputs[i], id))) {
                    alert("Please fill out " + human_lan[recordInputs[i]]);
                    DocGetEID(FieldId(recordInputs[i],id)).focus();
                    return false;
                }
            }
            var collect_picVal = DocGetEIDVal(FieldId(collect_pic,id));
            var confirmMsg = "Confirm Collecting PIC - " + collect_picVal;
            if(tableName == "mrs") {
                var quantity_actualVal = DocGetEIDVal(FieldId(quantity_actual,id));
                var quantity_stickVal = DocGetEIDVal(FieldId(quantity_stick,id));
                confirmMsg += "\nStick Chip - " + quantity_stickVal + "\nSetter Actual - " + quantity_actualVal;
                data = 'collect_pic,quantity_stick,quantity_actual,id,' + 
                    collect_picVal + ',' + quantity_stickVal + ',' + quantity_actualVal + ',' + id;
            }
            confirmMsg += " ?";
            if(!confirm(confirmMsg)) return;
        } 
        else if (isDelete) {
            data = 'id,' + id;
            SessSetItem("del_lot_no", lot_no)
        }
        // else if (isCheckBox) data = content;

        var dataField = {};
        dataField[status] = data;
        UpdateAjPutRecordRow(dataField, id);
    }
    return false;
}

function ShowPopUp(aTitle) {
    $('#popup').hide();
    $.ajax({
        type: 'POST',
        url: "popup.php",
        data: { title : aTitle },
        success: function(response) { 
            $('#popup').html(response);
            $('#popup').show();
            $('#pw').focus();
        }
    });
}

function SubmitPopUp(action, inp) {
    $('#popup').html("");
    if(action == "edit") {
        var submission = JSON.parse(SessGetItem("tempFormJSON"));
        submission['pw'] = inp;
        submission = JSON.stringify(submission);
        DocGetEID("submission").value = submission;
        DocGetEID("recordForm").submit();
    } else if(action == "delete") {
        var id = SessGetItem("deleteId");
        var dataField = { delete_document : "id,pw," + id + "," + inp };
        UpdateAjPutRecordRow(dataField, id);
    }
    return false;
}

function UpdateAjPutRecordRow(dataField, id) {
    ShowLoading();
    $.ajax({
        type: 'POST',
        url: "changes.php" + GetTableNameProcessPara(),
        data: dataField,
        success: function(response) { 
            if("delete_document" in dataField && response.trim() != "") {
                LoadFilterLotNo(SessGetItem("del_lot_no"));
                SessSetItem("deleteId", id);
                ShowPopUp("delete");
            } else {
                $('#data_row_' + id).replaceWith(response);
                RefreshDocInd();
            }
            HideLoading();
        }
    });
}

function SearchAjPutRecordTable(theUrl, putTable = true) {
    $("#recordTable").html("");
    ShowLoading();
    var btnClassNames = ["navBtn", "searchBtn"];
    btnClassNames.forEach(bcn => {
        var allButtons = DocGetEClass(bcn);
        for (var i = 0; i < allButtons.length; i++)
            allButtons[i].disabled = true;
    });
    $.ajax({
        type: 'POST',
        url: theUrl,
        data: $('#search').serializeArray(),
        success: function(response){
            if(putTable) $("#recordTable").html(response);
            else {
                window.open("export.php?newFileName=" + response, "_blank");
                SearchAjPutRecordTable("recordTable.php" + GetTableNameProcessPara());
            }
            LoadFilterLotNo();
            btnClassNames.forEach(bcn => {
                var allButtons = DocGetEClass(bcn);
                for (var i = 0; i < allButtons.length; i++) {
                    if(!allButtons[i].classList.contains("active"))
                        allButtons[i].disabled = false;
                }
            });
        }
    }).done(function(data){ HideLoading(); });
}

function FinishDTRequired() {
    var dateEmpty = DocGetEIDVal("finishDate") == "";
    var timeEmpty = DocGetEIDVal("finishTime") == "";
    var isRequired = dateEmpty || timeEmpty;
    DocGetEID("finishDate").required = isRequired;
    DocGetEID("finishTime").required = isRequired;
}

function SetSelectInput(id, valueToSelect) {
    DocGetEID(id).value = valueToSelect;
}

function SetSelectIndex(id, indexToSelect) {
    DocGetEID(id).selectedIndex = indexToSelect;
}

function SearchRecord(jump_date = "") {
    if (jump_date) {
        var date = new Date();
        if(jump_date == "thisMonth") {
            SetSelectInput("searchDay", "X");
        } else if(jump_date == "today") {
            SetSelectInput("searchDay", date.getDate());
        } 
        SetSelectIndex("searchMonth", date.getMonth());
        SetSelectInput("searchYear", date.getFullYear());
    }
    SearchAjPutRecordTable("recordTable.php" + GetTableNameProcessPara());
}

function DownloadRecord() {
    if(confirm("Note: \n\
- File preparation may take several seconds or more \n\
- Do not close the popup window to let the download complete \n\
Confirm to download record?"))
        SearchAjPutRecordTable("export.php" + GetTableNameProcessPara(), false);
}

function ChangeSearch(action, fieldStr) {
    const selectField = ["searchFurnaceNo", "searchDay", "searchMonth"];
    if (selectField.includes(fieldStr)) {
        var opts = DocGetEID(fieldStr).options;
        for (var i = 0; i < opts.length; i++) {
            if (opts[i].selected) {
                var selectedOpt = opts[i];
                opts[i].selected = false;
                if (action == "+") {
                    if (i + 1 == opts.length) i = -1;
                    selectedOpt = opts[i + 1];
                } else if (action == "-") {
                    if (i == 0) i = opts.length; 
                    selectedOpt = opts[i - 1];
                }
                selectedOpt.selected = true;
                break;
            }
        }
    } else {
        var curr = DocGetEID(fieldStr).value;
        if (action == "+") {
            curr = parseInt(curr) + 1;
        } else if (action == "-") {
            if (parseInt(curr) - 1 > 0)
                curr = parseInt(curr) - 1;
        }
        var prev = DocGetEID(fieldStr).value;
        if (prev != curr)
            DocGetEID(fieldStr).value = curr;
    }
}

function FilterLotNo(elmt) {
    var data_rows = DocGetEClass("data_row");
    // var data_row_inspected = DocGetEClass("data_row_inspected");
    var lot_nos = DocGetEClass("lot_no");
    for (var i = 0; i < lot_nos.length; i++) {
        var gotInclude = lot_nos[i].innerHTML.includes(elmt.value, 0);
        var displayStyle = gotInclude ? "" : "none";
        if (data_rows[i] == undefined) continue;
        data_rows[i].style.display = displayStyle;
        if (GetTableName() == "qs_furnace") 
            data_rows[i].nextElementSibling.style.display = displayStyle;
    }
    sessionStorage.setItem("filterLotNo", elmt.value);
}

function ClearLotNo() {
    var lotNoFilter = DocGetEID('lotNoFilter');
    lotNoFilter.value = '';
    FilterLotNo(lotNoFilter);
    lotNoFilter.focus();
}

function ChangeDisplay(className, isShow) {
    var emts = DocGetEClass(className);
    for (var i = 0; i < emts.length; i++)
        emts[i].style.display = isShow ? "" : "none";
}

function SetSearchPDNOnChange() {
    ["item_name", "item_type"].forEach(id => {
        var emt = DocGetEID(id);
        if(emt) {
            emt.onchange = function() { SearchPDN("input_tpn", this.value); };
            if (SessGetItem("isSubmission") == 'false') SearchPDN("input_tpn", emt.value);
        }
    });
}

function InsertInit() {
    var inputs = DocGetETag("input");
    var removeFrameInps = ["text", "number"];
    var ignoreInps = ["hidden", "reset", "submit"];
    for (var i = 0; i < inputs.length; i++) {
        var pEmtClassLi = inputs[i].parentElement.classList;
        if(removeFrameInps.includes(inputs[i].type))
            pEmtClassLi.add("inpValTD");
        else if (!ignoreInps.includes(inputs[i].type))
            pEmtClassLi.add("inpDisTD");
    }
    var selects = DocGetETag("select");
    for (var i = 0; i < selects.length; i++) {
        var pEmtClassLi = selects[i].parentElement.classList;
        pEmtClassLi.add("inpDisTD");
    }
    SetSearchPDNOnChange();
    ChangeDisplay("confirm", false);
    if (SessGetItem("action") == "add") DisplayDT();

    new Cleave("#lot_size", {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
    // document.querySelectorAll('.loan_max_amount').forEach(inp => new Cleave(inp, {
    //     numeral: true,
    //     numeralThousandsGroupStyle: 'thousand'
    //   }));
}

function SearchPDN(field, tpn, emt) {
    if(emt) emt.onmouseover = null;
    $.ajax({
        type: 'GET',
        url: "pdn_tpn.php" + GetTableNameProcessPara() + "&" + field + "=" + tpn,
        success: function(response) { 
            response = response.trim();
            if (location.href.includes("insert")) { // In insert page
                if (response) {
                    try {
                        var responseJS = JSON.parse(response);
                        $('#pdn_name').html(responseJS["pdn_name"]);
                        if (GetTableName() == "mrs") {
                            var pattern_no = responseJS["pattern_no"];
                            $('#pattern_no_dis').html(pattern_no);
                            $('#pattern_no').val(pattern_no);
                        }
                    } catch (e) {
                        $('#pdn_name').html(response);
                        $('#pattern_no_dis').html("");
                    }
                }
            } else if (response) { 
                var emtChilds = emt.childNodes;
                for (let i = 0; i < emtChilds.length; i++) {
                    var classLi = emtChilds[i].classList;
                    if (classLi && classLi.contains("item_pdn")) 
                        emtChilds[i].innerHTML = "<p>" + JSON.parse(response)["pdn_name"] + "</p>";
                }
            }
        }
    });
}

function ConfirmInsert() {
    var recordForm = DocGetEID("recordForm");
    SessSetItem("tempForm", recordForm.innerHTML);
    SessSetItem("tempFormJSON", ConvertFormToJSON(recordForm));
    var inpValTDs = DocGetEClass("inpValTD");
    for (var i = 0; i < inpValTDs.length; i++) {
        var inpCNs = inpValTDs[i].childNodes;
        for (var j = 0; j < inpCNs.length; j++) {
            if(inpCNs[j].value !== undefined) {
                inpValTDs[i].innerHTML = inpCNs[j].value;
                break;
            }
        }
    }
    var inpDisTDs = DocGetEClass("inpDisTD");
    var disEmtTagNames = ["SELECT", "INPUT"];
    for (var i = 0; i < inpDisTDs.length; i++) {
        var inpCNs = inpDisTDs[i].childNodes;
        for (var j = 0; j < inpCNs.length; j++) {
            if(disEmtTagNames.includes(inpCNs[j].tagName)) {
                inpCNs[j].disabled = true;
                break;
            }
        }
    }
    var furnace_nos = DocGetEName("furnace_no");
    for (var i = 0; i < furnace_nos.length; i++)
        if (furnace_nos[i].checked) 
            furnace_nos[i].parentElement.innerHTML = furnace_nos[i].value;
    
    ChangeDisplay("confirm", true);
    ChangeDisplay("save", false);
    SetScrollTop(1000);
    return false;
}

function RevertBackForm() {
    DocGetEID("recordForm").innerHTML = SessGetItem("tempForm");
    var inputs = DocGetETag("INPUT");
    for (var i = 0; i < inputs.length; i++) 
        if (inputs[i].type == "checkbox") inputs[i].checked = false;
        
    const tempFormJSON = JSON.parse(SessGetItem("tempFormJSON"));
    Object.keys(tempFormJSON).forEach(function(key) {
        var inpEmt = DocGetEName(key)[0];
        var val = tempFormJSON[key];
        if (val == "on") inpEmt.checked = true;
        else inpEmt.value = val;
        if (key == "furnace_no") DocGetEID(val).checked = "true";
    })
    SetSearchPDNOnChange();
}

function SubmitInsertForm() {
    var recordForm = DocGetEID("recordForm");
    SessSetItem("isSubmission", true);
    RevertBackForm();
    DocGetEID("submission").value = SessGetItem("tempFormJSON");
    recordForm.submit();
}

function InsertSuccess(addEdit, submission) {
    if(addEdit == "edit") SessSetItem("filterLotNo", JSON.parse(submission)["lot_no"]);
    location.href = GetChangeUrlPara("record.html", "", "", ["addEdit", "id"]);
}

function LoadFilterLotNo(filterLotNo = SessGetItem("filterLotNo")) {
    if (filterLotNo) {
        var lotNoFilter = DocGetEID('lotNoFilter');
        if(lotNoFilter) {
            lotNoFilter.value = filterLotNo;
            FilterLotNo(lotNoFilter);
        }
    }
}

function RecordInit() {
    window.onscroll = function () {
        localStorage.setItem("scroll", GetScrollTop());
        if (GetScrollTop() < 200) $("#backToTop").hide();
        else $("#backToTop").show();
    };
    var scroll = localStorage.getItem("scroll");
    if (scroll) SetScrollTop(scroll);
    SaveTableName(GetUrlPara("tableName"));
}

window.history.replaceState(null, null, window.location.href);

// window.open('http://www.google.com', '_blank');
// window.close();
// window.open('', '_self', ''); 

// function ChangeTable(name) {
//     var targetButton = DocGetEID("nav" + name);
//     if (!targetButton) return;
//     var allButtons = DocGetEClass("navBtn");
//     DocGetEID("tableButtonName").innerHTML = targetButton.innerHTML;
//     for (var i = 0; i < allButtons.length; i++) {
//         allButtons[i].classList.remove("active");
//         allButtons[i].disabled = false;
//     }
//     targetButton.classList.add("active");
//     targetButton.disabled = true;
//     var urlPara = GetTableNameProcessPara(name);
//     SearchAjPutRecordTable("recordTable.php" + urlPara);
//     DocGetEID("insertAnchor").href = "insert.php" + urlPara + "&addEdit=add";
//     SaveTableName(name);
// }