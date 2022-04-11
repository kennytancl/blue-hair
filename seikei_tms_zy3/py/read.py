from http.client import REQUEST_URI_TOO_LONG
import mysql.connector
from imp import config, cnx, cursor, linked, json, CursorToJSON, GetCursorToJSON, PrintJSON, \
    IsInt, FMT_DT_SPACE, FMT_JSON
from datetime import datetime, timedelta
from operator import itemgetter
# https://stackoverflow.com/questions/72899/how-do-i-sort-a-list-of-dictionaries-by-a-value-of-the-dictionary
seikei_tms = "seikei_tms"
seikei_zy3 = "seikei_zy3"

def IsBetween(numMain, numMin, numMax):
    if (not IsInt(numMain)): return False
    return numMain > numMin and numMain <= numMax

def ReplaceNoneData(js, dates = []):
    for j in js: 
        for d in dates:
            if type(j[d]) is datetime:
                j[d] = j[d].strftime(FMT_DT_SPACE) if j[d] else ""
        j = json.loads(str(j).replace("'", '"').replace("None", '""'))
    return js

def CalWR(d):
    if d["runtime"] == 0: return 0
    return d["runtime"] / (d["runtime"] + d["stoptime"]) * 100

def GetThe8am():
    the8 = datetime.today().replace(hour=8, minute=0, second=0)
    if datetime.now() < the8: the8 -= timedelta(days=1)
    return the8 - timedelta(seconds=1)

_config = config.copy()
_config["database"] = 'seikei_analyze'
_cnx = mysql.connector.connect(**_config)
_cursor = _cnx.cursor()
def GetMachineNameLi(tableName, hostNum):
    mach_series = tableName.replace('seikei_', '').replace('3', '').upper()
    query = (f"SELECT mach_name FROM machine_name "
            f"WHERE mach_series LIKE '%{mach_series}%' AND factory = 'F{hostNum}'")
    _cursor.execute(query)
    data = GetCursorToJSON(_cursor)
    goukiLi = []
    for d in data: 
        goukiLi.append(int(d['mach_name'].replace('TMS ', '')))
    # print(goukiLi)
    return goukiLi

def CachePath(cacheName): return f"cache/{cacheName}.json"

date_save_path = f"cache/date_save.json"
def CacheHead(page, tableName, hostNum):
    cacheName = f"{page}_{tableName}_{hostNum}"
    cache_path = CachePath(cacheName)

    cursor.execute(f"SELECT MAX(datadate) AS datadate FROM {tableName}")
    cacheDD = CursorToJSON()[0]["datadate"].strftime(FMT_JSON)
    with open(date_save_path, 'r') as f:
        js = json.loads(f.read())
        if cacheName in js and js[cacheName] == cacheDD:
            with open(cache_path, 'r') as f:
                PrintJSON(json.loads(f.read()))
            return True, js, cacheName, cacheDD
    return False, js, cacheName, cacheDD

def CacheTail(data, js, cacheName, datadate):
    cache_path = CachePath(cacheName)
    with open(cache_path, 'w') as f:
        f.write(json.dumps(data, indent=4, sort_keys=True))
    with open(date_save_path, 'w') as f:
        js[cacheName] = datadate
        json.dump(js, f, indent=4)

