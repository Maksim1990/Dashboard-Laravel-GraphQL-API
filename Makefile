up:
	USER_ID=$(id -u) GROUP_ID=$(id -g) docker-compose up -d
down:
	docker-compose down -v
exec:
	docker exec -it dashboard-api bash
rebuild-app:
	docker-compose up -d --build app
exec-mongo:
	docker exec -it dashboard-mongo bash
