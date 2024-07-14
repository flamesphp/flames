<p align="center"><a href="https://flamesphp.com" target="_blank"><img src="https://i.ibb.co/5LBsG09/flames.png" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Flames

**Flames** is a **PHP framework** for frontend, backend, console applications and a set
of **PHP components**.

## Why should I migrate to the Flames?

* Flames framework created for high performance and large number of accesses.
* Only 1.12ms from kernel boot to call controller (17-18x faster than Laravel, closer from Slim).
* You don't need to learn JS to develop your frontend, just run PHP natively in Flames.
* ORM with automatic migrations turn database easy.
* ORM with Models and Repositories.
* Server HTTP Client based on GuzzleHttp.
* Client HTTP Client based on Axios (PHP GuzzleHttp Axios wrapper).
* Frontend PHP engine with Routes, Controllers and Elements wrapper.
* Templating engine based on TWIG running on backend and frontend.
* Async Coroutines running outside CLI without external extensions.
* Command for build PHP project as static website (to upload on S3, GitHub Pages, CloudFlare Pages or simple HTML-only server).
* JetBrains PhpStorm and Visual Code plugins.

## Benchmark

### From kernel boot to call controller
|                 | Request Time |  Queue Requests | 
|-----------------|--------------|-----------------|
| Pure PHP        |    0.53ms    |    1887 p/sec   |
| Flames beta0.1  |    1.12ms    |     893 p/sec   |
| Symfony 6.2     |    2.81ms    |     355 p/sec   |
| CakePHP 4.4     |    4.14ms    |     241 p/sec   |
| Lumen 10.0      |    4.46ms    |     224 p/sec   |
| Laminas 2.0     |    5.13ms    |     194 p/sec   |
| Fuel 1.9        |    5.31ms    |     188 p/sec   |
| CodeIgniter 4.3 |   12.65ms    |      79 p/sec   |
| Laravel 10.0    |   19.48ms    |      51 p/sec   |


## Release

Working in progress...

# Installation

* Copy .env.dist to .env.

#### Setup Docker (or)
* Choose containers to use *(optional, Apache or NGINX+FPM in docker-compose.yml, default to NGINX+FPM)*
* Run `docker-compose up -d` in your root folder
* Run `git submodule add git@github.com:{user}/{repo}.git App` to setup your app repository *(optional)*
* Run `php bin install` in your docker php container

#### Setup Local
* Required PHP 8.2/8.3
* Required PHP mbstring extension
* Required Apache or NGINX+FPM *(optional, case NGINX+FPM do config using template .docker/nginx/default.conf)*
* Run `git submodule add git@github.com:{user}/{repo}.git App` to setup your app repository *(optional)*
* Run `php bin install` in your root folder
* Run `php bin server {host}:{port}` *(optional, case not use Apache/NGINX)*


# Important things
* Continuous development, we are here to stay.
* Would you like a new feature? Create an issue.
* Want to be a contributor? Just do and create a pull request.

# TODO
* Performance goal: 10.000 database rows rendered in template engine at 10ms.
* ORM support: PostgreSQL, Oracle Database, SQL Server, ElasticSearch, MongoDB, Redis Stack, Apache Cassandra, Amazon DynamoDB and SQLite.
* Caching ORM models, with support: KeyDB Flash (KeyDB with RocksDB), KeyDB, Redis, Redis Stack, MemCached and FileStorage.
* Profiler injection with requests, benchmarks, memory, etc.
* Automatic build SCSS/SASS and refreshs pages like Angular.
* Command for build JS/CSS minified bundles.
* Command for build obfuscated PHP/JS files.
* Caching generic, with support: KeyDB Flash (KeyDB with RocksDB), KeyDB, Redis, Redis Stack, MemCached and FileStorage.
* Custom tags template like Polymer.
* WebSocket support with parallels (websocket + socket) together.
* Frontend ORM using IndexedDB with Models and Repositories.
* Selenium like undetected automated browser.
* Plugins community with permissions support (like android).
* Run JavaScript on backend for render templates using Vue.js, React ou AngularJS.
* PHP VM inside PHP, allowing custom disabling functionalities.
* Backend Python engine like V8JS  with native PHP, to do things that PHP can't (ex: get server mouse position, supporting automate server GUI apps using OCR's and mouse/keyboard interceptor).
* P2P/TOR protocol support.
* Database Admin supporting all types of database (include from cache) like PhpMyAdmin.

## License

The Flames framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


*Flames was developed from scratch, with the goal of replacing the legacy KrupaBOX private framework (v1 2004-2015 with PHP 4.3-5.3 and v2 2016~2023 with PHP 5.4-7.4), used in several banks, large streaming platforms and government agencies. Now in an open-source way, created by **[kazzkzpk](https://github.com/kazzkzpk)**.*
