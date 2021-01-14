# [rtippin/messenger](https://github.com/RTippin/messenger) demo app

## Checkout the [LIVE DEMO](https://tippindev.com) (https://tippindev.com)

### Prerequisites
- PHP >= 7.4
- Laravel >= 8.x
- Database
- laravel-echo-server [tlaverdure/laravel-echo-server](https://github.com/tlaverdure/laravel-echo-server) 

# Installation (Laravel 8.x)

***Clone or download this repository***
```bash
$  git clone git@github.com:RTippin/messenger-demo.git
```
---

***Run in project folder:***
```bash
$  composer install
```
---

**Rename the .env.example to .env**

**Configure your desired environment variables.**

---

***In this demo, I use laravel-echo-server, which you should install globally first***
``` bash
$  npm install -g laravel-echo-server
```
---

***Run in project folder:***
```bash
$  php artisan key:generate
$  npm install
```
---

### Setup laravel-echo-server first. We can then stick all keys we need into the .env

***Run in project:***
``` bash
$  laravel-echo-server init
```
Follow console setup guide for laravel-echo-server. You should have it setup the appId and key for you. When completed, your project directory should contain a *laravel-echo-server.json*.

Example:
``` json
{
    "authHost": "http://localhost:8000",
    "authEndpoint": "/api/broadcasting/auth",
    "clients": [
        {
            "appId": "YOUR_APP_ID",
            "key": "YOUR_APP_KEY"
        }
    ],
    "database": "redis",
    "databaseConfig": {
        "redis": {
            "port": "6379",
            "server": "127.0.0.1"
        },
        "sqlite": {}
    },
    "devMode": true,
    "host": null,
    "port": "6001",
    "protocol": "http",
    "socketio": {},
    "sslCertPath": "",
    "sslKeyPath": "",
    "sslCertChainPath": "",
    "sslPassphrase": "",
    "subscribers": {
        "http": true,
        "redis": true
    },
    "apiOriginAllow": {
        "allowCors": true,
        "allowOrigin": "http://localhost:8000",
        "allowMethods": "GET, POST",
        "allowHeaders": "Origin, Content-Type, X-Auth-Token, X-Requested-With, Accept, Authorization, X-CSRF-TOKEN, X-Socket-Id"
    }
}
```
---

**Now copy your *laravel-echo-server.json* `appId / key` into the .env `SOCKET_APP_ID / SOCKET_APP_KEY`**

**Migrate and seed your database!**

***Run in project:***
```bash
$  php artisan migrate:fresh --seed
```
---

## Final Steps:
**To run locally, run 3 terminals for the following commands in your project folder:**
```bash
$  php artisan serve
$  php artisan queue:work --queue=messenger,default
$  laravel-echo-server start
```
---

# Pictures:


<img src="https://i.imgur.com/lFw5PZj.png" style="width:100%;"  alt="Demo"/>

---

<img src="https://i.imgur.com/JWIzv61.png" style="width:100%;"  alt="Demo"/>

---

<img src="https://i.imgur.com/f30GeCZ.png" style="width:100%;"  alt="Demo"/>

---

<img src="https://i.imgur.com/lnsRJfV.png" style="width:100%;"  alt="Demo"/>
