# Optimization test

## Description
You are given an array of sets with integers. You need to merge sets with equal values in one sorted set with unique elements.
For example:
```php
    $input = [
        [1, 3, 4, 5, 5],
        [6, 55, 12, 8],
        [3, 55],
        [92, 28]
    ];
    
    $output = [
        [1, 3, 4, 5, 6, 7, 12, 55],
        [28, 92]
    ];
```

This repository solves the problem described above in two ways: 
- brute force
- with optimization

## Test execution
PHP 7.4.5
```bash
    $ php index.php

    Without optimization result:
    Min: 5.4330170154572
    Max: 5.5232660770416
    Avg: 5.4626774311066
    
    With optimization result:
    Min: 0.50348496437073
    Max: 0.51053309440613
    Avg: 0.50762400627136
```
