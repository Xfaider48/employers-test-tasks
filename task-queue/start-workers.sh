#!/bin/bash
for i in $(seq 1 "$1"); do
  ((i--))
   docker-compose -f docker-compose-services.yml run -e WORKER_NUMBER=$i -d worker
done