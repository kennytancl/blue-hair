from CRUD import *
from datetime import datetime

print(datetime.now().strftime(FMT_DATE).replace("\n", ""), end="")