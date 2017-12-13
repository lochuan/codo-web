### Codo: Collaboration Todo

**Codo** is a tiny project for Internet Programming course(CS Dept. of University of Seoul).

The project adopts MVC pattern, there are three folders within the main directory:

* controllers
* models
* views

The **controllers** was written in JavaScript. It receives user's input, push the data to the server, and rendering the page base on server's response.

The **models** was written in PHP. It gets the data from the client and deals with the **DB** stuff.
 
The **views** are just HTML pages. It define how the page looks like.

Here is the full view of the project structure:

```
├── LICENSE
├── README.md
├── controllers
│   ├── controller.js
│   ├── home_utils.js
│   ├── index_utils.js
│   ├── notify.js
│   └── utils.js
├── home.php
├── index.php
├── models
│   ├── classes
│   │   ├── logger.php
│   │   ├── room.php
│   │   ├── user.php
│   │   └── utils.php
│   ├── config
│   │   ├── db_table
│   │   └── dbconn.php
│   └── distributer.php
└── views
    ├── home.html
    ├── index.html
    └── style
        ├── home.css
        └── index.css
```

## Requirements

1. [Apache2](https://httpd.apache.org)
2. [PHP 7.0+](http://php.net/)
3. [PostgreSQL](https://www.postgresql.org/)

---

After the requirements all set, you have to declare a *connection string* for PostgreSQL connection.

SetEnv sets a particular variable to some value, for using the code you need something like

```
SetEnv CODO_DB_CONN "host=**** dbname=**** user=****password=****"
```

If this is for a specific virtual host, and you have access to the Apache configuration files, this would go inside the <VirtualHost> directive for that virtual host.
