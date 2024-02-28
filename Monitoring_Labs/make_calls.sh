#!/bin/bash
DIR=`pwd`
if [[ -z "$1" ]]; then
    echo "Usage: make_calls.sh forecast|query [NUM] [ZRAY_TOKEN]"
    echo "       forecast|query : Weather forecast | Postcode lookup"
    echo "       NUM : number of calls to make"
    echo "       ZRAY_TOKEN : Copy and paste the Z-Ray token"
    exit 1
else
    export URL="http://zendphp.local/api/$1";
fi
if [[ "$2" != "" ]]; then
    export NUM="$2"
else
    export NUM=10
fi
if [[ "$3" != '' ]]; then
    export URL="$URL?zraytok=$3"
fi
echo "$URL"
for i in $(seq 1 $NUM);
do
    curl -X GET -H 'Accept: application/json' "$URL"
done

