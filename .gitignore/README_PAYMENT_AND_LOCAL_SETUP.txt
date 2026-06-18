MYFRIEND WEBSITE SETUP

For the latest setup instructions, open:
README_FINAL_CART_AND_PASSWORD_SETUP.txt

PAYMENT MODE
- Default: demo mode.
- Test card: 4111 1111 1111 1111
- Expiry: 12/30
- CVV: 123

REAL PAYFAST
1. Open config/payment.php.
2. Change PAYMENT_MODE to payfast_sandbox or payfast_live.
3. Enter the merchant ID, merchant key and passphrase.
4. The hosted website must use HTTPS and the notify URL must be publicly reachable.

Do not upload files containing live passwords or secret keys to GitHub.
