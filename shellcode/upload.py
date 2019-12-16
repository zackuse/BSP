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


a=datetime.datetime.now().strftime('%Y_%m_%d_%H_%M_%S')
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

    s="""
        chmod 777 build%s.sh
        sh build%s.sh %s
    """%(gamename,gamename,a)
    filename = gamename+"bin_"+a+".tar.gz"
    print s
    os.system(s)
    
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
    cmd="scp -o proxycommand=\"ssh "+ttuser+"@"+ttip+" nc %h %p\" ./out/"+filename+" "+username+"@"+ip+":/home/proj"
    tmpchild=pexpect.spawn(cmd)

    index = tmpchild.expect(["(yes/no)", "password", pexpect.EOF, pexpect.TIMEOUT])
    print index
    if (index==0):
        tmpchild.sendline("yes")
        tmpchild.expect("password")
        tmpchild.sendline(ttpassword)
        index1 = tmpchild.expect(["(yes/no)", "password", pexpect.EOF, pexpect.TIMEOUT])
        if (index1==0):
            tmpchild.sendline("yes")

    elif(index==1):
        tmpchild.sendline(ttpassword)
        index2 = tmpchild.expect(["(yes/no)", "password", pexpect.EOF, pexpect.TIMEOUT])
        if (index2==0):
            tmpchild.sendline("yes")

    tmpchild.sendline(password)
    tmpchild.read()
    print "传输完成"
    
    print "解压缩文件"
    s="""
        cd /home/proj/
        tar -zxf %s
        cd /home/proj/%sbin/%s
        `pwd`
        
    """%(filename,d,name)
    stdin,stdout,stderr = ssh.exec_command(s)

    s="""
        cd /home/proj/%sbin/%s
        chmod 777 logbackup.sh
        python cronlog.py
        cd /home/proj/%sbin/%s/server/3rd/xxtea
        make linux
    """%(d,d,d,d)
    print s
    stdin,stdout,stderr = ssh.exec_command(s)
    print stdout.readlines()

    if d=="phpadmin":
        cmd="""
            cd /home/proj/
            sudo cp -rf phpadminbin/* /var/www/html/unt/
        """
        stdin,stdout,stderr = ssh.exec_command(cmd,get_pty=True)
        stdin.write(password + '\n')
        stdin.flush()
        print stdout.readlines()
        pass


except Exception as e:
    print e
    # raise
else:
    pass
finally:
    pass
