# CartApp

## Email Testing

For development, the system uses [MailHog](https://github.com/mailhog/MailHog) that can be accessed
at http://localhost:8025. All emails sent from the system
will be available in this interface.

## Deployment

To deploy a release requires the following steps:

1. Download the release from the [releases page](https://github.com/pixelated-au/CartApp/releases)
2. Extract the release to a folder on the server
1. Unzip the release.
    - If using cPanel, you can do this via the File Manager
1. Copy the `.env.example` file to `.env` and update the values as required.
2. SSH into the server if you haven't yet already.
2. Run the following command for Composer. Note, you'll need PHP 8.1 installed on the server.
    - cPanel: `ea-php81 /opt/cpanel/composer/bin/composer install --optimize-autoloader --no-dev`
    - Regular: `composer install --optimize-autoloader --no-dev`
2. Run the following command to enable the Laravel cache:
    - `php artisan config:cache`
    - `php artisan route:cache`
    - `php artisan view:cache`
    - `php artisan event:cache`
    - `php artisan storage:link`
    - `php artisan key:generate`
1. Run the following command to migrate/install the database:
    - `php artisan migrate`

## Notes:

- If the link to `/storage/example-user-import.xlsx` isn't working (found on the admin, import users page), make sure
  the symlink has been created. Run `php artisan storage:link` to create the symlink.

