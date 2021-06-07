# loan-app

## steps using docker: 
- run ./vendor/bin/sail up -d

- run ./vendor/bin/sail artisan migrate:fresh --seed


## steps using composer: 
- composer install
- php artisan migrate:fresh --seed
