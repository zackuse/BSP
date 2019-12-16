#!/bin/sh
echo "打包server"

luajit=/usr/local/openresty/luajit/bin/luajit
if [ ! -d /usr/local/openresty/luajit/ ]; then
    #statements
    # echo "not exist"
    luajit=/usr/local/opt/openresty/luajit/bin/luajit
fi

if [ ! -d ./out ]; then
    #statements
    # echo "not exist"
    mkdir -p ./out
fi

a=$1

cur=`pwd`

cd $cur

rm -rf $cur/center
mkdir -p $cur/center
mkdir -p $cur/center/config
mkdir -p $cur/center/center
mkdir -p $cur/center/globalunit
mkdir -p $cur/center/globalunit/logic
mkdir -p $cur/center/globalunit/model
mkdir -p $cur/center/globalunit/utils
mkdir -p $cur/center/globalunit/validator
mkdir -p $cur/center/globalunit/validator/adapter
mkdir -p $cur/center/scheduler
mkdir -p $cur/center/scheduler/sql

function compile() {
    for file in $1
    do
        echo $file
        filepath=`dirname $file`
        subpath=${filepath:2}
        filename=`basename $file`

        echo $filepath
        echo $subpath
        echo $filename

        if [ ! -d $2/$subpath/ ]; then
            #statements
            # echo "not exist"
            mkdir -p $2/$subpath/
        fi
        if test -f $file
        then
            $luajit -b $file $2/$subpath/$filename
        fi
    done
}

function copy() {
    echo $1
    echo $2
    for file in $1
    do
        echo $file
        filepath=`dirname $file`
        subpath=${filepath:2}
        filename=`basename $file`

        echo $filepath
        echo $subpath
        echo $filename

        if [ ! -d $2/$subpath/ ]; then
            #statements
            # echo "not exist"
            mkdir -p $2/$subpath/
        fi
        if test -f $file
        then
            # $luajit -b $file ./gamebin/$subpath/$filename
            if [ "${filename##*.}" != "so" ]; then
                cp $file $2/$subpath/$filename
            fi
        fi
    done
}

cp ../globalunit/logic/* $cur/center/globalunit/logic
cp ../globalunit/model/* $cur/center/globalunit/model
cp ../globalunit/validator/* $cur/center/globalunit/validator
cp ../globalunit/utils/* $cur/center/globalunit/utils
cp ../globalunit/validator/adapter/* $cur/center/globalunit/validator/adapter

cp ../scheduler/* $cur/center/scheduler
cp ../scheduler/sql/* $cur/center/scheduler/sql

cp $cur/../config/prod.toml $cur/center/config

mkdir $cur/center/center/log

copy "../center/*.sh" "$cur/center"
copy "../center/conf/*" "$cur/center"
copy "../center/handler/*" "$cur/center"
copy "../center/logic/*" "$cur/center"
copy "../center/model/*" "$cur/center"
copy "../center/callback/*" "$cur/center"
copy "../center/scripts/*" "$cur/center"
copy "../center/task/*" "$cur/center"
copy "../center/utils/*" "$cur/center"
copy "../center/utils/aliyun-oss-php-sdk-2.3.0/*" "$cur/center/aliyun-oss-php-sdk-2.3.0"


mv ./center ./centerbin
tar -czvf ./out/centerbin_$a.tar.gz ./centerbin
rm -rf ./centerbin
























