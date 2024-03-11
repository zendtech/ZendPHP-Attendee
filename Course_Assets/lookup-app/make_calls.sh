#!/bin/bash
# Usage: make_calls.sh [IP_ADDRESS] [ZRAY_TOKEN]
export NUM=100
export IP_ADDR=10.10.60.10
export ZRAY_TOK=""
if [[ -f $2 ]]; then
    export ZRAY_TOK=$2
    export IP_ADDR=$1
else
    if [[ -f $1 ]]; then
        export IP_ADDR=$1
    fi
fi
export URL="http://$IP_ADDR?rand=1&zraytok=$ZRAY_TOK"
for i in $(seq 1 $NUM);
do
    curl -X GET -H 'Accept: application/json' "$URL"
done

