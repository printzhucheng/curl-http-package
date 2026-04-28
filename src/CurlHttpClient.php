<?php

declare(strict_types=1);

namespace Printzhucheng\CurlHttpPackage;

use InvalidArgumentException;
use RuntimeException;

/**
 * A simple and powerful cURL HTTP client for PHP.
 */
class CurlHttpClient
{
    /**
     * Base URL for requests
     */
    private ?string $baseUrl = null;

    /**
     * Default headers for all requests
     */
    private array $defaultHeaders = [];

    /**
     * Default timeout in seconds
     */
    private int $timeout = 30;

    /**
     * Whether to verify SSL certificate
     */
    private bool $verifySsl = true;

    /**
     * cURL handle
     */
    private $curl = null;

    /**
     * Last request information
     */
    private array $lastRequestInfo = [];

    /**
     * Constructor
     *
     * @param array $config Configuration options
     */
    public function __construct(array $config = [])
    {
        if (isset($config['base_url'])) {
            $this->baseUrl = rtrim($config['base_url'], '/');
        }
        if (isset($config['headers'])) {
            $this->defaultHeaders = $config['headers'];
        }
        if (isset($config['timeout'])) {
            $this->timeout = (int) $config['timeout'];
        }
        if (isset($config['verify_ssl'])) {
            $this->verifySsl = (bool) $config['verify_ssl'];
        }
    }

    /**
     * Set base URL
     *
     * @param string $baseUrl
     * @return self
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        return $this;
    }

    /**
     * Set default headers
     *
     * @param array $headers
     * @return self
     */
    public function setDefaultHeaders(array $headers): self
    {
        $this->defaultHeaders = $headers;
        return $this;
    }

    /**
     * Add a default header
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public function addDefaultHeader(string $name, string $value): self
    {
        $this->defaultHeaders[$name] = $value;
        return $this;
    }

    /**
     * Set timeout
     *
     * @param int $seconds
     * @return self
     */
    public function setTimeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * Set SSL verification
     *
     * @param bool $verify
     * @return self
     */
    public function setVerifySsl(bool $verify): self
    {
        $this->verifySsl = $verify;
        return $this;
    }

    /**
     * Send GET request
     *
     * @param string $url
     * @param array $params Query parameters
     * @param array $headers Additional headers
     * @return Response
     */
    public function get(string $url, array $params = [], array $headers = []): Response
    {
        if (!empty($params)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }
        return $this->request('GET', $url, [], $headers);
    }

    /**
     * Send POST request
     *
     * @param string $url
     * @param array|string $data Request body
     * @param array $headers Additional headers
     * @return Response
     */
    public function post(string $url, $data = [], array $headers = []): Response
    {
        return $this->request('POST', $url, $data, $headers);
    }

    /**
     * Send POST request with JSON body
     *
     * @param string $url
     * @param array $data
     * @param array $headers Additional headers
     * @return Response
     */
    public function postJson(string $url, array $data, array $headers = []): Response
    {
        $headers['Content-Type'] = 'application/json';
        return $this->post($url, json_encode($data), $headers);
    }

    /**
     * Send PUT request
     *
     * @param string $url
     * @param array|string $data Request body
     * @param array $headers Additional headers
     * @return Response
     */
    public function put(string $url, $data = [], array $headers = []): Response
    {
        return $this->request('PUT', $url, $data, $headers);
    }

    /**
     * Send PUT request with JSON body
     *
     * @param string $url
     * @param array $data
     * @param array $headers Additional headers
     * @return Response
     */
    public function putJson(string $url, array $data, array $headers = []): Response
    {
        $headers['Content-Type'] = 'application/json';
        return $this->put($url, json_encode($data), $headers);
    }

    /**
     * Send PATCH request
     *
     * @param string $url
     * @param array|string $data Request body
     * @param array $headers Additional headers
     * @return Response
     */
    public function patch(string $url, $data = [], array $headers = []): Response
    {
        return $this->request('PATCH', $url, $data, $headers);
    }

