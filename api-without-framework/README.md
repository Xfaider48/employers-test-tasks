# test-task-mini-api
Test task for interview

## Starting app
To run app use command:
`docker-compose up -d`

To apply migrations use command:
`docker-compose exec php-fpm sh -c "php migrate.php"`

## Routes
Make products:
`GET /products`

Create order:
`POST /orders`
with `product_ids` in JSON body of request

Make payment for order:
`POST /orders/{orderId}/pay`
