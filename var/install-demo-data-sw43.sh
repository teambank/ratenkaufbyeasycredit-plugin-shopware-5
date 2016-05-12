cd /shopware/
wget http://releases.s3.shopware.com/demo_4.3.0.zip
unzip demo_4.3.0.zip
rm demo_4.3.0.zip
cat demo.sql | mysql -h $SWDB_HOST -u $SWDB_USER -p$SWDB_PASS $SWDB_DATABASE
rm demo.sql
