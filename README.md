# Cart Scheduler

***

# Please Note, these directions assume you are a developer and understand advanced concepts such as hosting, setting up a database, and using the command line. If these concepts are foreign to you, please find someone who can help you.

***

## Deployment

### cPanel Hosting

cPanel Hosting is a little problematic because the root domain typically needs to be served from the
directory `~/public_html`. This means the default laravel directory structure which is served
from `/public`. If you are serving from an addon domain, it's easier because you can setup a directory structure to
suit. For example, you could setup a domain `mydomain.example.com` and set the directory
to `~/mydomain.example.com/public`.

To get some advice on bypassing this, here are some resources:

- https://hafizmohammed.medium.com/how-to-deploy-laravel-in-cpanel-the-right-way-78d0a767d5a2
- https://filippotoso.medium.com/how-to-host-a-laravel-app-on-a-cpanel-shared-hosting-a45793be73c3

### Standard Deployment

To deploy a release requires the following steps:

1. Download the release from the [releases page](https://github.com/pixelated-au/CartApp/releases)
1. Extract the release to a folder on the server
1. *Note: Make sure you are running PHP 8.1 or higher*
1. Unzip the release.
    - If using cPanel, you can do this via the File Manager
1. Copy the `.env.example` file to `.env` and update the values as required.
    - For cPanel, you can often use sendmail as the mail server. The following settings often work in the mail section:
    - ```dotenv
      MAIL_MAILER=sendmail
      MAIL_HOST=localhost
      ``` 
1. SSH into the server if you haven't yet already.
1. Run the following command to enable the Laravel cache:
    ```bash
    php artisan key:generate \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan event:cache \
    && php artisan storage:link
    ```
1. Or for cPanel, if the default system PHP version is not PHP 8.1, you may be able to use the following:
    ```bash
    ea-php81 artisan key:generate \
    && ea-php81 artisan config:cache \
    && ea-php81 artisan route:cache \
    && ea-php81 artisan view:cache \
    && ea-php81 artisan event:cache \
    && ea-php81 artisan storage:link
    ```
1. Run the following command to migrate/install the database:
    - `php artisan migrate`
1. Setup the cron job to run the following command every minute:
    - `php artisan schedule:run >> /dev/null 2>&1`
        - eg: `* * * * * cd /home/[MY_ACCOUNT]/[MY_APP] && php artisan schedule:run >> /dev/null 2>&1`
          <- Note, you may need to replace `php` with `ea-php81` if you're using cPanel
1. Create the admin user. Note, this can only be run once.:
    - `php artisan carts:create-user <name> <email> <phone> <gender> [<password>]`
1. Navigate to the site and login with the admin user you just created.
    - If you are having a server issue (eg 500 error), review the log file at `storage/logs/laravel.log`.
    - If you have this issue: `Your serialized closure might have been modified or it's unsafe to be unserialized`,
      you may need to run the following command: `php artisan route:clear`

## Email Testing

For development, the system uses [MailHog](https://github.com/mailhog/MailHog) that can be accessed
at http://localhost:8025. All emails sent from the system
will be available in this interface.

## Notes:

- If the link to `/storage/example-user-import.xlsx` isn't working (found on the admin, import users page), make sure
  the symlink has been created. Run `php artisan storage:link` to create the symlink.

## TODO:

- [ ] Implement Testing
- [x] Implement feature for admin to remove a user from a shift in addition to moving them to another shift
  - [ ] Create relationships between users so admin can assign user and their relationship to a shift
- [ ] Allow users to book another user onto a shift