def read(data):
    tableName, hostNum = data.split(',')
    isEnd, js, cacheName, cacheDD = CacheHead("map", tableName, hostNum)
    if isEnd: return

    goukiLi = GetMachineNameLi(tableName, hostNum)
    data = []
    for g in goukiLi:
        query = (f"SELECT datadate, gouki, lotno, item, runtime, stoptime "
                f"FROM {tableName} WHERE gouki = {g} ORDER BY datadate DESC LIMIT 1")
        cursor.execute(query)
        item = CursorToJSON()
        for i in item:
            if not i["gouki"]: i["gouki"] = g
            if not i["runtime"]: i["runtime"] = 0
            data.append(i)
        if not item:
            data.append({
                'datadate': '-', 'gouki': g, 'lotno': '-', 
                'item': '-', 'runtime': 0, 'stoptime': '-'
            })

    block_date = GetThe8am()
    # data = [d for d in data if d['datadate'] > block_date]
    for d in data:
        isEmpty = d["datadate"] and \
            (type(d["datadate"]) is not datetime or d["datadate"] < block_date)
        d["id"] = data.index(d)
        d["gouki"] = int(d["gouki"])
        if isEmpty: 
            d["workRate"] = d["workRateNoDec"] = "-"
            d["wrLT95"] = ""
        else:
            wr = CalWR(d)
            d["workRate"] = round(wr, 2)
            d["workRateNoDec"] = str(wr).split(".")[0]
            d["wrLT95"] = "wrLT95" if d["workRate"] < 95 else "wrMT95"

        classNameClt = (
            (not d["item"], "noData"),
            (isEmpty, "noData"),
            (d["workRate"] == 0, "noRun"),
            (IsBetween(d["workRate"], 0, 20), "wr20"),
            (IsBetween(d["workRate"], 20, 40), "wr40"),
            (IsBetween(d["workRate"], 40, 60), "wr60"),
            (IsBetween(d["workRate"], 60, 80), "wr80"),
            (IsBetween(d["workRate"], 80, 99), "wr100"),
            (d["workRate"] == 100, "wrMax"),
            (True, ""))

        for cnCondi, cName in classNameClt:
            if cnCondi: 
                d["className"] = cName
                break
    
    for sot, rev in (("gouki", False),): #, ("isEmpty", True)]:
        data = sorted(data, key=itemgetter(sot), reverse=rev)
    data = ReplaceNoneData(data, ["datadate"])
    PrintJSON(data)
    CacheTail(data, js, cacheName, cacheDD)
    return data

def readWR(data):
    tableName, hostNum = data.split(',')
    isEnd, js, cacheName, cacheDD = CacheHead("wr", tableName, hostNum)
    if isEnd: return

    h = 8
    data = []
    fromDate = GetThe8am()
    maxDate = fromDate + timedelta(days=1)
    datadate = ""
    while fromDate <= maxDate:
        toDate = fromDate + timedelta(hours=2)
        toDateStr = toDate.strftime(FMT_DT_SPACE)
        fromDateStr = fromDate.strftime(FMT_DT_SPACE)
        query = (f"SELECT MAX(datadate) AS datadate, gouki, "
                f"AVG(runtime / (runtime + stoptime) * 100) AS avgWR FROM {tableName} "
                f"WHERE datadate >= '{fromDateStr}' AND datadate < '{toDateStr}' GROUP BY gouki")
        # print(query)
        cursor.execute(query)
        time_block = CursorToJSON()
        for tb in time_block:
            tb["gouki"] = int(tb["gouki"])
            tb["avgWR"] = "{:.2f}".format(float(tb["avgWR"])) if tb["avgWR"] else ""
            tb["timeBlock"] = h
            datadate = tb["datadate"].strftime(FMT_DT_SPACE)
            del tb["datadate"]
        data.append(time_block)
        fromDate = toDate
        h += 2

    def InitMacTimeBlock(dct):
        for mac in range(8, 33, 2): dct[str(mac)] = "-"
        return dct

    clct = {}
    for timeBlock in data:
        for machine in timeBlock:
            g = str(machine["gouki"])
            if g not in clct:
                clct[g] = InitMacTimeBlock({"macNum" : machine["gouki"]})
            if machine["avgWR"] and float(machine["avgWR"]):
                clct[g][str(machine["timeBlock"])] = machine["avgWR"]

    arranged = {
        "datadate" : datadate,
        "machines" : []
    }
    for c in clct: arranged["machines"].append(clct[c])
    goukiLi = GetMachineNameLi(tableName, hostNum)
    arranged["machines"] = list(filter(lambda i: \
        i['macNum'] in goukiLi, arranged["machines"]))

    for g in goukiLi:
        isFound = False
        for m in arranged["machines"]:
            if m['macNum'] == g: 
                isFound = True
                break
        if not isFound:
            arranged["machines"].append(InitMacTimeBlock({"macNum" : g}))
    arranged["machines"] = sorted(arranged["machines"], key=itemgetter("macNum"), reverse=False)
    # print(str(arranged).replace("'", '"'))
    PrintJSON([arranged])
    CacheTail([arranged], js, cacheName, cacheDD)
    
def main(linked):
    if not linked: return
    status, data = linked # linked = [status, data]
    if "normal" in status:
        read(data)
    elif "wr" in status:
        readWR(data)
    cnx.commit()

main(linked)
# main(["normal", "seikei_tms,4"])
# main(["normal", "seikei_zy3,4"])
# main(["normal", "seikei_zy3,6"])
# main(["wr", "seikei_tms,4"])
# main(["wr", "seikei_zy3,4"])
# main(["wr", "seikei_zy3,6"])
