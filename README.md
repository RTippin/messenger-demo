# Tippin's Messenger Demo
Laravel 6 messenger demo app by Richard Tippin. Working to release this messenger as a package for use with laravel. This is a fully featured stand alone app you can use as a demo or extract my source to fit your needs.

****Please note that until I ready to extract this further into a package, I will be constantly moving items around, and will soon be redoing the entire database structure to utilize more pivots to avoid duplicated data***

### Checkout the [LIVE DEMO](https://tippindev.com)
*The database and all uploaded files are wiped and reset weekly*

### Prerequisites
* PHP >= 7.2
* REDIS
* Database
* laravel-echo-server [tlaverdure/laravel-echo-server](https://github.com/tlaverdure/laravel-echo-server)

### Features
* Models use morphs, able to message between any model using the HasMessenger trait and declare in the messenger config what models and their respective aliases are to be used in your application
* Real-time messaging
* Mobile friendly mode
* Messenger inbox is a SPA portal. Page reloads or history state changes are tracked and managed when inside */messenger*
* Messenger settings (online/away/offline, accept knocks, sounds, calls from non friends)
* Inactivity status (afk after 10 minutes)
* Group messaging or private messaging (can only have one active private thread between another model at one time, backend checks)
* Upload a profile avatar or a group thread avatar
* Video/Group calling and screen sharing (tracks calls and call participants in DB/Redis)
* Group permissions/admins
* Group invitation links (like discord)
* Live typing and read receipts (bobble heads, similar to hangouts)
* Able to delete private or group threads, or individual messages (if you own them or are admin in a group)
* Uses soft delete, job setup to force delete and remove attachments after 90 days archived
* Thread lockouts
* Messages support emojis, links, youtube, uploaded images or files. You may also drag and drop multiple files into the window at once
* System messages for events like calls, group changes (name, avatar, someone joined or added to group, etc)
* Ghost User model as the default/fallback if a morphs owner is not found (auto-locks private thread in this case)
* Uses ip-api on backend to get IP and timezone, and moment.js on frontend to ensure utc is changed to clients local time
* Friend request / Contacts
* Knock at someone (like a facebook poke to get attention, has a 5 minute lockout)
* Scheduler and jobs that run health checks on calls, group invite expiration's, 
### Notes
* The PushNotificationService is not used right now, but ready to setup and use Google's FCM or Apple's APN push services. You would store a users device ID/TOKEN/VOIP_TOKEN from your mobile app to the user_devices table. Passport can help you achieve this, and I may add that in soon as well
* This demo uses *"intervention/image"* package to render/orient/cache images, so no need to symlink storage directory to public
* Using my own made js framework, working towards moving to React
* If you use my database seeder, all account passwords are **Messenger1!**
  * Priority account emails are (admin@test.com, admin2@test.com, admin3@test.com) that are setup to be friends with everyone, and start message thread with everyone
  * All demo accounts are added to a default messenger group thread

# Installation (Laravel 6.x)

***In this demo, I use laravel-echo-server, which you should install globally first if you do not already have it***
``` bash
$  npm install -g laravel-echo-server
```

***Clone or download this repository***
```bash
$  git clone git@github.com:RTippin/messenger-demo.git
```
***Run in project:***
```bash
$  composer install
```
---
**Remove the .example from the .env.example file** 

---
***Run in project:***
```bash
$  php artisan key:generate
$  npm install
```

*Setup laravel-echo-server first so we can stick all keys we need into the .env at once*

***Run in project:***
``` bash
$  laravel-echo-server init
```
Follow console setup guide for laravel-echo-server. You should have it setup the appId and key for you. When completed, your project directory should contain a *laravel-echo-server.json*. Be sure to setup redis port and server for it. 

Example:
``` json
{
    "authHost": "http://localhost:8000",
    "authEndpoint": "/broadcasting/auth",
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
Now copy your *laravel-echo-server.json* **appId / key** into the .env **MIX_SOCKET_APP_ID / MIX_SOCKET_APP_KEY** and update the other **SOCKET / MIX_SOCKET** values to match your needs

**Update the database, redis, mail, recaptcha values to your use case**

If you have a pro account from [https://ip-api.com/](https://ip-api.com/) add your key to the .env **IPAPI_KEY**

***Run in project:***
```bash
$  php artisan config:cache
$  php artisan migrate:fresh --seed
```
***Compile the js/css assets:***
```bash
$  npm run dev
```
## Final Steps:
To run locally, run 3 terminals for the following commands in your project for all features minus scheduler:
```bash
$  php artisan serve
$  php artisan queue:work
$  laravel-echo-server start
```
For the auto call health checks/end calls, you will need to setup a cronjob / service worker to run the laravel scheduler

Example:
```bash
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```
Video calls themselves are tracked on the backend, but use the open WebRTC standard for media streaming amongst peers. I have included some open google stun servers inside my 
[WebRTCManager.js](https://github.com/RTippin/messenger-demo/blob/1148f71f49598118b99c51b66851ffec94b4e6a1/resources/js/managers/WebRTCManager.js#L479)

If you choose to use this outside of demo purposes, I recommend you setup your own Stun/Turn servers

### You should now be up and running!
*Please note the entire javascript managers/modules in the assets directory are a mini framework of my own making. I am hoping to switch this over to React as we are working towards a mobile app based on React Native* 

- - - -
**Inside a group conversation**
![Screenshot](public/images/example/screen1.png?raw=true "Screenshot")

----
**Contacts list/Mobile view**
![Screenshot](public/images/example/screen2.png?raw=true "Screenshot")
----
**In group call, other user is screen sharing**
![Screenshot](public/images/example/screen3.png?raw=true "Screenshot")

<p align="center"><img src="https://res.cloudinary.com/dtfbvvkyp/image/upload/v1566331377/laravel-logolockup-cmyk-red.svg" width="400"></p>
<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>


## License

The Laravel framework / Tippin's Messenger demo app is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
