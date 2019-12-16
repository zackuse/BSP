# -*- coding: utf-8 -*-

import re

try:
    file = open('dist/manifest.json', "r")
    myStr = file.read()
    obj = re.sub("/\*.*?\*/", "", myStr)
    file.close()
    file = open('dist/manifest.json', "w+")
    file.write(obj)
    print('删除成功！！！')
except:
    print('报错误啦！！！')
finally:
    if file:
        file.close()
