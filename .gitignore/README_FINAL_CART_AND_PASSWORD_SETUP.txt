MYFRIEND FINAL UPDATE: CART + FORGOT PASSWORD

WHAT THIS VERSION ADDS
- Shopping cart with quantities and cart total.
- Add to Cart buttons on the Shop and Product Details pages.
- Cart checkout for multiple products.
- One payment for the complete cart.
- My Orders remains available as order history after checkout.
- Cart count in the navbar.
- Forgot Password link on the Login page.
- Secure one-time password reset tokens that expire after 30 minutes.
- Local password-reset preview mode and optional SMTP email mode.

IMPORTANT DATABASE STEP FOR AN EXISTING DATABASE
1. Start Apache and MySQL in XAMPP.
2. Open http://localhost/phpmyadmin
3. Select the myfrienddb database.
4. Click SQL.
5. Open this file in Notepad:
   _database/cart_and_password_reset_upgrade.sql
6. Copy all the SQL code into phpMyAdmin and click Go.

This creates:
- cart table
- password_resets table
- transactions.quantity column
- transactions.checkoutReference column
- users.sellerPhone column if it is missing

NEW OR EMPTY DATABASE
Import:
_database/myfrienddb_local_setup.sql

LOCAL WEBSITE SETUP
1. Copy all files and folders directly into C:\xampp\htdocs
2. Start Apache and MySQL.
3. Open http://localhost/
4. The project also works from a subfolder because config/app.php detects the folder automatically.

TEST THE CART
1. Log in as a buyer.
2. Open Shop.
3. Click Add to Cart or open View Details and choose a quantity.
4. Open Cart from the navbar.
5. Update quantities or remove products.
6. Click Proceed to Checkout.
7. Enter buyer contact and delivery details.
8. Continue to payment.
9. Use the demo card:
   Card: 4111 1111 1111 1111
   Expiry: 12/30
   CVV: 123
10. After successful payment, the cart is cleared and the products appear in My Orders.

TEST FORGOT PASSWORD LOCALLY
The default setting is preview mode.
1. Open Login.
2. Click Forgot password?
3. Enter the email of an existing account.
4. Click Create Reset Link.
5. A local testing button appears.
6. Open it, enter a new password twice, and submit.
7. Log in with the new password.

SEND REAL RESET EMAILS WITH SMTP
1. Open config/mail.php
2. Change:
   define('MAIL_MODE', 'preview');
   to:
   define('MAIL_MODE', 'smtp');
3. Complete the SMTP settings in that file:
   SMTP_HOST
   SMTP_PORT
   SMTP_ENCRYPTION
   SMTP_USERNAME
   SMTP_PASSWORD
   SMTP_FROM_EMAIL
   SMTP_FROM_NAME
4. For Gmail, use an app password instead of your normal Gmail password.
5. Test the reset process on the hosted website.

IMPORTANT SECURITY NOTES
- Do not upload real email passwords or payment secrets to GitHub.
- Do not share config/mail.php or config/payment.php after adding live credentials.
- Password reset tokens are stored as hashes and cannot be reused after a successful reset.
- The demo payment page does not transfer real money.
