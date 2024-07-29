<p align="center"><a href="https://flamesphp.com" target="_blank"><img src="https://i.ibb.co/5LBsG09/flames.png" width="400" alt="Flames Logo"></a></p>


<p align="center">
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/license-MIT-green" alt="License"></a>
</p>

<h3 align="center">The first PHP Framework cross side.</h3>

## About Flames

**Flames** is a **PHP framework** for frontend, backend, console applications and a set
of **PHP components**.

## Why should I migrate to the Flames?

* Flames framework created for high performance and large number of accesses.
* Only 1.12ms from kernel boot to call controller (17-18x faster than Laravel, closer from Slim).
* You don't need to learn JS to develop your frontend, just run PHP 8.3 natively in Flames (WebAssembly).
* ORM with automatic migrations turn database easy.
* ORM with Models and Repositories.
* Server HTTP Client based on GuzzleHttp.
* Client HTTP Client based on Axios (PHP GuzzleHttp Fetch wrapper).
* Frontend PHP engine with Routes, Controllers and Elements wrapper.
* Templating engine based on TWIG running on backend and frontend.
* Async Coroutines running outside CLI without external extensions.
* Command for build PHP project as static website (to upload on S3, GitHub Pages, CloudFlare Pages or simple HTML-only server).
* JetBrains PhpStorm and Visual Code plugins.
* Platform Engine (Windows Only), to do things that PHP can't (ex: get server mouse position, supporting automate server GUI apps (like Selenium, but for O.S.) using OCR's and [mouse/keyboard interceptor : under development]).
* Automatic build client PHP files and load page (like Angular).
* Automatic detect CSS/JS changed files and load page.

## Benchmark

### From server kernel boot to call controller
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

### From client kernel load/boot (CDN)
|                                                        | First Request Time | Cached Request Time |  
|--------------------------------------------------------|--------------------|---------------------|
| Flames PHP 8.3 kernel                                  | 22ms               | 1ms                 |
| mbstring PHP extension *(optional)*                    | 24ms               | 1ms                 |   
| intl PHP extension *(optional)*                        | 43ms               | 2ms                 |   
| gd (freetype+png+webp+jpeg) PHP extension *(optional)* | 31ms               | 1ms                 |      
| openssl PHP extension *(optional)*                     | 46ms               | 2ms                 |       
| simplexml PHP extension *(optional)*                   | 23ms               | 1ms                 |         
| xml PHP extension *(optional)*                         | 38ms               | 2ms                 |         
| sqlite PHP extension *(optional)*                      | 22ms               | 1ms                 |         
| pdo_sqlite PHP extension *(optional)*                  | 24ms               | 1ms                 |          
| phar PHP extension *(optional)*                        | 9ms                | 1ms-                |         
| yaml PHP extension *(optional)*                        | 4ms                | 1ms-                |          
| zip PHP extension *(optional)*                         | 3ms                | 1ms-                |            
| zlib PHP extension *(optional)*                        | 3ms                | 1ms-                |
| From kernel boot to call controller                    | 189ms              | 128ms               |

*PHP dynamic extensions can be loaded at runtime as needed. It is not necessary to load them at boot.*

## Release

Working in progress...

# Installation

* Copy .env.dist to .env.

#### Setup Docker with Composer
* Clone (or download zip) our [skeleton repository](https://github.com/flamesphp/skeleton).
* Choose PHP Interpreter containers *(in docker-compose.yml)*
* Run `composer update` in your root folder
* Run `docker-compose up -d` in your root folder
* Run `php bin install` in your docker php container

#### Setup Local with Composer
* Clone (or download zip) our [skeleton repository](https://github.com/flamesphp/skeleton).
* Required PHP 8.3 / 8.2
* Required PHP mbstring extension
* Required PHP Composer
* Required PHP Interpreter (supported)
* Run `composer update` in your root folder
* Run `php bin install` in your root folder
* Run `php bin server {host}:{port}` *(optional, case not use Apache/NGINX)*

#### Setup Docker
* Clone (or download zip) this repository.
* Choose PHP Interpreter containers *(in docker-compose.yml)*
* Run `docker-compose up -d` in your root folder
* Run `git submodule add git@github.com:{user}/{repo}.git App` to setup your app repository *(optional)*
* Run `php bin install` in your docker php container

#### Setup Local
* Clone (or download zip) this repository.
* Required PHP 8.2/8.3
* Required PHP mbstring extension
* Required PHP Interpreter supported
* Run `git submodule add git@github.com:{user}/{repo}.git App` to setup your app repository *(optional)*
* Run `php bin install` in your root folder
* Run `php bin server {host}:{port}` *(optional, case not use Apache/NGINX)*

#### PHP Interpreters Supported
* Apache + Module PHP + HtAccess *(config using template .htaccess)*
* Apache + PHP-FPM *(WIP)*
* NGINX + PHP-FPM *(config using template .docker/nginx/default.conf)*
* RoadRunner + PHP-FPM *(WIP)*

# Important things
* Continuous development, we are here to stay.
* Would you like a new feature? Create an issue.
* Want to be a contributor? Just do and create a pull request.

# TODO
* Automatic build SCSS/SASS and refreshs pages like Angular.
* Performance goal: 10.000 database rows rendered in template engine at 10ms.
* ORM support: PostgreSQL, Oracle Database, SQL Server, ElasticSearch, MongoDB, Redis Stack, Apache Cassandra, Amazon DynamoDB and SQLite.
* Caching ORM models, with support: KeyDB Flash (KeyDB with RocksDB), KeyDB, Redis, Redis Stack, MemCached and FileStorage.
* Profiler injection with requests, benchmarks, memory, etc.
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
* P2P/TOR protocol support.
* Database Admin supporting all types of database (include from cache) like PhpMyAdmin.

## License

The Flames framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

*Flames was developed from scratch, with the goal of replacing the legacy KrupaBOX private framework (v1 2004-2015 with PHP 4.3-5.3 and v2 2016~2023 with PHP 5.4-7.4), used in several banks, large streaming platforms and government agencies. Now in an open-source way, created by **[kazzkzpk](https://github.com/kazzkzpk)**.*
