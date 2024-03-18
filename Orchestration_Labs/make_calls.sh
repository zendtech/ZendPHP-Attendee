#!/bin/bash
DIR=`pwd`
if [[ -z "$1" ]]; then
    echo "Usage: make_calls.sh 1|2 forecast|query|prime [NUM] [ZRAY_TOKEN]"
    echo "       1 | 2 : zendphp1 or zendphp2 container"
    echo "       forecast|query|prime : Weather forecast | Postcode lookup | Prime number generation"
    echo "       NUM : number of calls to make"
    echo "       ZRAY_TOKEN : Copy and paste the Z-Ray token"
    exit 1
else
    export URL=http://zendphp"$1".local/api/"$2";
fi
if [[ "$3" != "" ]]; then
    export NUM="$3"
else
    export NUM=10
fi
if [[ "$4" != '' ]]; then
    export URL="$URL?zraytok=$4"
fi
echo "$URL"
for i in $(seq 1 $NUM);
do
    curl -X GET -H 'Accept: application/json' "$URL"
done

