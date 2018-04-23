Openprovider HTTP Library
=========================

[![Build Status](https://travis-ci.org/openprovider/http.png?branch=master)](https://travis-ci.org/openprovider/http)

A simple HTTP PHP library based on Curl, which helps to create HTTP request and get convenient response.

### Current version

1.0.7

### Examples

```php
use \Openprovider\Service\Http\Request;
use \Openprovider\Service\Http\Response;

$response = Request::get('google.com')->execute();
$status = $response->getHttpStatusCode();
if ($response->isSuccess) {
    $cookie = $response->getCookie();
    $header = $response->getHeader();
    $data = $response->getData();
} else {
    print_r($response->getErrorCode() . ': ' . $response->getErrorDescription());
}
```

```php
use \Openprovider\Service\Http\Request;
use \Openprovider\Service\Http\Response;

$request = new Request('website.com');
$response = $request->setFollowLocation(false)
    ->setMethod(Request::POST)
    ->setTimeout(10)
    ->setCookie('PREF=ID; Name=Noname')
    ->execute();
```

## Authors

[Igor Dolzhikov](https://github.com/takama)

## Contributors

All the contributors are welcome. If you would like to be the contributor please accept some rules.
- The pull requests will be accepted only in "develop" branch
- All modifications or additions should be tested

Thank you for your understanding!

## License

[MIT Public License](https://github.com/openprovider/http/blob/master/LICENSE)
