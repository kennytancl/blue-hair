from CRUD import *
from datetime import datetime

def PrintDT(fmt):
    print(datetime.now().strftime(fmt), end="")

def main(linked):
    if not linked: return
    status, data = linked # linked = [status, data]
    if 'datetime' == status: PrintDT(FMT_JSON)
    elif 'year' == status: PrintDT(FMT_YEAR)
    elif 'month' == status: PrintDT(FMT_MONTH)
    elif 'day' == status: PrintDT(FMT_DAY)
    elif 'date' == status: PrintDT(FMT_DATE)
    elif 'time' == status: PrintDT(FMT_TIME_24)
    elif 'months' == status: 
        mths = []
        for m in range(1, 13):
            mths.append(datetime.strptime(str(m), '%m').strftime('%b'))
        print(','.join(mths), end="")

main(linked)
# main(linked)
# main("months")