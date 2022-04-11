from imp import DB_NAME, mysql, cnx, cursor, datetime
from random import *
from datetime import timedelta
from CRUD import create, NULL, GetAllTpn, GetMachines
import string

if input("""Confirm to reset? All data will be lost !!!
Enter 'y' to confirm : """).lower() != "y":
    exit()

FMT = '%d/%m/%Y %I:%M %p'
# START_DT = datetime.strptime('1/1/2022 12:00 AM', FMT)
# END_DT = datetime.strptime('15/1/2022 11:59 PM', FMT)
END_DT = datetime.now()
START_DT = END_DT - timedelta(days=180)

def RandomStr(length):
    return ''.join(choices(string.ascii_uppercase + string.digits, k=length))

def RandomDT(start = START_DT, end = END_DT):
    delta = end - start
    int_delta = (delta.days * 24 * 60 * 60) + delta.seconds
    random_second = randrange(int_delta)
    return start + timedelta(seconds=random_second)

INDEX_COL = ["furnace_no", "start_dt", "input_dt", "inspection_dt"]
COMMAND = {}
# COMMAND["dbDrop"] = "DROP DATABASE IF EXISTS " + DB_NAME
# COMMAND["dbCreate"] = "CREATE DATABASE " + DB_NAME
COMMAND["dbUse"] = "USE " + DB_NAME
CRE_TABLE = "CREATE TABLE IF NOT EXISTS"
QUERY_TAIL = "PRIMARY KEY (`id`))"

TABLE_NAMES = ["n2_saisanka", "o2_saisanka", 
    "o2_fast_cooling", "qh_furnace", "o2_low_temperature", 
    "mrs", "qs_furnace"]

for tbl in TABLE_NAMES:
    COMMAND[f"tbl_drop_{tbl}"] = f"DROP TABLE {tbl}"

query_common = ("{} `{}` ("
    "`id` INT NOT NULL AUTO_INCREMENT,"
    "`furnace_no` CHAR(20) NOT NULL,"
    "{}`ticket_no` INT NOT NULL,"
    "`item_name` VARCHAR(255) NOT NULL,"
    "`lot_no` VARCHAR(255) NOT NULL,"
    "`lot_size` INT NOT NULL,"
    "`chip_layer` VARCHAR(20) NOT NULL,"
    "`input_dt` DATETIME NOT NULL,"
    "`output_dt` DATETIME,"
    "`narabe_quantity` INT NOT NULL,"
    "`narabe_pic` INT NOT NULL,"
    "`collect_pic` INT," + QUERY_TAIL)

for name in TABLE_NAMES:
    if name is TABLE_NAMES[0]: additional = "`o2_free` INT(1) DEFAULT 0,"
    else: additional = (
        "`setter_pilling` VARCHAR(255) NOT NULL,"
        "`confirmation` INT(1) DEFAULT 0,")

    COMMAND["t_" + name] = query_common.format(CRE_TABLE, name, additional)
    for index in INDEX_COL:
        COMMAND["ind_{}_{}".format(index, name)] = "CREATE INDEX search_{} ON {} ({})".format(index, name, index)

COMMAND["t_mrs"] = ("{} `mrs` ("
    "`id` INT NOT NULL AUTO_INCREMENT,"
    "`furnace_no` CHAR(20) NOT NULL,"
    "`start_dt` DATETIME NOT NULL,"
    "`end_dt` DATETIME,"
    "`item_name` VARCHAR(255) NOT NULL,"
    "`key_no` VARCHAR(255) NOT NULL,"
    "`lot_no` VARCHAR(255) NOT NULL,"
    "`lot_size` INT NOT NULL,"
    "`quantity_plan` INT NOT NULL,"
    "`quantity_actual` INT,"
    "`pattern_no` VARCHAR(50) NOT NULL,"
    "`quantity_stick` INT,"
    "`narabe_pic` INT NOT NULL,"
    "`collect_pic` INT,"
    "`grp` VARCHAR(50) NOT NULL," + QUERY_TAIL).format(CRE_TABLE, "mrs")

