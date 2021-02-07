## Running Personal Management System on Docker

The intention of this README is to be moved to the [project install documentation](https://volmarg.github.io/docs/general/installation) (need to check with the owner).

This README is a draft, please note _the steps below will result in a **non-working** Personal Management System_.

Currently need to follow steps listed in https://github.com/Volmarg/personal-management-system/pull/43#issue-493755082 as well as documentation from https://volmarg.github.io/docs/general/installation/

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

### Current steps to (attempt to) run Personal Management System on Docker:
_Note:_ The steps below have been tested using Docker on Windows 10 (using the WSL 2 based engine).  As mentioned above, the current steps do not allow Personal Management System to run properly.
1. Download the [latest version from GitHub](https://github.com/Volmarg/personal-management-system/releases/latest)
2. Extract/unzip the entire project into a folder on your system (using `C:\docker\personal-management-system` for these steps)
3. In PowerShell, navigate to the project directory
  * `PS C:\Users\MyUser> cd C:\docker\personal-management-system\`
  * No changes were made to any of the files in the project directory
4. Build the containers (the commands assume you are in the project directory)
  * Command prompt should show `PS C:\docker\personal-management-system>`
  * `docker-compose build`
  * `docker-compose up -d`
  * `docker-compose exec php-fpm bash`
5. Enter the `php-fpm` container and continue installation
  * `docker-compose exec php-fpm bash`
    * Command prompt should change to the container: `root@<container_id>:/application`
    * Example: `root@682bdf287a87:/application`
  * `composer install`
    * unable to issue `sudo` as it is not installed
    * received `bash: sudo: command not found`
  * `composer pms-installer-docker`
    * should receive multiple `  APCu is not enabled.` responses in the output
    * I have issued `pecl install apcu`, but need to resolve the APCu issue as mentioned in https://volmarg.github.io/docs/general/known-issues#php-apcu-extension
6. Open an issue in https://github.com/Volmarg/personal-management-system and see if we can get this to work in Docker on Windows
