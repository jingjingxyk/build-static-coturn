#!/bin/bash

set -exu
__DIR__=$(
  cd "$(dirname "$0")"
  pwd
)
__PROJECT__=${__DIR__}

cd ${__PROJECT__}

OS=$(uname -s)
ARCH=$(uname -m)

case $OS in
'Linux')
  OS="linux"
  ;;
'Darwin')
  OS="macos"
  ;;
*)
  case $OS in
  'MSYS_NT'* | 'CYGWIN_NT'* )
    OS="windows"
    ;;
  'MINGW64_NT'*)
    OS="windows"
    ;;
  *)
    echo '暂未配置的 OS '
    exit 0
    ;;
  esac
  ;;
esac

case $ARCH in
'x86_64')
  ARCH="x64"
  ;;
'aarch64' | 'arm64' )
  ARCH="arm64"
  ;;
*)
  echo '暂未配置的 ARCH '
  exit 0
  ;;
esac

COTURN_VERSION='4.6.2'
VERSION='v1.1.0'

mkdir -p bin/runtime
mkdir -p var/runtime

cd ${__PROJECT__}/var/runtime

SOCAT_DOWNLOAD_URL="https://github.com/jingjingxyk/build-static-coturn/releases/download/${VERSION}/coturn-${COTURN_VERSION}-${OS}-${ARCH}.tar.xz"
CACERT_DOWNLOAD_URL="https://curl.se/ca/cacert.pem"

if [ $OS = 'windows' ]; then
  SOCAT_DOWNLOAD_URL="https://github.com/jingjingxyk/build-static-coturn/releases/download/${VERSION}/coturn-${COTURN_VERSION}-vs2022-${ARCH}.zip"
fi

MIRROR=''
while [ $# -gt 0 ]; do
  case "$1" in
  --mirror)
    MIRROR="$2"
    ;;
  --proxy)
    export HTTP_PROXY="$2"
    export HTTPS_PROXY="$2"
    NO_PROXY="127.0.0.0/8,10.0.0.0/8,100.64.0.0/10,172.16.0.0/12,192.168.0.0/16"
    NO_PROXY="${NO_PROXY},::1/128,fe80::/10,fd00::/8,ff00::/8"
    NO_PROXY="${NO_PROXY},localhost"
    NO_PROXY="${NO_PROXY},.aliyuncs.com,.aliyun.com,.tencent.com"
    NO_PROXY="${NO_PROXY},.myqcloud.com,.swoole.com"
    export NO_PROXY="${NO_PROXY},.tsinghua.edu.cn,.ustc.edu.cn,.npmmirror.com"
    ;;
  --*)
    echo "Illegal option $1"
    ;;
  esac
  shift $(($# > 0 ? 1 : 0))
done

case "$MIRROR" in
china)
  SOCAT_DOWNLOAD_URL="https://php-cli.jingjingxyk/coturn-${COTURN_VERSION}-${OS}-${ARCH}.tar.xz"
  if [ $OS = 'windows' ]; then
    SOCAT_DOWNLOAD_URL="https://php-cli.jingjingxyk/coturn-${COTURN_VERSION}-vs2022-${ARCH}.zip"
  fi
  ;;

esac

test -f cacert.pem || curl -LSo cacert.pem ${CACERT_DOWNLOAD_URL}

COTURN_RUNTIME="coturn-${COTURN_VERSION}-${OS}-${ARCH}"

if [ $OS = 'windows' ]; then
  {
    COTURN_RUNTIME="coturn-${COTURN_VERSION}-vs2022-${ARCH}"
    test -f ${COTURN_RUNTIME}.zip || curl -LSo ${COTURN_RUNTIME}.zip ${SOCAT_DOWNLOAD_URL}
    test -d ${COTURN_RUNTIME} && rm -rf ${COTURN_RUNTIME}
    unzip "${COTURN_RUNTIME}.zip"
    exit 0
  }
else
  test -f ${COTURN_RUNTIME}.tar.xz || curl -LSo ${COTURN_RUNTIME}.tar.xz ${SOCAT_DOWNLOAD_URL}
  test -f ${COTURN_RUNTIME}.tar || xz -d -k ${COTURN_RUNTIME}.tar.xz
  test -d coturn && rm -rf coturn
  test -d coturn || tar -xvf ${COTURN_RUNTIME}.tar
  chmod a+x coturn/bin/turnserver
  cp -rf ${__PROJECT__}/var/runtime/coturn ${__PROJECT__}/bin/runtime/coturn
fi

cd ${__PROJECT__}/var/runtime

cp -f ${__PROJECT__}/var/runtime/cacert.pem ${__PROJECT__}/bin/runtime/cacert.pem


cd ${__PROJECT__}/

set +x

echo " "
echo " USE COTURN RUNTIME :"
echo " "
echo " export PATH=\"${__PROJECT__}/bin/runtime:\$PATH\" "
echo " "
echo " ./bin/runtime/coturn/bin/turnserver -c ./bin/runtime/coturn/etc/turnserver.conf  "
echo " "
echo " coturn docs :  https://github.com/coturn/coturn "
echo " coturn example :  https://github.com/coturn/coturn"
echo " "
export PATH="${__PROJECT__}/bin/runtime:$PATH"
