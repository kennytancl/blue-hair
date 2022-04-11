from imp import cnx, cursor
from CRUD import FmtInsCol, FmtInsVal

def SetAdd(data, num, machtype, factory):
    data.append({
        "num" : num,
        "machno" : f"{machtype}#{num}",
        "machtype" : machtype,
        "factory" : factory
    })
    return data

fieldLi = ["num", "machno", "machtype", "factory"]
query = f"INSERT INTO `machines` ({FmtInsCol(fieldLi)}) VALUES ({FmtInsVal(fieldLi)})"

data = []
""" Factory 7 """
F = 7
for x in range(1, 17):
    data = SetAdd(data, x, "MRS", F)

data = SetAdd(data, 9, "N2", F)
data = SetAdd(data, 1, "N2", F)

for x in range(1, 7):
    if x != 2: data = SetAdd(data, x, "QHS", F)

data = SetAdd(data, 8, "O2", F)

for x in range(3, 5):
    data = SetAdd(data, x, "QSS", F)

""" Factory 5 """
F = 5
for x in range(3, 7):
    data = SetAdd(data, x, "N2", F)
for x in range(3, 7):
    if x != 5: data = SetAdd(data, x, "O2", F)

data = SetAdd(data, 2, "QHS", F)

for x in range(1, 3):
    data = SetAdd(data, x, "QSS", F)

data = SetAdd(data, 4, "AS", F)

for x in range(9, 11):
    data = SetAdd(data, x, "SHO", F)
for x in range(6, 8):
    data = SetAdd(data, x, "ANN", F)

# print(data)
cursor.execute("DROP TABLE machines")
cursor.execute("""CREATE TABLE machines (
    machno CHAR(20) NOT NULL PRIMARY KEY,
    num INT NOT NULL,
    machtype CHAR(20) NOT NULL,
    factory INT NOT NULL
)""")
for d in data:
    cursor.execute(query, d)
    cnx.commit()
