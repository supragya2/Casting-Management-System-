# newcastflow

A simple Casting Management System built with plain PHP (mysqli) + MySQL.
Designers upload designs and invite models. Models build a portfolio and
can also request to model for a design they like.

## How to run it (XAMPP)

1. Copy the whole `newcastflow` folder into `C:\xampp\htdocs\`
2. Open phpMyAdmin, and run everything inside `database/schema.sql`
   (this creates the `newcastflow` database and all tables)
3. Open `config.php` and check the 4 values at the top match your MySQL
   setup (default XAMPP is user "root", empty password)
4. Visit `http://localhost/newcastflow/` in your browser

## Why the links are relative

Every link in this project is a **relative path** (like `designs.php` or
`../login.php`), not an absolute one (`/designs.php`). That means it will
work no matter what folder name you put the project in, or whether it's
at the root of htdocs or inside a subfolder — as long as the folder
structure stays the same.

## File structure

```
newcastflow/
├── config.php              -> database connection (mysqli)
├── database/schema.sql     -> run once in phpMyAdmin
├── css/style.css           -> all the styling
├── js/script.js            -> confirm-delete popups
├── index.php                -> landing page (choose Designer or Model)
├── signup.php                -> sign up form
├── login.php                 -> login form
├── logout.php
├── uploads/designs/          -> uploaded design photos go here
├── uploads/photos/           -> uploaded model portfolio photos go here
├── designer/
│   ├── menu.php               -> sidebar (included on every designer page)
│   ├── dashboard.php
│   ├── designs.php            -> list of designs
│   ├── design_form.php        -> add / edit a design
│   ├── design_delete.php
│   ├── browse_models.php
│   ├── model_view.php         -> view one model + send invitation
│   ├── invitations.php        -> invitations you've sent
│   ├── requests.php           -> requests you've received, accept/decline
│   └── profile.php
└── model/
    ├── menu.php
    ├── dashboard.php
    ├── portfolio.php          -> upload portfolio photos
    ├── availability.php       -> mark free/busy dates
    ├── browse_designers.php
    ├── designer_view.php      -> view one designer + send request
    ├── invitations.php        -> invitations received, accept/decline
    ├── requests.php           -> requests you've sent
    └── profile.php
```

## How the code is written

Every page follows the same simple pattern, no fancy stuff:

```php
session_start();
include('../config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'designer') {
    header("Location: ../login.php");
    exit;
}
```

Then a plain `mysqli_query()` to get data, and plain HTML with
`<?php ... ?>` blocks to print it out. No classes, no PDO, no helper
functions beyond what's built into PHP — so it should be easy to read
top to bottom.
