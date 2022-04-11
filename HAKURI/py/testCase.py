import verify as v
import unittest

class TestInput(unittest.TestCase):

    def test_split(self):
        s = 'hello world'
        self.assertEqual(s.split(), ['hello', 'world'])
        # check that s.split fails when the separator is not a string
        with self.assertRaises(TypeError):
            s.split(2)

    def test_input(self):
        A_1071A = 'CE EMK107BBJ475KAHT'
        B_1071A = 'CE JMK107 BJ475KASTP'

        A_1071B = 'CE LDK107BBJ106MKLT'

        O2121C = {}
        O2121C["A"] = 'CE LMK212 BJ106KG-T'
        O2121C["B"] = 'CE LMK212 BJ106KG-TI'
        O2121C["C"] = 'CE LMK212 BJ106KGBT'
        O2121C["D"] = 'CE LMK212 BJ106KGMT'

        O2121E = {}
        O2121E["A"] = 'CE LMK212ABJ106KD-TI'
        O2121E["B"] = 'CE LMK212 BJ106KDST'
        O2121E["C"] = 'CE LMK212 BJ106KDHT'
        O2121E["D"] = 'CE EMK212ABJ106KD-TI'

        ALL = {}
        ALL["A"] = 'CE EDK107 BJ225KANL'
        ALL["B"] = 'CE JMK212ABJ226MD-T'
        ALL["C"] = 'RM JMK063ABJ105MP-F'
        ALL["D"] = 'RM EMK105 B7224KVAF'

        group = [O2121C, O2121E]

        all = [
            A_1071A, B_1071A, 
            A_1071B 
        ]

        for a in O2121C: all.append(O2121C[a])
        for a in O2121E: all.append(O2121E[a])
    
        for a in all:
            self.assertTrue(v.main([a]))
            self.assertTrue(v.main([a, a]))
            self.assertTrue(v.main([a, a, a]))
            self.assertTrue(v.main([a, a, a, a]))
    
        self.assertTrue(v.main([A_1071A, A_1071B]))
        self.assertTrue(v.main([A_1071A, A_1071B, A_1071B]))
        self.assertTrue(v.main([A_1071A, A_1071B, A_1071B, A_1071B]))

        self.assertFalse(v.main([A_1071A, B_1071A]))
        self.assertFalse(v.main([A_1071A, A_1071A, B_1071A]))
        self.assertFalse(v.main([A_1071A, A_1071A, B_1071A, B_1071A]))
        self.assertFalse(v.main([A_1071A, A_1071A, A_1071A, B_1071A]))

        for d in group:
            self.assertFalse(v.main([d["A"], d["B"]]))
            self.assertFalse(v.main([d["A"], d["B"]]))
            self.assertFalse(v.main([d["A"], d["B"], d["C"]]))
            self.assertFalse(v.main([d["A"], d["B"], d["C"], d["D"]]))
            
if __name__ == '__main__':
    unittest.main()

# A_2121A = 'CE EMK212ABJ106KG-TI'
# B_2121A = 'CE EMK212ABJ106KG-T'
# C_2121A = 'CE EMK212 BJ106KG-T'
# D_2121A = 'CE EMK212ABJ106KGMT'