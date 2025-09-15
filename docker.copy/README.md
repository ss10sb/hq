# Docker LEMP stack

## What is this?

This is a development environment for PHP (specifically Laravel) built in seperate docker containers, 
managed with docker-compose.  The basic setup contains a LEMP stack (Linux, Nginx, MariaDB and PHP). 

## What's inside:
 * Nginx
 * PHP 7.x (fpm)
    * XDebug
 * Mailcatcher
 * MariaDB
 * Redis

## Documentation

Copy .env.example to .env and edit for your environment.  It is used by docker and the shell scripts.

### Requirements

Make sure you have installed `docker` and `docker-compose`. Both are easily installed via:

Docker: https://docs.docker.com/engine/installation/
   
Docker compose: https://docs.docker.com/compose/install/

### bin shell scripts

Prefix all commands with /dir/to/lemp/bin

	# LEMP stack related commands
	exec mailcatcher command (default: bash)
	exec mariadb command (default: bash)
	exec nginx command (default: bash)
	exec php (default)
	clear X (removes X from docker, X can be containers, networks, all (containers + networks) or images) 
	
	# Build commands
	publish [dev] (default is prod)
	composer (runs composer in container)
	node (runs node in container)
	npm (runs npm in container)
	tar (builds tar.gz file for project)
	
	# Helpers/utilities
	artisan_exec (artisan command in running php container)
	artisan_run (artisan command in one-off container)
	chown (change owner of working dir to user:group of current user)
	phpcs (run phpcs)
	phploc (run phploc)
	phpmd (run phpmd)
	phpstan (run phpstan)
	set_x (set executable bit on files in script directory)

### Launching containers

From directory with docker-compose.yml (eg, project/docker.copy)

    docker-compose up #-d to run in background
