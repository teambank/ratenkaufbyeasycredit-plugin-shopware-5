#!/bin/bash

DIR="$(cd "$(dirname "$0")"; pwd)";
cd $DIR/..

if compgen -G "./build/*" > /dev/null; then
  rm -r ./build/*
fi
mkdir -p ./build/Frontend/NetzkollektivEasyCredit

cp -r ./src/Frontend/NetzkollektivEasyCredit/* ./build/Frontend/NetzkollektivEasyCredit/
cp -r ./merchant-interface/dist/* ./build/Frontend/NetzkollektivEasyCredit/Views/backend/_resources/merchant/

version=`php -r "class Shopware_Components_Plugin_Bootstrap {}; require_once 'src/Frontend/NetzkollektivEasyCredit/Bootstrap.php'; echo \Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Bootstrap::getVersion();"`

(cd ./build && zip -r - *) > dist/easycredit-shopware-$version.zip
