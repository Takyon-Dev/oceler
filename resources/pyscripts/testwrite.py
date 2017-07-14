import os
cwd = os.getcwd()
print(cwd)
f = open('../resources/pyscripts/testfile.txt','w')
f.write('hi there\n')
f.write('hi there') # same result as previous line ?????????
f.close()
