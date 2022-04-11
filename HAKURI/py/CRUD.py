from imp import cnx, cursor, linked, json, datetime, ConvertLi, CursorToJSON, PrintJSON

FMT_BR = '%d %b %Y<br>%H:%M'
FMT_COMMA = '%Y-%m-%d,%H:%M'
FMT_COMMA_SEC = '%Y-%m-%d,%H:%M:%S'
FMT_DATE = '%Y-%m-%d'

INSERT_REC_ITEM = ("INSERT INTO hakuri_record_item "
            "(rec_batch_id, tank_no, item_id, lot_no) "
            "VALUES (%(rec_batch_id)s, %(tank_no)s, %(item_id)s, %(lot_no)s)")

def DTNow():
    return datetime.now().strftime('%Y-%m-%d %H:%M:00')

def CheckInt(str):
    try:
        int(str)
        return True
    except ValueError:
        return False

def read(status, data):
    data = ConvertLi(data)[0]
    data = None if data == 'None' else data

    query = ("SELECT rec_batch_id, machine_no, grinding_mins, "
    "rpm, start_dt, finish_dt, start_pic, end_pic "
    "FROM hakuri_record_batch ")
    if data:
        query += "WHERE "
        if 'id' in status:
            query += "rec_batch_id = %s "
        elif 'start_dt' in status:
            query += ("DATE(start_dt) = %s "
            "ORDER BY start_dt DESC, rec_batch_id DESC")
            data = datetime.strptime(data, FMT_DATE)
        cursor.execute(query, (data,))
    batch = CursorToJSON()

    query = ("SELECT rec_batch_id, tank_no, rcd_id, item_name, lot_no "
    "FROM hakuri_record_item AS I NATURAL JOIN hakuri_item_all AS N "
    "WHERE I.item_id = N.rcd_id ORDER BY tank_no")
    cursor.execute(query)
    item = CursorToJSON()

    for b in batch:
        queryById = data and 'id' in status
        fmt = FMT_COMMA if queryById else FMT_BR
        b["start_dt"] = b["start_dt"].strftime(fmt)
        if b["finish_dt"]:
            b["finish_dt"] = b["finish_dt"].strftime(fmt)
        elif queryById:
            b["finish_dt"] = ','
        items = []
        for i in item:
            if i["rec_batch_id"] == b["rec_batch_id"]:
                items.append(i)
        b["items"] = items

    PrintJSON(batch)
    return batch

def create(linked):
    # linked = '[{"machine_no":"5","rec_items":[{"item_id":"31","tank_no":"5","lot_no":"asca"},{"item_id":"","tank_no":"6","lot_no":""},{"item_id":"","tank_no":"7","lot_no":""},{"item_id":"","tank_no":"8","lot_no":""}],"grinding_mins":"7","rpm":"195","start_pic":"xv bbvbc"}]'
    linked = linked.replace("'", '"')
    rec_batch = json.loads(linked)[0]
    query = ("INSERT INTO hakuri_record_batch "
               "(machine_no, grinding_mins, rpm, start_dt, start_pic) "
               "VALUES (%(machine_no)s, %(grinding_mins)s, %(rpm)s, %(start_dt)s, %(start_pic)s)")
    rec_item = rec_batch.pop("rec_items")
    rec_batch['start_dt'] = DTNow()
    cursor.execute(query, rec_batch)

    query = INSERT_REC_ITEM
    batch_id = cursor.lastrowid
    for data in rec_item:
        if data['item_id']: 
            data['rec_batch_id'] = batch_id
            cursor.execute(query, data)

def delete(linked):
    query = ("DELETE FROM hakuri_record_batch WHERE rec_batch_id = %s")
    cursor.execute(query, (ConvertLi(linked)[0],))

def update(status, data):
    if 'end_pic' in status:
        endPic, id = ConvertLi(data)
        query = ("UPDATE hakuri_record_batch "
            "SET end_pic = %s, finish_dt = %s "
            "WHERE rec_batch_id = %s")
        cursor.execute(query, (endPic, DTNow(), id))
    elif 'all' in status:
        query = ("UPDATE hakuri_record_batch SET "
        "machine_no = %(machine_no)s, "
        "grinding_mins = %(grinding_mins)s, "
        "rpm = %(rpm)s, "
        "start_dt = %(start_dt)s, "
        "finish_dt = %(finish_dt)s, "
        "start_pic = %(start_pic)s, "
        "end_pic = %(end_pic)s "
        "WHERE rec_batch_id = %(rec_batch_id)s")
        batch = json.loads(data)[0]
        print("d", batch["start_dt"])
        if batch["start_dt"] != "," and batch["start_dt"]:
            batch["start_dt"] = datetime.strptime(batch["start_dt"], FMT_COMMA_SEC)
        else:
            batch["start_dt"] = None
        if batch["finish_dt"] != "," and batch["finish_dt"]:
            batch["finish_dt"] = datetime.strptime(batch["finish_dt"], FMT_COMMA_SEC)
        else:
            batch["finish_dt"] = None
        items = batch.pop("rec_items")
        cursor.execute(query, batch)
        query = ("DELETE FROM hakuri_record_item WHERE rec_batch_id = %s")
        batch_id = batch["rec_batch_id"]
        cursor.execute(query, (batch_id,))
        query = INSERT_REC_ITEM
        for i in items:
            if i["item_id"] and i["lot_no"]:
                i["rec_batch_id"] = batch_id
                cursor.execute(query, i)
    
def main(linked):
    if not linked: return
    status, data = linked # linked = [status, data]
    if 'READ' in status: read(status, data)
    elif 'CREATE' in status: create(data)
    elif 'DELETE' in status: delete(data)
    elif 'UPDATE' in status: update(status, data)

    cnx.commit()
    print("")

main(linked)
# main(['UPDATEall', '[{"machine_no":"5","rec_items":[{"item_id":82,"tank_no":"5","lot_no":"42H54PZDK1","item_name":"RM AMK105AC6475MVEM4"},{"item_id":125,"tank_no":"6","lot_no":"H3JMWP17GL","item_name":"CE LMK107 BJ335KA-TI"},{"item_id":32,"tank_no":"7","lot_no":"ssss","item_name":"CE LMK212 BJ106KG-TI"},{"item_id":32,"tank_no":"8","lot_no":"111","item_name":"CE LMK212 BJ106KG-TI"}],"grinding_mins":"7","rpm":"195","start_pic":"PVR2JZG0NN1Gs","rec_batch_id":"58","start_dt":"2022-01-03,15:07:00:00","finish_dt":",","end_pic":""}]'])