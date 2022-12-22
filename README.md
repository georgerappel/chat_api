# Chat API

Welcome a simple Chat API, built by @georgerappel!

I've included some docs for reference and simplicty (and maybe for myself in the future).

# About

* Simple back-end for a chat application
* It uses PHP 8.1 + Slim + SQLite
* Includes Docker support, to reduce overhead when building it locally (Thanks to [this post](https://dev.to/cherif_b/using-docker-for-slim-4-application-development-environment-1opm) for providing a boilerplate)


# Setup (Docker)

To run the project using docker:

* Build and run: `docker-compose up -d --build`
* Go to http://localhost:8080/
* To see real-time logs: `docker-compose logs -f`