# Chat API

Welcome a simple Chat API, built by @georgerappel!

I've included some docs for reference and simplicty (and maybe for myself in the future).

# About

* Most of the relevant app logic sits in app/UserController and app/ChatController.
* The app/routes.php file simply calls the controllers
* Very few abstractions/models were used to keep a simple architecture using mostly Slim 4, without ORMs or DB helpers aside from native PHP SQLite3 library.
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

## Testing without the Authorization header
Comment out lines 11-18 on the AuthMiddleware.php, and uncomment line 20 with your user ID from the POST /users route.

# API Reference
## Authorization
* For authorization, a simple logic expects the username in the Authorization header of a request, instead of bearer tokens

## Users Routes
Users are 'public', and no authorization is required to create, search or list users.

* POST /users - Create user, expects a JSON body with `{"username": "myname"}`
    * IF the username is available, returns HTTP 200 OK with `{"id": 1234}`
    * IF the username is not available, returns HTTP 409 CONFLICT with `{"error": "Username taken"}`
* GET /users?query={name} - Read all users, optionally provide a query for searching usernames, returns HTTP 200 OK with `{"users": [{"username": "myname", "id": 123, "created_at": "2022-12-22 12:00:00"}, ...]}`
* GET /users/{username} - Read user, returns `{"username": "myname", "id": 123, "created_at": "2022-12-22 12:00:00"}`

## Chat Routes
Authorization is required for all routes.

* GET /chat/{other_user} - Reads messages from a chat, returns last 20 messages.
    * IF the chat was never used, returns an empty array
    * ELSE returns up to 20 messages: `{"messages": [{"id": 1, "content": "Hi, this is my message", "sender": 1, "recipient": 2, "created_at": "2022-12-22 12:00:00"}, {"id": 2, "content": "Hi myname, heres my message as well", "sender": 2, "recipient": 1, "created_at": "2022-12-22 13:00:00"}], "first_message": 1, "last_message": 2}`
    * To **load previous/older messages**, use the `?before={first_meswsage}` query param with the first message received on a previous request to load older messages
    * To **Poll for new messages**, use the `?after={last_message}` query param with the last_message from the first request made, this will load newer messages.
* POST /chat/{other_user} - Send a message to "other_user", expects a JSON body with `{"content": "Plain text message"}`
    * Returns the message just created: `{"id":8,"sender":1,"recipient":4,"created_at":"2022-12-22 21:19:25","content":"My first message"}`


# Improvements & Shortcomings

* Add a route `GET /users/{id}/online` to check wether the user is currently online, based on their last poll for messages, storing this in a simple 30sec cache or in a column on the user table.
* Add indexes to the sender/recipient columns on the message table to speed up queries
* Add an error handler for exceptions, currently raising exceptions such as HttpNotFoundException returns HTTP 200 OK with the trace on the body, instead of the 404 NOT FOUND.