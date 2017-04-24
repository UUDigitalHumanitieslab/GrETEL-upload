#! /bin/sh

## This command is used to start Alpino in server-mode

export ALPINO_HOME=/opt/Alpino
export PORT=7001
export TIMEOUT=600000
export MEMLIMIT=1500M
export TMPDIR=/tmp

PROLOGMAXSIZE=${MEMLIMIT} ${ALPINO_HOME}/bin/Alpino -notk -veryfast user_max=${TIMEOUT}\
 server_kind=parse\
 server_port=${PORT}\
 assume_input_is_tokenized=on\
 debug=0\
 end_hook=xml\
 -init_dict_p\
 batch_command=alpino_server 2> ${TMPDIR}/alpino_server.log &

echo "Alpino started"
