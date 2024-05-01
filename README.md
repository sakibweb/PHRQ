# PHRQ
### PHRQ - PHP HTTP Request Library

PHRQ is a PHP library for sending HTTP requests using cURL and generating JavaScript code for XHR requests.

## Features
- Send HTTP requests from PHP using cURL.
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
