# MyFRIEND C2C E-Commerce Platform

MyFRIEND is a PHP and MySQL customer-to-customer e-commerce platform designed for South African informal traders and buyers. The system allows sellers to create shops, upload products, manage orders, and offer delivery or collection options. Buyers can browse products, add items to cart, pay online through PayFast Sandbox, or choose cash on delivery/collection where available.

## Project Overview

MyFRIEND was developed as a web-based marketplace to support informal traders by giving them a simple online platform to sell products. The system includes buyer, seller, and administrator functionality.

The platform focuses on:

* Simple product browsing
* Seller shop creation
* Secure user login
* Cart and checkout functionality
* PayFast Sandbox online payments
* Cash on delivery and cash on collection
* Seller-managed delivery and collection
* Buyer order tracking
* Seller order management
* Administrator management tools
* Complaint submission and complaint management

## Main Features

### Buyer Features

* Register and log in
* Browse marketplace products
* Search for products
* Filter products by category and province
* View product details
* View seller contact details and seller location
* Add products to cart
* Choose delivery or collection where available
* Pay online using PayFast Sandbox
* Choose cash on delivery or cash on collection
* View order history and payment status
* Submit complaints to the administrator

### Seller Features

* Create a seller shop
* Add shop contact details
* Add seller location
* Choose whether collection is available
* Provide a full collection address when collection is offered
* Upload products
* Manage listed products
* View buyer orders
* See payment status for each order
* Mark orders as delivered or collected

### Administrator Features

* View admin dashboard
* Manage users
* Manage products
* View transactions
* View and manage complaints
* Delete users or products where necessary

## Payment System

The project uses PayFast Sandbox for online payment testing.

The payment flow works as follows:

1. Buyer checks out with online payment.
2. The website redirects the buyer to PayFast Sandbox.
3. The buyer completes the sandbox payment.
4. PayFast returns the buyer to MyFRIEND.
5. The system updates the order as paid.
6. Buyer and seller pages show the updated payment status.

The project also supports:

* Cash on delivery
* Cash on collection

No real card details are collected by MyFRIEND. Online payment details are handled by PayFast.

## Delivery and Collection

Sellers are responsible for arranging delivery or collection with buyers.

If a seller offers collection, they must provide a full collection address. This address is shown to buyers when collection is available.

If a seller does not offer collection, the checkout page disables the collection option and shows that collection is unavailable.

## Technologies Used

* HTML5
* CSS3
* Bootstrap 5
* JavaScript
* PHP
* MySQL / MariaDB
* phpMyAdmin
* XAMPP
* InfinityFree
* PayFast Sandbox

## Project Structure

```text
MyFRIEND-C2C-Ecommerce/
│
├── admin/
│   ├── index.php
│   ├── users.php
│   ├── products.php
│   ├── transactions.php
│   ├── complaints.php
│   ├── delete-user.php
│   └── delete-product.php
│
├── assets/
│   ├── images/
│   └── style.css
│
├── config/
│   ├── app.php
│   ├── database.example.php
│   ├── mail.example.php
│   └── payment.example.php
│
├── database/
│   └── database_schema.sql
│
├── includes/
│   └── navbar.php
│
├── index.php
├── login.php
├── register.php
├── products.php
├── buy-product.php
├── cart.php
├── cart-checkout.php
├── payment.php
├── payment-return.php
├── payment-success.php
├── payment-cancel.php
├── payment-itn.php
├── orders.php
├── seller-orders.php
├── create-shop.php
├── upload-product.php
├── contact-admin.php
├── README.md
└── .gitignore
```

## Local Setup Instructions

1. Install XAMPP.
2. Copy the project folder into:

```text
C:\xampp\htdocs
```

3. Start Apache and MySQL from the XAMPP Control Panel.
4. Open phpMyAdmin.
5. Create a new database.
6. Import:

```text
database/database_schema.sql
```

7. Copy:

```text
config/database.example.php
```

and rename it to:

```text
config/database.php
```

8. Add your local database credentials:

```php
$servername = "localhost";
$username = "root";
$password = "";
$database = "your_database_name";
```

9. Copy:

```text
config/payment.example.php
```

and rename it to:

```text
config/payment.php
```

10. Add your PayFast Sandbox merchant details.
11. Open the project in your browser:

```text
http://localhost/
```

## Live Hosting Setup

The live project can be hosted on InfinityFree or another PHP/MySQL hosting provider.

For live hosting:

1. Upload the project files to the `htdocs` folder.
2. Import `database/database_schema.sql` into the hosting database.
3. Create a private `config/database.php` file with the hosting database credentials.
4. Create a private `config/payment.php` file with PayFast Sandbox credentials.
5. Make sure `SITE_URL` in `config/payment.php` matches the public website URL.

Example:

```php
define('SITE_URL', 'https://yourdomain.com');
```

## Important Security Notes

The following files should not be uploaded publicly with real credentials:

```text
config/database.php
config/payment.php
config/mail.php
.vscode/
uploads/
```

The repository includes example files instead:

```text
config/database.example.php
config/payment.example.php
config/mail.example.php
```

These example files show the required structure without exposing private passwords or keys.

## Database

The database structure is stored in:

```text
database/database_schema.sql
```

This file contains the table structure required to run the project.

Main database tables include:

* users
* products
* cart
* transactions
* complaints
* password_resets

## Testing Checklist

The system was tested for the following:

* User registration
* User login
* Seller shop creation
* Product upload
* Product browsing
* Category filtering
* Province filtering
* Cart functionality
* Checkout functionality
* PayFast Sandbox payment flow
* Cash on delivery
* Cash on collection
* Buyer order display
* Seller order display
* Admin user management
* Admin product management
* Admin complaint management
* Responsive layout

## Author

Bokang Tlhalefo Kelebonye
Student Number: EDUV4964814
Project: MyFRIEND C2C E-Commerce Platform

