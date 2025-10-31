# Homeowner Names - Technical Test

## How to run
- docker compose build
- docker compose up -d
- docker compose exec street-group-tech-task-cli php ../composer.phar install

- docker compose cp {LOCAL_FILE_PATH} street-group-tech-task-cli:/home/application
- eg: `docker compose cp /home/{USER}/Documents/examples.csv street-group-tech-task-cli:/home/application`

- docker compose exec street-group-tech-task-cli php artisan fix-names --file=/home/application/{FILE_NAME}
- eg: `docker compose exec street-group-tech-task-cli php artisan format-names --file=/home/application/examples.csv`
