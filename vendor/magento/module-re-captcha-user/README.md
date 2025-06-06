# Magento reCAPTCHA

Google reCAPTCHA ensures that a human being, rather than a computer (or “bot”), is interacting with your website. Unlike the standard Magento CAPTCHA, Google reCAPTCHA provides enhanced security with a selection of different display options and methods. Additional website traffic information is available in the dashboard of your Google reCAPTCHA account.

This module provides the reCAPTCHA implementations related to user actions.

For more information please visit the [reCAPTCHA documentation](https://experienceleague.adobe.com/en/docs/commerce-admin/systems/security/captcha/security-google-recaptcha).

## Emergency commandline disable for Admin panel Login page:

Can disable Google reCAPTCHA for Admin Panel Login page from command-line:

`php bin/magento security:recaptcha:disable-for-user-login`

This will disable Google reCAPTCHA for Admin Panel Login page form.

## Emergency commandline disable for Admin panel Reset Password page:

Can disable Google reCAPTCHA for Admin panel Reset Password page from command-line:

`php bin/magento security:recaptcha:disable-for-user-forgot-password`

This will disable Google reCAPTCHA for Admin panel Reset Password page form.
