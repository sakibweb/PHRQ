# PHRQ
### PHRQ - PHP HTTP Request Library

PHRQ is a PHP library for sending HTTP requests using cURL and generating JavaScript code for XHR requests.

## Features
- Send HTTP requests from PHP using cURL.
- Manage HTTP response headers for API responses and file downloads.
- Stream data to clients using HTTP headers.
- Generate JavaScript code for XHR requests.
- Supports various HTTP methods: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS, CUSTOM.
- Ability to set custom request headers and options.
- Automatically decodes JSON responses.

## Usage
### Sending HTTP Request from PHP
To send an HTTP request from PHP using cURL:

```
$response = PHRQ::php('GET', 'https://example.com', ['Content-Type: application/json']);
echo $response;
```

### Setting HTTP Response Headers for API Responses
You can set custom HTTP headers for your API responses using the header method:
```
PHRQ::header('GET', 'https://example.com', 'application/json', [
    'Cache-Control' => 'no-cache',
    'Access-Control-Allow-Methods' => 'GET, POST, PUT',
]);
```
