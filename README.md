# IMAP-Cleaner
A php-based commandline tool that helps in cleaning up full IMAP mailboxes.

## Usage
If you have docker installed, it is dead simple to use this, just execute it with docker:
`docker run -it --rm cn-consult/imap-cleaner`.

Use `docker run -it --rm cn-consult/imap-cleaner mailbox:list -s <server> <email>` to list all mailbox of a your account
and `docker run -it --rm cn-consult/imap-cleaner mailbox:cleanup -s <server> <email> <mailbox>` to delete old mails.

Required parameters can be passed as arguments or options, but they are also asked interactivley if not specify.
This for example allows you to enter the password with the keyboard which is more secure than passing
it as command-line parameter.


## Development
You can implement your own changes and build a new version of the image with `docker build . -t my/imap-cleaner` and run
it afterwards with `docker run my/imap-cleaner`.

### Ideas for improvements
* Allow to choose mailbox interactiveley if not specified
* Allow to sort mailboxes by mail-count
* Try to autodetect mail-server with DNS lookups based on email

Feel free to open pull-requests with your feature additions!