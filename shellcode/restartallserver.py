# -*- coding: utf-8 -*-
import paramiko
import os
import datetime
import ConfigParser
from paramiko import SSHClient, SSHConfig, SSHException
config = ConfigParser.RawConfigParser()

ROOT=os.path.dirname(os.path.realpath(__file__))

config.read(os.path.join(ROOT,"../config/server.ini"))

print ROOT,"../config/server.ini"

#跳转服务器信息
ttip='116.206.179.102'
ttport='22'
ttuser='root'
ttpassword='oopQvbaP'

#跳转连接       
def getSSHConnection(ttip,ip,prot,name,pwd):

    # setup SSH client
    client = SSHClient()
    client.load_system_host_keys()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())

    # #Check for proxy settings
    try:
        proxy = paramiko.ProxyCommand("ssh -o StrictHostKeyChecking=no "+ttuser+"@"+ttip+" nc "+ip+" "+ttport)
    except:
        print 1
    #Setup the SSH connection
    try:
        client.connect(ip,prot, username=name, password=pwd, sock=proxy)
    except SSHException, ex:
        print ex

    return client

#生成包名
a=datetime.datetime.now().strftime('%Y_%m_%d_%H_%M_%S')

try:
    name = raw_input("请输入要重启的服务器代号(具体的参见config目录下的server.ini:")

    print name
    ip=config.get(name,'ip')
    port=config.getint(name,'port')
    username=config.get(name,'user')
    password=config.get(name,'password')

    d=config.get(name,'framework')

    s="""
        docker stop proj
        docker start proj
        cd /home/proj/centerbin/center
        sudo chmod 777 startallserver.sh 
        sudo chmod 777 startbean.sh 
        sudo chmod 777 startlog.sh 
        sudo chmod 777 startqueue.sh 
        sudo sh startallserver.sh
    """

    print '通过跳转服务器连接目标服务器，输入跳转服务器连接密码'
    ssh = getSSHConnection(ttip,ip,port,username,password)
    print "连接成功"

    ok = raw_input("请输入要重启的服务器:确定在服务器%s上执行如下脚本%s"%(ip,s))
    if ok=='ok':
        stdin,stdout,stderr = ssh.exec_command(s)
        print stdout.readlines()
        pass
    else:
        pass
    ssh.close()
    
except Exception as e:
    print e
    # raise
else:
    pass
finally:
    pass
