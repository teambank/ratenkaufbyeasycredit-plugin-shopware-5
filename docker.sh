VERSION=$1
VERSION_CLEAN=${VERSION//./}

export COMPOSE_PROJECT_NAME=easycredit-shopware-$VERSION_CLEAN
cat > docker-compose.yml <<EOL
shop:
  image: dnhsoft/shopware:$1
  volumes:
      - ./src/Frontend/NetzkollektivEasyCredit:/shopware/engine/Shopware/Plugins/Local/Frontend/NetzkollektivEasyCredit
      - .:/exttools
  links:
   - db
  ports:
   - "8$VERSION_CLEAN:80"
db:
  image: tutum/mysql:5.6
  environment:
   MYSQL_PASS: 123456
EOL

if [ "$2" == "bash" ]; then
    DEFAULT_CONTAINER=$(docker-compose -f docker-compose.yml ps | grep `cat .docker-compose-default` | cut -d" " -f 1)
    docker exec -ti $DEFAULT_CONTAINER bash
    exit;
fi;
docker-compose -f docker-compose.yml $2
