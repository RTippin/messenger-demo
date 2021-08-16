# [Messenger Demo App](https://github.com/RTippin/messenger)

---

## Included addon packages:
- UI / Web routes [messenger-ui](https://github.com/RTippin/messenger-ui)
- Ready-made bots [messenger-bots](https://github.com/RTippin/messenger-bots)
- Faker commands [messenger-faker](https://github.com/RTippin/messenger-faker)
- Janus media server client [janus-client](https://github.com/RTippin/janus-client)

## Checkout the [LIVE DEMO](https://tippindev.com)

### Prerequisites
- PHP >= 7.4
- Laravel >= 8.42
- MySQL >= 8.x
- laravel-echo-server [tlaverdure/laravel-echo-server](https://github.com/tlaverdure/laravel-echo-server) 
- [PHPREDIS](https://github.com/phpredis/phpredis/blob/develop/INSTALL.markdown)

### Notes
- This demo is meant to be seeded before use. Registration also assumes a pre-seeded database, as we automatically create threads between the admin user and a newly registered user, as well as set friendships.
- Calling will be disabled by default. Even though we have our [janus-client](https://github.com/RTippin/janus-client) installed, you are responsible for setting up your own `Janus Server`.
- Please see [Janus official docs](https://janus.conf.meetecho.com/docs/index.html) for more information.

---

# Installation

#### Clone or download this repository
```bash
$  git clone git@github.com:RTippin/messenger-demo.git
```

#### Composer install
```bash
$  composer install
```

#### Rename the .env.example to .env and configure your environment

#### Generate your app key
```bash
$  php artisan key:generate
```

#### Migrate and seed your database
```bash
$  php artisan migrate:fresh --seed
```

#### To view the API Explorer, you must download our generated responses
```bash
$  php artisan messenger:get:api
```

---

## Running locally:

#### Run each command in their own terminal inside your project folder
- You must setup *laravel-echo-server* first before running it!
```bash
$  php artisan serve
$  php artisan queue:work --queue=messenger,messenger-bots,default
$  laravel-echo-server start
```

---

## Setting up laravel-echo-server
- We must globally install laravel-echo-server and initialize it within our project. We can then stick all keys we need into the `.env`

#### Install globally
``` bash
$  npm install -g laravel-echo-server
```

#### Initialize laravel-echo-server in our project
- Follow console setup guide for laravel-echo-server. You should have it generate the `appId` and `key` for you. When completed, your project directory should contain a *laravel-echo-server.json*.
``` bash
$  laravel-echo-server init
```

#### Example
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

#### Now copy your *laravel-echo-server.json* `appId / key` into the `.env` `SOCKET_APP_ID / SOCKET_APP_KEY`

---

## Default seeded admin account:

### Email `admin@example.net`

### Password: `messenger`

### All other seeded accounts use `messenger` password as well

---

# Pictures:


<img src="https://i.imgur.com/lFw5PZj.png" style="width:100%;"  alt="Demo"/>

---

<img src="https://i.imgur.com/JWIzv61.png" style="width:100%;"  alt="Demo"/>

---

<img src="https://i.imgur.com/f30GeCZ.png" style="width:100%;"  alt="Demo"/>

---

<img src="https://i.imgur.com/lnsRJfV.png" style="width:100%;"  alt="Demo"/>
