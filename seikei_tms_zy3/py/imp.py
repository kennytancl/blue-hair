from __future__ import print_function
from datetime import date, datetime, timedelta
import json
import sys
import mysql.connector
from mysql.connector import errorcode
from collections import Counter

FMT_DATE = '%Y-%m-%d'
FMT_MONTH = '%b'
FMT_YEAR = '%Y'
FMT_DAY = '%d'
FMT_JSON = '%Y-%m-%dT%H:%M'
FMT_BR = '%d %b %Y<br>%H:%M'
FMT_DATE_TXT = '%d %b %Y'
FMT_TIME_24 = '%H:%M'
FMT_TIME_24_S = '%H:%M:%S'
FMT_TIME_APM = '%I:%M %p'
FMT_DT_SPACE = f'{FMT_DATE} {FMT_TIME_24_S}'
FMT_COMMA_APM = f'{FMT_DATE_TXT}, {FMT_TIME_APM}'
FMT_COMMA = f'{FMT_DATE},{FMT_TIME_24}'

config = {
    'user': 'smartE',
    'password': 'Ch3m1str3#94',
    'host': '192.168.166.3',
    'database': 'msty_hatteishi'
}

cnx = mysql.connector.connect(**config)
cursor = cnx.cursor()

def ConvertLi(s):
    try: return s.replace('[','').replace(']','').split(",")
    except: return []

def PrintJSON(js):
    print(json.dumps(js, indent=4, sort_keys=True))

def GetCursorToJSON(cursor):
    return [dict((cursor.description[i][0], value) \
            for i, value in enumerate(row)) for row in cursor.fetchall()]

def CursorToJSON():
    r = GetCursorToJSON(cursor)
    return (r if r else "")

def IsInt(str):
    try:
        int(str)
        return True
    except ValueError:
        return False

linked = ""
if len(sys.argv) > 1:
    linked = [sys.argv[1], sys.argv[2]]
