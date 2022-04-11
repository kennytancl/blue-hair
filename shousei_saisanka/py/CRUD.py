from datetime import timedelta
from sqlite3 import DatabaseError
import mysql.connector
from imp import cnx, cursor, linked, json, datetime, ConvertLi, CursorToJSON, PrintJSON
from hashlib import sha256

FMT_JSON = '%Y-%m-%dT%H:%M'
FMT_BR = '%d %b %Y<br>%H:%M'
FMT_DATE_TXT = '%d %b %Y'
FMT_TIME_24 = '%H:%M'
FMT_TIME_APM = '%I:%M %p'
FMT_COMMA_APM = f'{FMT_DATE_TXT}, {FMT_TIME_APM}'
FMT_DATE = '%Y-%m-%d'
FMT_MONTH = '%b'
FMT_YEAR = '%Y'
FMT_DAY = '%d'
FMT_COMMA = f'{FMT_DATE},{FMT_TIME_24}'
MRS = "mrs"
N2_SAISANKA = "n2_saisanka"
QS_FURNACE = "qs_furnace"

O2_FAST_COOLING = "o2_fast_cooling"
O2_LOW_TEMPERATURE = "o2_low_temperature"
O2_SAISANKA = "o2_saisanka"
QH_FURNACE = "qh_furnace"

NULL = "NULL"

def DTNow(): return datetime.now().strftime('%Y-%m-%d %H:%M')
def FmtInsCol(fieldLi): return ', '.join(fieldLi)
def FmtInsVal(fieldLi): return '%(' + ')s, %('.join(fieldLi) + ')s'

def CheckInt(str):
    try:
        int(str)
        return True
    except ValueError:
        return False

def ConvertNameValue(temp):
    data = {}
    for t in temp: 
        if "name" in t and "value" in t:
            data[t["name"]] = t["value"]
        else: return temp
    return data

def ConvertCheckBox(data, name):
    data[name] = 1 if name in data and (data[name] == "on" or data[name] == 1) else 0
    return data

def ConvertUpdateFields(fields):
    s = []
    for f in fields: s.append("{0} = %({0})s".format(f))
    return ', '.join(s)

def SplitLi(lis):
    half = len(lis)//2
    return lis[:half], lis[half:]

def LiToSQL(field, joinStr, isJSON = False):
    tempLi = []
    eqStr = '{0} = %({0})s' if isJSON else '{0} = %s'
    for f in field: tempLi.append(eqStr.format(f))
    return joinStr.join(tempLi)
    
def ConvertDT(data, tableName):
    for d in data:
        if MRS in tableName:
            d["date_txt"] = d["start_dt"].strftime(FMT_DATE_TXT)
            d["start_dt"] = d["start_dt"].strftime(FMT_TIME_24)
            d["end_dt"] = d["end_dt"].strftime(FMT_TIME_24) if d["end_dt"] else "-"
        elif QS_FURNACE in tableName:
            d["inspection_datetime"] = d["inspection_dt"].strftime(FMT_BR.replace('<br>', ' '))
            d["inspection_date"] = d["inspection_dt"].strftime(FMT_DATE)
            d["inspection_time"] = d["inspection_dt"].strftime(FMT_TIME_24)
            d["inspection_dt"] = d["inspection_dt"].strftime(FMT_JSON)
        else:
            d["input_dt"] = d["input_dt"].strftime(FMT_BR)
            d["output_dt"] = d["output_dt"].strftime(FMT_BR) if d["output_dt"] else "-"
    return data

def GetTPN(data):
    for field in ("item_name", "item_type"):
        if field in data: k = field
    return data[k]
def PrintInsertMsg(insertMsg): PrintJSON([insertMsg])
def GetInsertMsg(data, itemNameMetCondi, pwTrue):
    itemNameInp = GetTPN(data)
    return {
        "itemNameInp" : itemNameInp,
        "itemNameFound" : str(itemNameInp in GetAllTpn()),
        "itemNameMetCondi" : str(itemNameMetCondi),
        "pwTrue" : str(pwTrue)
    }
def CanInsert(data, itemNameMetCondi, pwTrue):
    countTrue = 0
    insertMsg = GetInsertMsg(data, itemNameMetCondi, pwTrue)
    for key in insertMsg:
        if insertMsg[key] == "True":
            countTrue += 1
    return countTrue >= 3, insertMsg
    
