rm -r ./build/*

mkdir -p ./build/Frontend/NetzkollektivEasyCredit/Library/Netzkollektiv/EasyCreditApi

cp -r ./src/Frontend/NetzkollektivEasyCredit/* ./build/Frontend/NetzkollektivEasyCredit/
cp -r ./module-api/EasyCreditApi/* ./build/Frontend/NetzkollektivEasyCredit/Library/Netzkollektiv/EasyCreditApi/

cat > ./build/version.php <<EOL
<?php
class Shopware_Components_Plugin_Bootstrap {}
require_once dirname(__FILE__).'/Frontend/NetzkollektivEasyCredit/Bootstrap.php';
echo \Shopware_Plugins_Frontend_NetzkollektivEasyCredit_Bootstrap::getVersion();
EOL

version=$(php ./build/version.php)
rm ./build/version.php
(cd ./build && zip -r - *) > dist/easycredit-shopware-$version.zip
