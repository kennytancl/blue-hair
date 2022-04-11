from imp import mysql, cnx, cursor, datetime
from CRUD import create
from random import *
import string
from datetime import timedelta

if input("""Confirm to reset? All data will be lost !!!
Enter 'y' to confirm : """).lower() != "y":
    exit()

FMT = '%d/%m/%Y %I:%M %p'
END_DT = datetime.now()
START_DT = END_DT - timedelta(days=10)
# START_DT = datetime.strptime('1/1/2022 12:00 AM', FMT)

def RandomStr(length):
    return ''.join(choices(string.ascii_uppercase + string.digits, k=length))

def RandomDT(start = START_DT, end = END_DT):
    delta = end - start
    int_delta = (delta.days * 24 * 60 * 60) + delta.seconds
    random_second = randrange(int_delta)
    return start + timedelta(seconds=random_second)

def main():
    TABLES = {}
    TABLES["batch"] = ("CREATE TABLE IF NOT EXISTS `hakuri_record_batch` ("
        "`rec_batch_id` INT NOT NULL AUTO_INCREMENT,"
        "`machine_no` INT NOT NULL,"
        "`grinding_mins` INT NOT NULL,"
        "`rpm` INT NOT NULL,"
        "`start_dt` DATETIME NOT NULL,"
        "`finish_dt` DATETIME,"
        "`start_pic` VARCHAR(255) NOT NULL,"
        "`end_pic` VARCHAR(255),"
        "PRIMARY KEY (`rec_batch_id`))")

    TABLES["batch_index"] = ("CREATE INDEX search_start_dt ON hakuri_record_batch (start_dt)")

    TABLES["item"] = ("CREATE TABLE IF NOT EXISTS `hakuri_record_item` ("
        "`rec_item_id` INT NOT NULL AUTO_INCREMENT,"
        "`rec_batch_id` INT NOT NULL,"
        "`tank_no` INT NOT NULL,"
        "`item_id` INT NOT NULL,"
        "`lot_no` VARCHAR(255) NOT NULL,"
        "PRIMARY KEY (`rec_item_id`),"
        "CONSTRAINT `fore_item_id` FOREIGN KEY (`item_id`)"
        "REFERENCES `hakuri_item_all` (`rcd_id`) ON DELETE CASCADE,"
        "CONSTRAINT `fore_rec_batch_id` FOREIGN KEY (`rec_batch_id`)"
        "REFERENCES `hakuri_record_batch` (`rec_batch_id`) ON DELETE CASCADE)")

    cursor.execute("DROP TABLE IF EXISTS hakuri_record_item")
    cursor.execute("DROP TABLE IF EXISTS hakuri_record_batch")
    try:
        for table_name in TABLES: cursor.execute(TABLES[table_name])
    except mysql.connector.Error as err:
        print(err.msg)

    linked = '[{"machine_no":"5","rec_items":[{"item_id":"31","tank_no":"5","lot_no":"asca"},{"item_id":"","tank_no":"6","lot_no":""},{"item_id":"","tank_no":"7","lot_no":""},{"item_id":"","tank_no":"8","lot_no":""}],"grinding_mins":"7","rpm":"195","start_pic":"xv bbvbc"}]'

    query = ("UPDATE hakuri_record_batch SET "
    "start_dt = %s WHERE rec_batch_id = %s")
    for id in range(2000):
        batch = {}
        itemLi = []
        batch["machine_no"] = randrange(4, 6);
        isSame = choice([True, False])
        itemID = randrange(1, 186)
        lotNo = RandomStr(10)
        for x in range(1, randrange(2, 6)):
            item = {}
            item["item_id"] = itemID if isSame else randrange(1, 186)
            item["tank_no"] = x if batch["machine_no"] == 4 else x + 4
            item["lot_no"] = lotNo if isSame else RandomStr(10)
            itemLi.append(item)
        batch["rec_items"] = itemLi;
        batch["grinding_mins"] = 7;
        batch["rpm"] = 195;
        batch["start_pic"] = randrange(1000, 99999);
        li = []
        li.append(batch)
        try: create(str(li))
        except: pass        
        cursor.execute(query, (RandomDT(), id + 1))
        cnx.commit()
main()