def read(status, data):
    filterField, filterData = SplitLi(ConvertLi(data))
    tableName = status.replace("READ", "")

    if MRS in tableName: dtField = "start_dt"
    elif QS_FURNACE in tableName: dtField = "inspection_dt"
    else: dtField = "input_dt"

    if 'X' in filterData:
        del filterField[filterData.index('X')]
        filterData.remove('X')

    # fieldEQ = []
    # for ff in filterField: fieldEQ.append('{} = %s'.format(ff))
    # fieldEQ = ' AND '.join(fieldEQ)
    
    fieldEQ = LiToSQL(filterField, ' AND ')
    if '{}' in fieldEQ: fieldEQ = fieldEQ.replace('{}', '{0}').format(dtField)
    query = f"SELECT * FROM {tableName} WHERE {fieldEQ} ORDER BY {dtField} DESC, id DESC "
    
    cursor.execute(query, tuple(filterData))
    data = CursorToJSON()
    for d in data:
        if "lot_size" in d:
            d["lot_size_str"] = '{:,}'.format(d["lot_size"])
        if MRS in tableName:
            d["date"] = d["start_dt"].strftime(FMT_DATE)
            d["start_time"] = d["start_dt"].strftime(FMT_TIME_24)
            d["end_time"] = d["end_dt"].strftime(FMT_TIME_24) \
                if d["end_dt"] else ''
            stick = d["quantity_stick"]
            d["stick_rate"] = "{:.2f}".format(stick / d["lot_size"] * 100) \
                if stick or stick == 0 else '-'
        elif QS_FURNACE not in tableName:
            d["input_date"] = d["input_dt"].strftime(FMT_DATE)
            d["input_time"] = d["input_dt"].strftime(FMT_TIME_24)
            d["output_date"] = d["output_dt"].strftime(FMT_DATE) \
                if d["output_dt"] else ''
            d["output_time"] = d["output_dt"].strftime(FMT_TIME_24) \
                if d["output_dt"] else ''
        
    data = ConvertDT(data, tableName) #, "id" in filterField)
    PrintJSON(data)
    return data

CHECKBOXES = ("confirmation", "o2_free", "slit_nfmd", "tube_nfmd", "feeder_nfmd", "tray_nfmd")
def prepareInsert(tableName, data, isReset = False):
    if not isReset:
        data = data.replace("'", '"')
        # data = ConvertNameValue(json.loads(data)[0])
        data = json.loads(data)[0]
    for c in CHECKBOXES: data = ConvertCheckBox(data, c);

    for d in data:
        if not data[d] and d not in CHECKBOXES: 
            is_quantity_stick = d == "quantity_stick"
            if not is_quantity_stick or (is_quantity_stick and data[d] != 0):
                data[d] = NULL

    excptLi = []
    if QS_FURNACE in tableName:
        fieldLi = ["furnace_no", "inspection_dt", "item_type", "lot_no", 
            "lot_quantity", "item", "slit", "slit_nfmd", "temp", 
            "tube_nfmd", "feeder_nfmd", "tray_nfmd",
            "done_by", "checked_by", "verified_by", "remark"]
            
    elif MRS in tableName:
        fieldLi = ["furnace_no", "start_dt", "item_name", "key_no", 
            "lot_no", "lot_size", "quantity_plan", "pattern_no", 
            "narabe_pic", "grp"]
        excptLi = ["quantity_actual", "quantity_stick", "collect_pic", "end_dt"]

        if "start_dt" not in data:
            data["start_dt"] = f"{data['date']}T{data['start_time']}"
            if data["end_time"] != NULL:
                data["end_dt"] = f"{data['date']}T{data['end_time']}"
            else: data["end_dt"] = NULL
        data["lot_size"] = data["lot_size"].replace(",", "")

    else:
        fieldLi = ["furnace_no", "ticket_no", "item_name", "lot_no", "lot_size", "chip_layer", 
            "input_dt", "narabe_quantity", "narabe_pic"]
        fieldLi += ["o2_free"] if tableName == N2_SAISANKA \
            else ["setter_pilling", "confirmation"]
        excptLi = ["collect_pic", "output_dt"]

        if not isReset:
            data["input_dt"] = f"{data['input_date']}T{data['input_time']}"
            hasDateOrTime = data["output_date"] != NULL or data["output_time"] != NULL
            data["output_dt"] = f"{data['output_date']}T{data['output_time']}" \
                if hasDateOrTime else NULL
    
    for el in excptLi:
        if el in data and data[el] != NULL: 
            fieldLi.append(el)

    return fieldLi, data

