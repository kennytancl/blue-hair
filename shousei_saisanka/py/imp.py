from __future__ import print_function
from datetime import date, datetime, timedelta
import json
import sys
# import pymysql
import mysql.connector
# from mysql.connector import errorcode
from collections import Counter

DB_NAME = 'shousei_saisanka_csheet'
config = {
    'user': 'root',
    'host': '127.0.0.1',
    'database': DB_NAME
}

cnx = mysql.connector.connect(**config)
cursor = cnx.cursor()

def ConvertLi(s):
    try: return s.replace('[','').replace(']','').split(",")
    except: return []

def PrintJSON(js):
    print(str(js).replace("'", '"').replace("None", '""').replace("NULL", ''))

def CursorToJSON(cursor = cursor):
    r = [dict((cursor.description[i][0], value) \
            for i, value in enumerate(row)) for row in cursor.fetchall()]
    return (r if r else "")

linked = ""
if len(sys.argv) > 1:
    linked = [sys.argv[1], sys.argv[2]]