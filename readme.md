

## Resume API  

Used to send raw data through 

```sh
/resume/{lang}
/intro
/socials
/archive
/status
```

Usage
------


```sh
$ composer i 
```


Create the database schema:
```sh
$ php bin/console doctrine:database:create
$ php bin/console doctrine:schema:update --force
```

Run the web server:
```sh
$ symfony server:start
```

 Managed with [EasyAdmin](https://symfony.com/bundles/EasyAdminBundle/current/index.html) & [Symfony](https://symfony.com/) 5.4.7

 
