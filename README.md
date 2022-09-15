# CartApp

## Email Testing

For development, the system uses [MailHog](https://github.com/mailhog/MailHog) that can be accessed
at http://localhost:8025. All emails sent from the system
will be available in this interface.

Notes:

- If the link to `/storage/example-user-import.xlsx` isn't working (found on the admin, import users page), make sure
  the symlink has been created. Run `php artisan storage:link` to create the symlink.

