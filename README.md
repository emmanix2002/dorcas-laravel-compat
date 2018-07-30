Dorcas Laravel Compat
=============

A small pack of parts to make Dorcas play well with Laravel.

## Authentication

The Dorcas PHP SDK provides some utility functions for creating user accounts, and authenticating with 
a username-password combination (provided you have details for a Password Grant client).

To integrate your Laravel install with Dorcas for user authentication, you should make changes to your 
`config/auth.php` file.

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'dorcas', // formerly users
    ],

    'api' => [
        'driver' => 'token',
        'provider' => 'users',
    ],
],
```

This package registers a new `UserProvider` which you can use with your installation.