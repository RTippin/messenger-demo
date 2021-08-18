# [Messenger Demo App](https://github.com/RTippin/messenger)

![Preview](public/examples/image1.png?raw=true)

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

#### Rename the .env.example to .env and configure your environment, adding your database connection details before proceeding
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=1234
DB_DATABASE=demo
DB_USERNAME=root
DB_PASSWORD=password
```

#### Run the Install Command
- This command will generate your `APP_KEY` for you, as well as migrating fresh and downloading our documentation files.
  - This will `WIPE` any data in your database as it runs `migrate:fresh` under the hood.
```bash
$  php artisan demo:install
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

# Pictures

### Sending images, voice messages, replies, and reactions
![Preview](public/examples/image1.png?raw=true)

---

### Interacting with a chat-bot using triggers to invoke responses
![Preview](public/examples/image2.png?raw=true)

---

### Viewing a bots actions and triggers
![Preview](public/examples/image3.png?raw=true)

---

### Managing a groups participants
![Preview](public/examples/image4.png?raw=true)

---

### In a video call
![Preview](public/examples/image5.png?raw=true)

---

### Sending documents and images, hovering over options / reactions
![Preview](public/examples/image6.png?raw=true)
