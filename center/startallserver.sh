#!/bin/sh
docker exec -d proj bash -c  "cd /usr/proj/centerbin/center/ && ./startcenterprod.sh"
docker exec -d proj bash -c  "cd /usr/proj/centerbin/center/ && ./startqueue.sh"
docker exec -d proj bash -c  "cd /usr/proj/centerbin/center/ && ./startbean.sh"
docker exec -d proj bash -c  "cd /usr/proj/centerbin/center/ && ./startlog.sh"