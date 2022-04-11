from imp import cnx, cursor, linked, json, Counter, ConvertLi

class Message:
    def __init__(self, isError, itemIDs = [], error_type = "", errorItems = [], error_start_pic = ""):
        self.isError = isError
        self.itemIDs = itemIDs
        self.msg = f"Saving failed. Item {error_type}"
        self.errorItems = errorItems

        if isError: # Record error count here
            query = ("INSERT INTO hakuri_input_error (error_type, error_items, start_pic)"
                "VALUES (%s, %s, %s)")
            cursor.execute(query, (error_type, str(errorItems), error_start_pic)) 
            cnx.commit()

    def PrintJSON(self):
        j = json.dumps(self, default=lambda o: o.__dict__, sort_keys=True, indent=4)
        print(j)
        return j

def main(linked):
    start_pic, itemNames = linked
    query = ("SELECT rcd_id, item_name, item_grp FROM hakuri_item_all "
        "WHERE item_name = %s AND active = 1")
    
    try: itemNames = ConvertLi(itemNames)
    except: pass

    itDict = {} # item dictionary {'CE UMK107 B7224KA-TR': '1071G', ...}
    itemIDs = []
    notFoundNames = []
    for l in itemNames: # loop list from php
        cursor.execute(query, (l,)) # query database with item name
        isFound = False
        for (rcd_id, item_name, item_grp) in cursor:
            # insert to dictionary (repeated item name will be replaced)
            itDict[item_name] = item_grp
            itemIDs.append(rcd_id)
            isFound = True
        # item not found
        if not isFound: notFoundNames.append(l)
    
    if notFoundNames:
        Message(True, [], "name not found", notFoundNames, start_pic).PrintJSON()
        return False

    itGroup = [] # item group list ['1071G', ...]
    # add item group from dictionary to list
    for itDict_K in itDict:
        itGrp = itDict[itDict_K]
        # only verify if not 'ALL' group
        if itGrp != 'ALL': itGroup.append(itGrp)

    repeatedIts = [] # repeated item name list [['CE EMK107BBJ475KAHT', ...], ...]
    countItGroup = Counter(itGroup) # count item group in list
    for countItGroup_K in countItGroup:
        if countItGroup[countItGroup_K] > 1: # check which item has repetition
            repeatedItTemp = []
            # find group and add name to repeated item name list
            for itKey in itDict:
                if itDict[itKey] == countItGroup_K: repeatedItTemp.append(itKey)
            repeatedIts.append(repeatedItTemp)

    isAllow = True
    for names in repeatedIts:
        if len(names) > 1:
            isAllow = False
            break

    if isAllow: Message(False, itemIDs).PrintJSON()
    else:
        errorItems = []
        for names in repeatedIts:
            for n in names: errorItems.append(n)
        Message(True, [], "group repeated", errorItems, start_pic).PrintJSON()

    return isAllow
    # cursor.close()
    # cnx.close()

if len(linked) > 0:
    main(linked)

# print("{}, {}, {} <br>".format(rcd_id, item_name, item_grp))
