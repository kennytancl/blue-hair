from imp import cnx, cursor, linked, json, Counter, ConvertLi, CursorToJSON, PrintJSON

FMT_BR = '%d %b %Y<br>%I:%M %p'
FMT_COMMA = '%Y-%m-%d,%H:%M'
FMT_COMMA_SEC = '%Y-%m-%d,%H:%M:%S'
FMT_DATE = '%Y-%m-%d'

def read(status, data):
    query = ("SELECT rcd_id, item_name, type, item_type, item_grp, item_lot "
    "FROM hakuri_item_all WHERE active = 1 ORDER BY ")
    data = ConvertLi(data)
    if data and data[0]:
        query += data[0]
        ord = "ASC" if "true" in status else "DESC"
        query += " {}, ".format(ord)
    query += "item_name ASC"
    cursor.execute(query)
    
    data = CursorToJSON()
    itemNames = []
    for d in data: itemNames.append(d["item_name"])

    sameNamesLi = []
    countItemNames = Counter(itemNames)
    for k in countItemNames:
        if countItemNames[k] != 1: sameNamesLi.append(k)

    for d in data:
        d["isUnique"] = 'False' if d["item_name"] in sameNamesLi else 'True'
    PrintJSON(data)

def create(data):
    data = data.replace("'", '"')
    rec_batch = json.loads(data)[0]
    query = ("INSERT INTO hakuri_item_all "
               "(item_name, type, item_type, item_grp, item_lot) "
               "VALUES (%(item_name)s, %(type)s, %(item_type)s, %(item_grp)s, %(item_lot)s)")
    cursor.execute(query, rec_batch)

def delete(data):
    query = ("UPDATE hakuri_item_all SET active = 0 WHERE rcd_id = %s")
    cursor.execute(query, (ConvertLi(data)[0],))

def main(linked):
    if not linked: return
    status, data = linked # linked = [status, data]
    if 'READ' in status: read(status, data)
    elif 'CREATE' in status: create(data)
    elif 'DELETE' in status: delete(data)

    cnx.commit()
    print("")

main(linked)
# main(['READ', '["item_name"]'])
