from __future__ import print_function
from datetime import date, datetime, timedelta
import json
import sys
import mysql.connector
from mysql.connector import errorcode
from collections import Counter
from collections import OrderedDict

USER = "root"
HOST = "localhost"
PASSWORD = ""
DB_NAME = "cuts"

config = {
    'user': 'root',
    'password': 'MariaDB123',
    'host': '127.0.0.1',
    'database': 'cuts'
}

# cnx = mysql.connector.connect(**config)
cnx = mysql.connector.connect(user=USER, host=HOST, database=DB_NAME)
cursor = cnx.cursor()

def ConvertLi(s):
    try: return s.replace('[','').replace(']','').split(",")
    except: return []

def PrintJSON(js):
    print(str(js).replace("'", '"').replace("None", '""'))
     
def CursorToJSON():
    r = [dict((cursor.description[i][0], value) \
               for i, value in enumerate(row)) for row in cursor.fetchall()]
    return (r if r else "")
    
linked = ""
if len(sys.argv) > 1:
    linked = [sys.argv[1], sys.argv[2]]
