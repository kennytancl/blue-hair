
class Item:
    def __init__(self, itemName = "", lotNo = ""):
        self.itemName = itemName
        self.lotNo = lotNo
        
    def __eq__(self, other):
        return self.itemName == other.itemName and self.lotNo == other.lotNo

ori = Item()
other = Item(1, 2)
# other.setAll(1,2)

print(ori == other)