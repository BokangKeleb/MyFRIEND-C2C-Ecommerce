MYFRIEND CASH ON DELIVERY + COLLECTION UPDATE

1. In InfinityFree phpMyAdmin, select the live database.
2. Open the SQL tab.
3. Run the contents of _database/cod_collection_upgrade.sql.
4. Copy these files into your local C:\xampp\htdocs folder:
   - cart-checkout.php
   - complete-order.php
   - orders.php
   - payment.php
   - payment-success.php
   - seller-orders.php
   - create-shop.php
   - upload-product.php
5. Replace the existing files.
6. Because upload-on-save is enabled, open each replaced PHP file in VS Code and press Ctrl+S.
   Alternatively, right-click each file and use SFTP: Upload File.
7. Do not upload the _database folder through SFTP.
8. Test all four combinations:
   - Delivery + Online Payment
   - Delivery + Cash on Delivery
   - Collection + Online Payment
   - Collection + Cash on Collection

Cash orders:
- Do not open the card-payment page.
- Clear the cart immediately after the order is created.
- Appear in My Orders and Shop Orders.
- Become Paid and Completed when the seller marks them Delivered or Collected.

Online orders:
- Continue to payment.php.
- Clear the cart only after successful payment.
