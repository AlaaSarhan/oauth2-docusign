# Docusign Provider for OAuth 2.0 Client

[![Latest Version](https://img.shields.io/github/release/AlaaSarhan/oauth2-docusign.svg?style=flat-square)](https://github.com/AlaaSarhan/oauth2-docusign/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://travis-ci.org/AlaaSarhan/oauth2-docusign.svg?branch=master)](https://travis-ci.org/AlaaSarhan/oauth2-docusign)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/AlaaSarhan/oauth2-docusign.svg?style=flat-square)](https://scrutinizer-ci.com/g/AlaaSarhan/oauth2-docusign/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/AlaaSarhan/oauth2-docusign.svg?style=flat-square)](https://scrutinizer-ci.com/g/AlaaSarhan/oauth2-docusign)
[![Total Downloads](https://img.shields.io/packagist/dt/sarhan/oauth2-docusign.svg?style=flat-square)](https://packagist.org/packages/sarhan/oauth2-docusign)

This package provides Docusign OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require sarhan/oauth2-docusign
```

## Usage

Usage is the same as The League's OAuth client, using `\Sarhan\OAuth2\Client\Provider\Docusign` as the provider.

### Authorization Code Flow

```php
$provider = new \Sarhan\OAuth2\Client\Provider\Docusign([
    'clientId'          => '{docusign-integrator-key}',
    'clientSecret'      => '{docusign-integrator-key-secret}',
    'redirectUri'       => 'https://example.com/callback-url'
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getId());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

### Refreshing a Token

```php
$provider = new \Sarhan\OAuth2\Client\Provider\Docusign([
    'clientId'          => '{docusign-integrator-key}',
    'clientSecret'      => '{docusign-integrator-key-secret}',
    'redirectUri'       => 'https://example.com/callback-url'
]);

$token = $provider->getAccessToken('refresh_token', [
	'refresh_token' => '{refresh token}'
]);
```

## Vendor specific options

`sandbox`

when passed with `true` to the provider constructor, the provider will direct docuaign endpoint calls to docusign sandbox domain (account-d.docusign.com).

```php
$provider = new \Sarhan\OAuth2\Client\Provider\Docusign([
    'clientId'          => '{docusign-integrator-key}',
    'clientSecret'      => '{docusign-integrator-key-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
    'sandbox'           => true
]);
```


## Testing

```bash
$ ./vendor/bin/phpunit
```

or

```bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](https://github.com/AlaaSarhan/oauth2-docusign/blob/master/CONTRIBUTING.md) for details.


## License

The MIT License (MIT). Please see [License File](https://github.com/AlaaSarhan/oauth2-docusign/blob/master/LICENSE) for more information.
