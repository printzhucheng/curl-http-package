# Curl HTTP Package

A simple and powerful cURL HTTP client for PHP. Supports GET, POST, PUT, PATCH, DELETE requests, JSON handling, file uploads, and more.

## Installation

```bash
composer require printzhucheng/curl-http-package
```

## Requirements

- PHP >= 7.4
- cURL extension

## Quick Start

```php
use Printzhucheng\CurlHttpPackage\CurlHttpClient;

// Create client instance
$client = new CurlHttpClient();

// Simple GET request
$response = $client->get('https://api.example.com/users');
echo $response->getBody();

// With query parameters
$response = $client->get('https://api.example.com/users', ['page' => 1, 'limit' => 10]);

// Check if successful
if ($response->isSuccessful()) {
    $data = $response->json();
    print_r($data);
}
```

## Usage

### Configuration

```php
$client = new CurlHttpClient([
    'base_url' => 'https://api.example.com',
    'timeout' => 60,
    'verify_ssl' => true,
    'headers' => [
        'Accept' => 'application/json',
        'X-API-Key' => 'your-api-key'
    ]
]);
```

### GET Request

```php
// Simple GET
$response = $client->get('/users');

// With query parameters
$response = $client->get('/users', ['status' => 'active', 'role' => 'admin']);

// With custom headers
$response = $client->get('/users', [], ['Authorization' => 'Bearer token']);
```

### POST Request

```php
// Form data
$response = $client->post('/users', ['name' => 'John', 'email' => 'john@example.com']);

// JSON data
$response = $client->postJson('/users', ['name' => 'John', 'email' => 'john@example.com']);

// Raw body
$response = $client->post('/api/endpoint', '{"key":"value"}', ['Content-Type' => 'application/json']);
```

### PUT Request

```php
// Form data
$response = $client->put('/users/1', ['name' => 'John Updated']);

// JSON data
$response = $client->putJson('/users/1', ['name' => 'John Updated']);
```

### PATCH Request

```php
$response = $client->patch('/users/1', ['status' => 'inactive']);
```

### DELETE Request

```php
$response = $client->delete('/users/1');
```

### File Upload

```php
// Single file
$response = $client->upload('/upload', 'file', '/path/to/file.jpg');

// File with additional data
$response = $client->upload('/upload', 'file', '/path/to/file.jpg', ['user_id' => 1, 'type' => 'avatar']);
```

### Working with Response

```php
$response = $client->get('/users/1');

// Get status code
$statusCode = $response->getStatusCode();

// Get body as string
$body = $response->getBody();

// Get body as JSON (array)
$data = $response->json();

// Get body as JSON (object)
$data = $response->json(false);

// Get all headers
$headers = $response->getHeaders();

// Get specific header
$contentType = $response->getHeader('Content-Type');

// Check response status
if ($response->isSuccessful()) {
    // 2xx response
}

if ($response->isOk()) {
    // 200 response
}

if ($response->isCreated()) {
    // 201 response
}

if ($response->isClientError()) {
    // 4xx response
}

if ($response->isServerError()) {
    // 5xx response
}

if ($response->isNotFound()) {
    // 404 response
}
```

### Fluent Interface

```php
$client = new CurlHttpClient();

$client->setBaseUrl('https://api.example.com')
       ->setTimeout(60)
       ->setVerifySsl(false)
       ->addDefaultHeader('Authorization', 'Bearer token')
       ->addDefaultHeader('Accept', 'application/json');

$response = $client->get('/users');
```

### Error Handling

```php
use Printzhucheng\CurlHttpPackage\CurlHttpClient;
use RuntimeException;

try {
    $client = new CurlHttpClient(['timeout' => 5]);
    $response = $client->get('https://api.example.com/users');
    
    if (!$response->isSuccessful()) {
        echo "Request failed with status: " . $response->getStatusCode();
    }
} catch (RuntimeException $e) {
    echo "cURL error: " . $e->getMessage();
}
```

## API Reference

### CurlHttpClient

| Method | Description |
|--------|-------------|
| `get(string $url, array $params = [], array $headers = [])` | Send GET request |
| `post(string $url, $data = [], array $headers = [])` | Send POST request |
| `postJson(string $url, array $data, array $headers = [])` | Send POST request with JSON body |
| `put(string $url, $data = [], array $headers = [])` | Send PUT request |
| `putJson(string $url, array $data, array $headers = [])` | Send PUT request with JSON body |
| `patch(string $url, $data = [], array $headers = [])` | Send PATCH request |
| `delete(string $url, array $headers = [])` | Send DELETE request |
| `upload(string $url, string $field, string $filePath, array $additionalData = [], array $headers = [])` | Upload file |
| `request(string $method, string $url, $data = [], array $headers = [])` | Send custom request |
| `setBaseUrl(string $baseUrl)` | Set base URL |
| `setTimeout(int $seconds)` | Set timeout |
| `setVerifySsl(bool $verify)` | Set SSL verification |
| `setDefaultHeaders(array $headers)` | Set default headers |
| `addDefaultHeader(string $name, string $value)` | Add default header |
| `getLastRequestInfo()` | Get last request info |

### Response

| Method | Description |
|--------|-------------|
| `getStatusCode()` | Get HTTP status code |
| `getBody()` | Get response body as string |
| `json(bool $assoc = true)` | Get response body as JSON |
| `getHeaders()` | Get all response headers |
| `getHeader(string $name)` | Get specific header |
| `isSuccessful()` | Check if 2xx response |
| `isOk()` | Check if 200 response |
| `isCreated()` | Check if 201 response |
| `isClientError()` | Check if 4xx response |
| `isServerError()` | Check if 5xx response |
| `isNotFound()` | Check if 404 response |

## License

MIT License