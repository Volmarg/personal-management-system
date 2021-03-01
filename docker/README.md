# Running Personal Management System on Docker
See the [project install documentation](https://volmarg.github.io/docs/general/installation) unless you are running on Docker on Windows.

## Running Personal Management System on Docker on Windows

The intention of this README is to be moved to the [project install documentation](https://volmarg.github.io/docs/general/installation) in the future.

Currently need to follow steps listed in https://github.com/Volmarg/personal-management-system/pull/43#issue-493755082 as well as documentation from https://volmarg.github.io/docs/general/installation/

<details><summary> Steps from https://github.com/Volmarg/personal-management-system/pull/43#issue-493755082 </summary>

Steps from https://github.com/Volmarg/personal-management-system/pull/43#issue-493755082

```
how to start:

docker-compose build
docker-compose up -d
docker-compose exec php-fpm bash
composer install
composer pms-installer-docker

browse to: ip:8001

This docker-compose.yml setup contains an php-fpm, nginx, mariadb and an adminer container.
```

</details>

### Steps to run Personal Management System on Docker on Windows:
_Note:_ The steps below have been tested using Docker on Windows 10 (using the WSL 2 based engine).
1. Download the [latest version from GitHub](https://github.com/Volmarg/personal-management-system/releases/latest)
2. Extract/unzip the entire project into a folder on your system (using `C:\docker\personal-management-system` for these steps)
3. In PowerShell, navigate to the project directory
  * `PS C:\Users\MyUser> cd C:\docker\pms-1.4.30\`
  * No changes were made to any of the files in the project directory
  * Command prompt should show `PS C:\docker\pms-1.4.30>`
4. Validate `extension = apcu.so` is included in `php-fpm\php.ini`
  * `cat .\docker\php-fpm\php.ini`, response should be:
    ```
    memory_limit = 4G
    extension = apcu.so
    ```
5. Build the containers (the commands assume you are in the project directory)
  * Command prompt should show `PS C:\docker\pms-1.4.30>`
  * `docker-compose build`
  * `docker-compose up -d`
  * `docker-compose exec php-fpm bash`
6. Enter the `php-fpm` container and continue installation
  * `docker-compose exec php-fpm bash`
    * Command prompt should change to the container: `root@<container_id>:/application`
    * Example: `root@682bdf287a87:/application`
7. Install `apcu` and update `php` configuration
  * Install apcu with `pecl install apcu`
    * Output should be similar to:
      ```
      'install ok: channel://pecl.php.net/apcu-5.1.19'
      'Extension apcu enabled in php.ini'
      ```
  * Update php configuration with `pear config-set php_ini /application/docker/php-fpm/php.ini`
    * Output should be similar to:
      ```
      'config-set succeeded'
      ```
8. Continue with installation of Composer and `pms-installer-docker`
  * `composer install`
     * You should see this complete after installing approximately 140+ packages
     * Look for:
       ```
       Use the `composer fund` command to find out more!
       Run composer recipes at any time to see the status of your Symfony recipes.
       ```
  * `composer pms-installer-docker`
    * You should see this complete with output providing next steps, including:
      ```
      User register:
      Simply open the project in browser and if no user is registered, then You will see register button
      ```
9. Exit the `php-fpm` container, then restart the docker containers using the container IDs
  * issue `exit` then the command prompt should switch back to `PS C:\docker\pms-1.4.30>`
  * Find the container IDs using `docker ps*
    ```
    CONTAINER ID   IMAGE                            COMMAND                  CREATED          STATUS                    PORTS                                                               NAMES
    a719234ab9a1   pms-1430-attempt04_php-fpm       "docker-php-entrypoi…"   31 minutes ago   Up 31 minutes             9000/tcp                                                            php-fpm
    113bfc52caf2   mariadb                          "docker-entrypoint.s…"   31 minutes ago   Up 31 minutes             3306/tcp                                                            mariadb
    0b16cdd476b5   nginx:alpine                     "/docker-entrypoint.…"   31 minutes ago   Up 31 minutes             0.0.0.0:8001->80/tcp                                                nginx
    bd29ef0790c4   adminer                          "entrypoint.sh docke…"   31 minutes ago   Up 31 minutes             0.0.0.0:8081->8080/tcp                                              adminer
    ```
  * Restart the containers with `docker container restart <container1id> <container2id> <container3id> <container4id>`
  * Once the containers have restarted, browse to `http://127.0.0.1:8001` and begin using Personal Management System.
