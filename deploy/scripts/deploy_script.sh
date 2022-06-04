#!/usr/bin/env bash

find ./deploy/ -type f -iname "*.sh" -exec chmod +x {} \;

if [ $4 == "stage" ]; then
    ./deploy/mapping_docker_stage.sh
else
  ./deploy/mapping_docker_prod.sh
fi

scp -r ./docker-compose.yml $1@$2:$3
scp -r ./docker $1@$2:$3
scp -r ./deploy $1@$2:$3

scp ./services/docker/remove_volume_if_not_exists.sh $1@$2:$3/deploy

ssh $1@$2 "cd $3 && docker-compose pull"
ssh $1@$2 "cd $3 && docker-compose down"

ssh $1@$2 "cd $3 && chmod +x ./deploy/remove_volume_if_not_exists.sh"
ssh $1@$2 "cd $3/deploy && ./remove_volume_if_not_exists.sh the_wug_main_data-volume"

ssh $1@$2 "cd $3 && docker-compose up -d --no-deps --force-recreate"

ssh $1@$2 "cd $3 && docker exec the_wug_app composer install"
ssh $1@$2 "cd $3 && docker exec the_wug_app npm install"

ssh $1@$2 "cd $3 && docker exec the_wug_app cp ./deploy/.env.dist ./.env"
ssh $1@$2 "cd $3 && docker exec the_wug_app php artisan key:generate"
ssh $1@$2 "cd $3 && docker exec the_wug_app php artisan cache:clear"
ssh $1@$2 "cd $3 && docker exec the_wug_app chmod +x ./services/docker/set_storage_read_write_permissions.sh"
ssh $1@$2 "cd $3 && docker exec the_wug_app ./services/docker/set_storage_read_write_permissions.sh"
ssh $1@$2 "cd $3 && docker exec the_wug_app ./services/start_supervisor.sh"

ssh $1@$2 "docker image prune -a -f"
