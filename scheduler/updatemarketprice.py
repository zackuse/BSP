#!/usr/bin/env python
# -*- coding: utf-8 -*-
import sys
reload(sys)

import sys, time
import os
reload(sys)
sys.setdefaultencoding('utf-8')
import datetime

ROOT=os.path.dirname(os.path.realpath(__file__))

ISTEST=False

if os.path.exists(os.path.join(ROOT,"../config/dev.toml")):
    ISTEST=True
    pass

PROJ_PATH=os.path.join(ROOT,"..")
PROJ_PATH=os.path.abspath(PROJ_PATH)

PROJ_PATH_OUT=os.path.join(ROOT,"../../centerbin")
PROJ_PATH_OUT=os.path.abspath(PROJ_PATH_OUT)

print PROJ_PATH
print PROJ_PATH_OUT
print ISTEST

if ISTEST:
	os.system("php ../../QYS/src/QYS/QYS.php -p "+PROJ_PATH+"/center -c conf/debug.php -f "+PROJ_PATH+"/center/task/MarketPriceTask.php ")
else:
	os.system('docker exec -d proj bash -c "cd /usr/proj/centerbin/center && php ../../QYS/src/QYS/QYS.php -p  /usr/proj/centerbin/center -c conf/prod.php -f  /usr/proj/centerbin/center/task/MarketPriceTask.php " ')
