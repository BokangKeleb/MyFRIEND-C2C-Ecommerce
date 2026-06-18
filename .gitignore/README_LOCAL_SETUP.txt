MYFRIEND LOCAL SETUP

1. Copy everything inside this ZIP directly into:
   C:\xampp\htdocs

2. Start Apache and MySQL in XAMPP.

3. Open phpMyAdmin:
   http://localhost/phpmyadmin

4. Existing database:
   Select myfrienddb, click SQL, and run:
   _database/cart_and_password_reset_upgrade.sql

5. New or empty database:
   Import:
   _database/myfrienddb_local_setup.sql

6. Open the site:
   http://localhost/

7. Read the full feature instructions in:
   README_FINAL_CART_AND_PASSWORD_SETUP.txt

8. This ZIP uses local XAMPP settings in config/database.php.
   Change that file back to the hosting database credentials before uploading online.
