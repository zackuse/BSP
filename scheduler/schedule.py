# -*- coding: utf-8 -*-
import click
import os
ROOT_PATH=os.path.abspath(os.path.join(os.path.dirname(__file__),os.pardir))
from plan import Plan

cron = Plan("scripts", path=os.path.join(ROOT_PATH,'scheduler'))
# cron.script('marketstart.py', every='1.day',at='9:00')
# cron.script('marketstat.py', every='15.minute')
# cron.script('task_fenhongdialy.py', every='1.day',at='08:01')
# cron.script('task_fenhongmonthly.py', every='1.month',at='08:10')
# cron.script('task_fenhongmonthly.py', every='monday',at='08:20')

cron.script('updatemarketprice.py', every='5.minute')
cron.script('dayrelease.py', every='1.day',at='00:00')
cron.script('grantstar.py', every='30.minute')

@click.command()
@click.option('--flag', prompt='操作类型',
              help='check 检查,write写入contab,clear清除.')
def hello(flag):
    """游戏定时器."""
    if flag=='check':
        cron.run("check")
        pass
    elif flag=='write':
        cron.run("write")
        pass
    elif flag=='clear':
        cron.run("clear")
        pass
    elif flag=='update':
        cron.run("update")
        pass
    else:
        click.echo(u'check 检查,write写入contab,clear清除.update更新')
        pass
if __name__ == "__main__":
    hello()
