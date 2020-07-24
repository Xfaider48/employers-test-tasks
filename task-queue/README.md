# Api without framework

## Task description
Есть веб-api, принимающее события для группы аккаунтов (для теста - 1000, в реальности сотни тысяч). События в большом объёме непрерывно (для теста ограничим поток в 10к событий).
Обработка события занимает 1с. Реализовать фоновую обработку (sleep + лог "результатов") событий с сохранением очерёдности для каждого аккаунта.
веб-апи эмулировать консольным скриптом, генерирующим события для обработки в фоне.
События генерировать случайными блоками, содержащими последовательноси по 1-5 для каждого аккаунта.

## Starting app
Before stating you need to make `.env` (see `.env.example`)
Start db and rabbit:
```bash
docker-compose up -d
```

Start logger (in another terminal to see logs):
```bash
docker-compose -f docker-compose-services.yml run logger
```

Start workers (X - number of workers. It should be equal to `NUMBER_OF_WORKERS` value in `.env`):
```bash
bash start-workers.sh X
```

Start balancer:
```bash
docker-compose -f docker-compose-services.yml run -d balancer
```

Produce test data:
```bash
docker-compose -f docker-compose-services.yml run producer
```

## Stopping app
```bash
docker-compose -f docker-compose-services.yml down
docker-compose down
```