FROM ubuntu:jammy

ARG DEBIAN_FRONTEND=noninteractive;

# Required packages to be able to install php8.1
## Also the apt-get update must be chained like this, it's known docker issue
RUN apt-get update --fix-missing && yes | apt-get install software-properties-common;

# Install some packages for easier work in docker and packages that just MUST be there
RUN     yes | apt-get install nano \
    &&  yes | apt-get install curl \
    &&  yes | apt-get install wget \
    &&  yes | apt-get install symlinks \
    &&  yes | apt-get install less;

# Need to expose internal container port to allow mapping it to some port that will be accesible outside of container
## 8025 = gui
## 1025 = incoming tcp smtp
EXPOSE 8025 1025

RUN wget https://raw.githubusercontent.com/axllent/mailpit/develop/install.sh && sh ./install.sh;