    /**
     * Send DELETE request
     *
     * @param string $url
     * @param array $headers Additional headers
     * @return Response
     */
    public function delete(string $url, array $headers = []): Response
    {
        return $this->request('DELETE', $url, [], $headers);
    }

    /**
     * Upload file
     *
     * @param string $url
     * @param string $field Field name
     * @param string $filePath File path
     * @param array $additionalData Additional form data
     * @param array $headers Additional headers
     * @return Response
     */
    public function upload(string $url, string $field, string $filePath, array $additionalData = [], array $headers = []): Response
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("File not found: {$filePath}");
        }

        $data = $additionalData;
        $data[$field] = new \CURLFile($filePath);

        return $this->post($url, $data, $headers);
    }

    /**
     * Send HTTP request
     *
     * @param string $method HTTP method
     * @param string $url URL
     * @param array|string $data Request body
     * @param array $headers Additional headers
     * @return Response
     */
    public function request(string $method, string $url, $data = [], array $headers = []): Response
    {
        $this->initCurl();

        // Build full URL
        $fullUrl = $this->buildUrl($url);

        // Set URL
        curl_setopt($this->curl, CURLOPT_URL, $fullUrl);

        // Set method
        $this->setMethod($method);

        // Set body
        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            if (is_array($data)) {
                $data = http_build_query($data);
            }
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
        }

        // Set headers
        $allHeaders = array_merge($this->defaultHeaders, $headers);
        $formattedHeaders = [];
        foreach ($allHeaders as $name => $value) {
            $formattedHeaders[] = "{$name}: {$value}";
        }
        if (!empty($formattedHeaders)) {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $formattedHeaders);
        }

        // Execute request
        $response = curl_exec($this->curl);

        // Get request info
        $this->lastRequestInfo = curl_getinfo($this->curl);

        // Check for errors
        if ($response === false) {
            $error = curl_error($this->curl);
            $errno = curl_errno($this->curl);
            throw new RuntimeException("cURL error ({$errno}): {$error}");
        }

        // Build response
        $statusCode = (int) $this->lastRequestInfo['http_code'];
        $headerSize = (int) $this->lastRequestInfo['header_size'];

        $responseHeaders = substr($response, 0, $headerSize);
        $responseBody = substr($response, $headerSize);

        return new Response($statusCode, $responseBody, $this->parseHeaders($responseHeaders));
    }

    /**
     * Get last request information
     *
     * @return array
     */
    public function getLastRequestInfo(): array
    {
        return $this->lastRequestInfo;
    }

    /**
     * Initialize cURL handle
     */
    private function initCurl(): void
    {
        $this->curl = curl_init();

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_MAXREDIRS, 5);

        if (!$this->verifySsl) {
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        }
    }

    /**
     * Set HTTP method
     *
     * @param string $method
     */
    private function setMethod(string $method): void
    {
        $method = strtoupper($method);

        switch ($method) {
            case 'GET':
                curl_setopt($this->curl, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($this->curl, CURLOPT_POST, true);
                break;
            case 'PUT':
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case 'PATCH':
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
                break;
            case 'DELETE':
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default:
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
        }
    }

    /**
     * Build full URL
     *
     * @param string $url
     * @return string
     */
    private function buildUrl(string $url): string
    {
        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }

        if ($this->baseUrl === null) {
            throw new InvalidArgumentException("Invalid URL: {$url}. No base URL set.");
        }

        return $this->baseUrl . '/' . ltrim($url, '/');
    }

    /**
     * Parse response headers
     *
     * @param string $headerString
     * @return array
     */
    private function parseHeaders(string $headerString): array
    {
        $headers = [];
        $lines = explode("\r\n", trim($headerString));

        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($name, $value) = explode(':', $line, 2);
                $headers[trim($name)] = trim($value);
            }
        }

        return $headers;
    }

    /**
     * Close cURL handle
     */
    public function close(): void
    {
        if ($this->curl !== null) {
            curl_close($this->curl);
            $this->curl = null;
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }
}