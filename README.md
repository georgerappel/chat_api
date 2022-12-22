# Chat API

Welcome a simple Chat API, built by @georgerappel!

I've included some docs for reference and simplicty (and maybe for myself in the future).

# About

* Most of the relevante app logic sits in app/UserController and app/ChatController.
* The app/routes.php file simply calls the controllers
* Very few abstraction/models were used to keep a simple architecture using mostly Slim 4, without ORMs or DB helpers aside from native PHP SQLite3 library.
* Simple back-end for a chat application
* It uses PHP 8.1 + Slim 4 + SQLite
* Docker support was used to reduce overhead when building it locally (Thanks to [this post](https://dev.to/cherif_b/using-docker-for-slim-4-application-development-environment-1opm) for providing a quick boilerplate)


# Setup (Docker)

To run the project using docker:

* Build and run: `docker-compose up -d --build`
* Go to http://localhost:8080/
* To see real-time logs: `docker-compose logs -f`

# Ideal Workflow
1. Create username, using POST /users
2. Optionally, search for a username
3. Open a chat with another user, using GET /chat/{other_user}


# API Reference
* POST /users - Create user, expects the body `{"username": "myname"}`
    * IF the username is available, returns HTTP 200 OK with `{"id": 1234}`
    * IF the username is not available, returns HTTP 409 CONFLICT with `{"error": "Username taken"}`
* GET /users?query={name} - Read all users, optionally provide a query for searching usernames, returns HTTP 200 OK with `{"users": [{"username": "myname", "id": 123, "created_at": "2022-12-22T12:00:00"}, ...]}`
* GET /users/{username} - Read user, returns `{"username": "myname", "id": 123, "created_at": "2022-12-22T12:00:00"}`
* GET /chat/{other_user}?before={message_id} - Reads messages from a chat, returns last 20 messages, optional Page parameter loads more messages.
    * IF the chat was never used, returns an empty array
    * ELSE returns up to 20 messages: `{"messages": [{"id": 1, "content": "Hi, this is my message", "sender": "myname", "created_at": "2022-12-22T12:00:00"}, {"id": 2, "content": "Hi myname, heres my message as well", "sender": "othername", "created_at": "2022-12-22T13:00:00"}], "first_message": 1, "last_message": 2}`
    * Optionally, use the before message_id with the first message received on a previous request, to load older messages.
* POST /chat/{other_user} - Send a message to "other_user", expects the body `{"content": "Plain text message"}`
* GET /chat/{other_user}/new_messages/{last_message} - Poll for new messages sent after the last_message from the previous route, this ID is provided in the GET /chat/{other_user} response.

# Ideas

Some other features that could be included:
* Route /users/{id}/online to check wether the user is currently online, based on their last poll for messages