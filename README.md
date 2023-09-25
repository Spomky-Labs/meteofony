# Météofony

Playground for improving the security of the Météofony application.
1) Install and start Docker
    https://www.docker.com/get-started/

2) Install Git and Make

3) Get the application we are going to work on
   From a terminal:
    - git clone git@github.com:Spomky-Labs/meteofony.git
    or
    - git clone https://github.com/Spomky-Labs/meteofony.git
    or
    - gh repo clone Spomky-Labs/meteofony

4) Start the app
   From the root of the application
    - make build
    - make up
    - make init

5) From your browser
    https://localhost:8443

This is a basic application on top of:

* PHP 8.2
* Symfony 6.4
* Symfony UX + AssetMapper + Tailwind
* API Platform
* FrankenPHP
* Caddy
* And a few other things