def create(status, data, isReset = False):
    tableName = status.replace("CREATE", "")
    fieldLi, data = prepareInsert(tableName, data, isReset)
    if isReset: canInsert = True
    else:
        itemNameMetCondi = GetItemNameMetCondi(data, tableName)
        canInsert, insertMsg = CanInsert(data, itemNameMetCondi, True)
    if canInsert: 
        query = f"INSERT INTO {tableName} ({FmtInsCol(fieldLi)}) VALUES ({FmtInsVal(fieldLi)})"
        cursor.execute(query, data)
    if not isReset: PrintInsertMsg(insertMsg)
    return canInsert

def Before24Hour(tableName, id):
    if QS_FURNACE in tableName: dtField = "inspection_dt"
    elif MRS in tableName: dtField = "start_dt"
    else: dtField = "input_dt"
    query = f"SELECT {dtField} FROM {tableName} WHERE id = %s"
    cursor.execute(query, (id,))
    data = CursorToJSON()[0]
    return datetime.now() - data[dtField] < timedelta(hours=24)

def ConvertSplitLiToJson(data):
    theField, theData = SplitLi(ConvertLi(data))
    data = {}
    for i in range(len(theField)): data[theField[i]] = theData[i]
    return data

def GetPWTrue(tableName, data):
    return Before24Hour(tableName, data["id"]) or \
            ("pw" in data and sha256(data["pw"].encode()).hexdigest() == \
                "c627fdd7f347b81f57042fd411c6a32ab69597fa96691cdd35c97adc53ccde2b")

def delete(status, data):
    tableName = status.replace("DELETE", "")
    data = ConvertSplitLiToJson(data)
    pwTrue = GetPWTrue(tableName, data)
    if pwTrue:
        query = f"DELETE FROM {tableName} WHERE id = %(id)s"
        cursor.execute(query, data)
    data["pwTrue"] = str(pwTrue)
    PrintJSON([data])

def update(status, data):
    tableName = status.replace("UPDATE", "")
    if 'all' in status:
        tableName = tableName.replace("all", "")
        fieldLi, data = prepareInsert(tableName, data, False)
        pwTrue = GetPWTrue(tableName, data)
        itemNameMetCondi = GetItemNameMetCondi(data, tableName)
        canInsert, insertMsg = CanInsert(data, itemNameMetCondi, pwTrue)
        if canInsert:
            query = f"UPDATE {tableName} SET {LiToSQL(fieldLi, ', ', True)} WHERE id = %(id)s"
            cursor.execute(query, data)
        PrintInsertMsg(insertMsg)
    else:
        data = ConvertSplitLiToJson(data)
        # updField, updData = SplitLi(ConvertLi(data))
        # data = {}
        # for i in range(len(updField)): data[updField[i]] = updData[i]

        if "collect_pic" in data:
            dtAuto = "end_dt" if tableName == MRS else "output_dt"
            data[dtAuto] = DTNow()

        dataCopy = data.copy()
        del dataCopy['id']
        fieldEQ = LiToSQL(dataCopy.keys(), ', ', True)
        queryUpdate = f"UPDATE {tableName} SET {fieldEQ} WHERE id = %(id)s"
        # for d in data:
        #     if data[d] == "None":
        #         queryRead = f"SELECT {d} FROM {tableName} WHERE id = %s "
        #         cursor.execute(queryRead, (data['id'],))
        #         prevData = CursorToJSON()[0][d]
        #         data[d] = not prevData
        cursor.execute(queryUpdate, data)

_config = {
    # 'user': 'root',
    'user': 'smartE',
    'password': 'Ch3m1str3#94',
    'host': '192.168.166.3',
    'database': 'tdcs'
}
_cnx = mysql.connector.connect(**_config)
_cursor = _cnx.cursor()
def GetAllTpn():
    query = "SELECT tpn_name AS item_name FROM tbl_saisanka_pdn_tpn"
    _cursor.execute(query)
    return [i['item_name'].strip() for i in CursorToJSON(_cursor)]