COMMAND["t_qs_furnace"] = ("{} `qs_furnace` ("
    "`id` INT NOT NULL AUTO_INCREMENT,"
    "`furnace_no` CHAR(20) NOT NULL,"
    "`inspection_dt` DATETIME NOT NULL,"
    "`item_type` VARCHAR(255) NOT NULL,"
    "`lot_no` VARCHAR(255) NOT NULL,"
    "`lot_quantity` INT NOT NULL,"
    "`item` VARCHAR(20) NOT NULL,"
    "`slit` VARCHAR(20) NOT NULL,"
    "`slit_nfmd` INT(1) DEFAULT 0,"
    "`temp` INT NOT NULL,"
    "`tube_nfmd` INT(1) DEFAULT 0,"
    "`feeder_nfmd` INT(1) DEFAULT 0,"
    "`tray_nfmd` INT(1) DEFAULT 0,"
    "`done_by` INT,"
    "`checked_by` INT,"
    "`verified_by` INT," 
    "`remark` VARCHAR(255)," + QUERY_TAIL).format(CRE_TABLE, "qs_furnace")

for table_name in COMMAND: 
    try: cursor.execute(COMMAND[table_name])
    except mysql.connector.Error as err: pass
        # print(err.msg)

#  {'item_name': 'ads', 'lot_no': 'ssssssssssads', 'lot_size': 'ads', 'setter_pilling': 'qqw', 'input_date': '2022-01-05', 'output_date': '', 'chip_layer': 'CC', 'input_time': '16:36', 'output_time': '', 'narabe_quantity': '234', 'narabe_pic': '1231232', 'collect_pic': '234', 'submission': '', 'input_dt': '2022-01-05T16:36', 'output_dt': 'NULL'}
all_items = GetAllTpn()
MAX_RANGE = 3000
for tbl in TABLE_NAMES:
    for id in range(MAX_RANGE):
        # if not id % 100:
        #     print("{:.2f} %".format(id / MAX_RANGE * 100), end="\r")
        # print(id)
        created = False
        while not created:
            main = {
                "furnace_no" : choice(GetMachines(tbl, False)), #  randrange(1, 4)
                "ticket_no" : randrange(1, 100),
                "item_name" : choice(all_items),
                "lot_no" : RandomStr(10),
                "lot_size" : randrange(1000000, 9999999),
                "input_dt" : RandomDT(),
                # "output_dt" : RandomDT(),
                "chip_layer" : choice(["CC", "1 Layer"]),
                "narabe_quantity" : randrange(50, 100),
                "narabe_pic" : randrange(1000, 9999),
                # "collect_pic" : randrange(1000, 9999),
                "setter_pilling" : RandomStr(3),
                "confirmation" : randrange(0, 2),
                # n2_saisanka
                "o2_free" : randrange(0, 2),
                # mrs
                "start_dt" : RandomDT(),
                # "end_dt" : RandomDT(),
                "key_no" : RandomStr(5),
                "quantity_plan" : randrange(50, 100),
                # "quantity_actual" : randrange(50, 100),
                # "quantity_stick" : choice([NULL, NULL, randrange(0, 20)]),
                "quantity_stick" : NULL,
                "pattern_no" : RandomStr(4),
                "grp" : RandomStr(5),
                # qs_furnace
                "inspection_dt" : RandomDT(),
                "item_type" : choice(all_items),
                "lot_quantity" :  randrange(50, 100),
                "item" : choice(["063", "105"]),
                "slit" : choice(["G-H4", "G-H6", "SW4"]),
                "slit_nfmd" : randrange(0, 2),
                "temp" : randrange(0, 1000),
                "tube_nfmd" : randrange(0, 2),
                "feeder_nfmd" : randrange(0, 2),
                "tray_nfmd" : randrange(0, 2),
                "done_by" : randrange(1000, 99999),
                "checked_by" : randrange(1000, 99999),
                "verified_by" : randrange(1000, 99999),
                "remark" : RandomStr(5)
            }        
            li = []
            li.append(main)

            created = create(tbl, main, isReset=True)
        cnx.commit()
