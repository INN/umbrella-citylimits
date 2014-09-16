# CityLimits.org Documentation

## Job listings

    TODO

## Event Listings

### Notes

- Gravity Forms, Events Calendar and Events Calendar Pro plugins should be enabled for event listings.
- Since we're using Gravity Forms to feed data to the Events Calendar plugin, the "Event calendar submission form" should not be modified.
- If any form fields are changed, there is a check in place that will correct those changes so that the connection between Gravity Forms and Events Calendar will not be broken.
- You CAN modify the price to list an event by editing the "Event listing" field of the "Event calendar submission form" — the very last field on the form edit page.
- Accepting payments requires the "PayPal Add-On" for Gravity Forms.

### Set up PayPal to accept payments for event listings:

- From the WordPress dashboard, go to Forms > PayPal
- If there is NOT an existing entry for "Event calendar submission form" click "Add New"
- Enter your PayPal email address, set the "Mode" to "Production," set "Transaction Type" to "Products and Services"
- TODO ...

## Registration

- The registration page is a standard WordPress page (titled "Register") with a Largo registration short code embedded in it.
- In the event that it is lost or accidentally modified, the short code for CityLimits.org is:

    `[largo_registration_form first_name last_name]`.

- The URL for the Register page is "/register." Other components of the CityLimits.org theme depend on this, so please don't alter the URL.

## Newsletter sign up

- The Constant Contact plugin should be enabled and configured with your username and password.
- The registration page will allow users to choose which email lists they would like to subscribe to.
- Users can also manage their newsletter preferences via the standard WP profiled edit page.

## Donations

- To enable the donate link on your site.
- From the WordPress dashboard: Appearance > Theme Options > Basic Settings
- Check the box next to "Show a button in the top header to link to your donation page or form."
- You'll need the link to your donate page, which you can get by creating a donate button via PayPal.