def GetPDN(tpn, isPrint = True):
    query = ("SELECT pdn_name, condition1 AS pattern_no, condition4 AS condi FROM tbl_saisanka_pdn_tpn "
            "WHERE tpn_name = %s")
    _cursor.execute(query, (tpn,))
    data = CursorToJSON(_cursor)
    data = data[0] if data else {}
    if isPrint: PrintJSON([data])
    return data

def CheckMetCondi(tpn, tableName, isPrint = True):
    tblNContainCondi = {
        "mrs" : "",
        "n2_saisanka" : "",
        "o2_fast_cooling" : "Cooling",
        "o2_low_temperature" : "Low",
        "o2_saisanka" : "Normal",
        "qh_furnace" : "QH",
        "qs_furnace" : "QS"
    }
    data = GetPDN(tpn, False)
    data["metCondi"] = "True" \
        if "condi" in data and tblNContainCondi[tableName] in data["condi"] \
            else "False"
    if isPrint: PrintJSON([data])
    return data["metCondi"]

def GetItemNameMetCondi(data, tableName):
    return CheckMetCondi(GetTPN(data), tableName, False)

def InputTpn(data):
    tpn, tableName = ConvertLi(data)
    CheckMetCondi(tpn, tableName)

def GetMachines(data, isPrint = True):
    tableName = ConvertLi(data)[0]
    tbl_macType = {
        MRS : "MRS",
        N2_SAISANKA : "N2",
        O2_FAST_COOLING : "O2",
        O2_LOW_TEMPERATURE : "O2",
        O2_SAISANKA : "O2",
        QH_FURNACE : "QHS",
        QS_FURNACE : "QSS"
    }
    data = tbl_macType[tableName]
    query = ("SELECT machno FROM machines "
            "WHERE machtype = %s ORDER BY num")
    cursor.execute(query, (data,))
    data = [i['machno'] for i in CursorToJSON()]
    if isPrint: PrintJSON(data)
    return data

def main(linked):
    if not linked: return
    status, data = linked # linked = [status, data]
    if 'READ' in status: read(status, data)
    elif 'CREATE' in status: create(status, data)
    elif 'DELETE' in status: delete(status, data)
    elif 'UPDATE' in status: update(status, data)
    elif 'input_tpn' in status: InputTpn(data)
    elif 'get_pdn' in status: GetPDN(data)
    elif 'get_machines' in status: GetMachines(data)
    cnx.commit()

main(linked)
# main(["READ" + QS_FURNACE, '[furnace_no, 7]'])
# main(['CREATEmrs', '[[{"name":"item_name","value":"dfdfggf"},{"name":"lot_no","value":"dfdsgg"},{"name":"lot_size","value":"gfdgdf"},{"name":"key_no","value":"gdfgf"},{"name":"date","value":"2022-01-13"},{"name":"pattern_no","value":"1332"},{"name":"start_time","value":"09:40"},{"name":"end_time","value":""},{"name":"quantity_plan","value":"234234"},{"name":"quantity_actual","value":"23423"},{"name":"stick_quantity","value":"423434"},{"name":"narabe_pic","value":"23423423"},{"name":"grp","value":"42343"},{"name":"sheet_id","value":"53"},{"name":"submission","value":""}]]'])
# main(["input_tpn", '[CE LMK212 BJ106MDMT,qs_furnace]'])
# main(["UPDATEallo2_low_temperature", '[{"furnace_no":"1","ticket_no":"18","item_name":"RM GMK105ABJ105KV-","lot_no":"LPIC38U89Y","lot_size":"1748","chip_layer":"CC","input_date":"2022-02-08","output_date":"","setter_pilling":"Q6U","input_time":"10:20","output_time":"","narabe_quantity":"68","narabe_pic":"6932","collect_pic":"","id":"1692","submission":""}]'])
# main(["DELETEn2_saisanka", '[id,14]'])
# main(["get_machines", '[n2_saisanka]'])



# updField, updId = ConvertLi(data)
# queryUpdate = ("UPDATE {} SET {} = %s WHERE id = %s").format(tableName, updField)
# queryRead = "SELECT {} FROM {} WHERE id = %s ".format(updField, tableName)
# cursor.execute(queryRead, (updId,))
# data = CursorToJSON()[0]
# ori = None
# for key in data: ori = data[key]
# cursor.execute(queryUpdate, (not ori, updId))