# Shopping List

A simple shopping list app for Mayden coding challenge. You can add items with prices, tick them off as you shop, reorder them, set a spending limit and email the list to someone. Full auth capabilities. 

## Build

- **Backend:** Laravel 13. The shopping list logic is in `app/Http/Controllers/ShoppingListController.php`, with an `Item` model that belongs to each `User`.
- **Frontend:** Vue 3 with Inertia, styled with Tailwind CSS.
- **Login:** Laravel Jetstream, which handles registration, login and password reset.
- **Database:** MySQL, which stores the items, their order, what's been bought and each user's spending limit.
- **Email:** the shared list is an HTML email in `resources/views/mail/shopping-list.blade.php`.
- **Docker:** runs the app (PHP + Nginx), MySQL and Vite. The `app` container sets itself up on first start.

## Running it

You only need Docker Desktop.

1. Open a terminal in this folder.
2. Run:

   ```bash
   docker compose up
   ```

3. Open http://localhost:8000 and register an account.

The first start takes a few minutes while it sets everything up.

## Emails

All emails sent are received by a Mailtrap sandbox.

To receive emails, you need your own Mailtrap login:

1. Sign up for a free account at [mailtrap.io](https://mailtrap.io).
2. Go to **Sandboxes**, open your inbox and find its **SMTP** username and password.
3. Put them in the `.env` file in this folder. The file is created the first time you run the app.

   ```
   MAIL_USERNAME=your-mailtrap-username
   MAIL_PASSWORD=your-mailtrap-password
   ```

4. Restart the app:

   ```bash
   docker compose restart app
   ```
