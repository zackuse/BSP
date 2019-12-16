# -*- coding: utf-8 -*-
import paramiko
import os
import datetime
import ConfigParser
from paramiko import SSHClient, SSHConfig, SSHException
import pexpect
from pexpect import *
import time
import sys,re
import subprocess
import commands

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


#直连
def getSSHConnection1(tip,tport,tname,tpwd):
    # setup SSH client
    client = SSHClient()
    client.load_system_host_keys()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())

    #Setup the SSH connection
    try:
        client.connect(tip,tport, username=tname, password=tpwd)
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
    gamename = config.get(name,'gamename')

    print '通过跳转服务器连接目标服务器，输入跳转服务器连接密码'
    ssh = getSSHConnection(ttip,ip,port,username,password)
    print "连接成功"

    print "目标服务器创建目录"
    s="""
        cd /home
        mkdir proj
    """
    stdin,stdout,stderr = ssh.exec_command(s)

    print "本地调用scp通过nc代理服务器把文件传到目标服务器"
    cmd="scp -o proxycommand=\"ssh "+ttuser+"@"+ttip+" nc %h %p\" QYS.zip"+" "+username+"@"+ip+":/home/proj"

    print cmd
    tmpchild=pexpect.spawn(cmd)
    tmpchild.expect("password")
    tmpchild.sendline(ttpassword)
    tmpchild.expect("password")
    tmpchild.sendline(password)
    tmpchild.read()
    print "传输完成"
    pass
except Exception as e:
    print e
    # raise
else:
    pass
finally:
    pass
