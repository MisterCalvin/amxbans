## AMXBans 6.14.5
![AMXBans 6.14.5](build/nginx/config/amxbans/web/images/banner/amxbans.png "AMXBans")  
An updated and Dockerized version of AMXBans with added quality of life features. See [INSTALL.md](INSTALL.md) for help with installation, or [CHANGELOG.md](CHANGELOG.md) for update details

## Quickstart (Docker)

> [!IMPORTANT]  
> If you intend to use an existing AMXBans database, you will need to import the new columns from [build/nginx/config/amxbans/amxbans_update.sql](build/nginx/config/amxbans/amxbans_update.sql) (be sure to create a backup of your database beforehand). Failure to do so will result in application errors when using certain pages. It is recommended you do a fresh install of AMXBans if you intend to use this version of the application.

`git clone https://github.com/MisterCalvin/amxbans.git && cd ./amxbans && docker compose pull && docker compose up -d`

> [!NOTE]
> You are not required to use the `amxbans-mariadb` container image in this repo if you have an existing MySQL database. This image is provided for convenience

### docker compose
```
services:
  amxbans-nginx:
    image: ghcr.io/mistercalvin/amxbans-nginx:latest
    container_name: amxbans-nginx
    hostname: amxbans-nginx
    environment:
      TZ: "UTC"
      INSTALL: "True" # Set to "False" after successful install
      
      # All of the variables below are optional and can be removed after a successful install
      # They can be omitted entirely if you wish to input this information manually during your first install
      ADMIN_USER: "root"
      ADMIN_PASS: "password" # Minimum 4 characters
      ADMIN_EMAIL: "root@email.com"
      URL_PATH: "amxbans.mydomain.com"
      DB_HOST: "amxbans-mariadb"
      DB_USER: "root"
      DB_PASS: "password"
      DB: "amxbans"
      DB_PREFIX: "amx_"
    volumes:
      - amxbans_web:/var/www/html
    ports:
      - 80:80
    depends_on:
      - amxbans-mariadb
    restart: unless-stopped

  amxbans-mariadb:
    image: ghcr.io/mistercalvin/amxbans-mariadb:latest
    container_name: amxbans-mariadb
    hostname: amxbans-mariadb
    environment:
      TZ: "UTC"
      MYSQL_USER: "root"
      MYSQL_PASSWORD: "password"
      MYSQL_ROOT_PASSWORD: "rootpassword"
      MYSQL_DATABASE: "amxbans"
    volumes:
      - amxbans_db:/var/lib/mysql
    ports:
      - 3306:3306
    restart: unless-stopped

volumes:
  amxbans_web:
    name: amxbans_web
  amxbans_db:
    name: amxbans_db
```

### docker cli

Create two named volumes before executing the commands below: `docker volume create amxbans_web` and `docker volume create amxbans_db`

#### Web Server
```
docker run -d \
  --name=amxbans-nginx \
  --hostname=amxbans-nginx \
  -e TZ="UTC" \
  -e INSTALL="True" \
  -e ADMIN_USER="root" \
  -e ADMIN_PASS="password" \
  -e ADMIN_EMAIL="root@email.com" \
  -e URL_PATH="amxbans.mydomain.com" \
  -e DB_HOST="amxbans-mariadb" \
  -e DB_USER="root" \
  -e DB_PASS="password" \
  -e DB="amxbans" \
  -e DB_PREFIX="amx_" \
  -v amxbans_web:/var/www/html \
  -p 80:80 \
  --restart unless-stopped \
  ghcr.io/mistercalvin/amxbans-nginx:latest
  ```

#### Database
  ```
  docker run -d \
  --name=amxbans-mariadb \
  --hostname=amxbans-mariadb \
  -e TZ="UTC" \
  -e MYSQL_USER="root" \
  -e MYSQL_PASSWORD="password" \
  -e MYSQL_ROOT_PASSWORD="rootpassword" \
  -e MYSQL_DATABASE="amxbans" \
  -v amxbans_db:/var/lib/mysql \
  -p 3306:3306 \
  --restart unless-stopped \
  ghcr.io/mistercalvin/amxbans-mariadb:latest
```

## Notes, Disclaimers, Bugs

> [!WARNING]
> The codebase for AMXBans is 20+ years old. Outside of the work done by fysiks1 to make AMXBans work with modern versions of PHP/MySQL, and the quality of life features/bug fixes featured in this repo, there has been no active development AMXBans in a long time, nor has the codebase been audited recently. Please keep this in mind if you intend to publicly expose this application to the internet, as there may be undiscovered exploits or undocumented bugs. If you do intend to expose this application to the internet, consider using the Docker images found in this repo, which will run the application as an unpriviledged user, and ensure you generate secure, random passwords for your instance. The author of this repo is in no way responsible for users who choose not to heed this warning.

- Executing `amx_plugins` on `Admin area -> Server -> <Your GoldSrc Server>` will return `No response from server!`, this is because SourceQuery handles packet fragmentation in a different way than the old `rcon_hl_net.inc` library. There is no fix currently, however all other commands do work.

- Bans issued from `Admin area -> Add online ban` will be marked as the Server issuing the ban on our `ban_list.php`, rather than the web user who executed it. No fix currently.

- I'm not sure email_security is actually doing anything on `Admin area -> Website -> Admins` or `Admin area -> Website -> Settings`. I personally use an SMTPS relay, and successfully tested it with that.

- The Active checkbox for your Default Ban Set on `Admin area -> Server -> Ban Reasons` will not be checked, even if it is correctly set in the database. No fix currently.

- The Docker containers are currently only compiled and tested on amd64. ARM64 images will be coming in the future.

## Credits

[fysiks1/amxbans](https://github.com/fysiks1/amxbans) for their work updating AMXBans to PHP8  
[PHPMailer](https://github.com/PHPMailer/PHPMailer)  
[xPaw/SourceQuery](https://github.com/xPaw/PHP-Source-Query) for their PHP Source Query class  
[TrafeX/docker-php-nginx](https://github.com/TrafeX/docker-php-nginx) for their tiny NGINX 1.24/PHP-FPM 8.3 Dockerfile  
[jbergstroem/mariadb-alpine](https://github.com/jbergstroem/mariadb-alpine) for their tiny mariadb Dockerfile   
[cod3venom/amx_sploit](https://github.com/cod3venom/amx_sploit)  
[NginxProxyManager/nginx-proxy-manager](https://github.com/NginxProxyManager/nginx-proxy-manager) for [block-exploits.conf](build/nginx/config/conf.d/include/block-exploits.conf)
