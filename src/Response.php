<?php

declare(strict_types=1);

namespace Printzhucheng\CurlHttpPackage;

/**
 * HTTP Response class
 */
class Response
{
    /**
     * HTTP status code
     */
    private int $statusCode;

    /**
     * Response body
     */
    private string $body;

    /**
     * Response headers
     */
    private array $headers;

    /**
     * Constructor
     *
     * @param int $statusCode
     * @param string $body
     * @param array $headers
     */
    public function __construct(int $statusCode, string $body, array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->headers = $headers;
    }

    /**
     * Get status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get response body
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Get response body as JSON
     *
     * @param bool $assoc Return as associative array
     * @return mixed
     */
    public function json(bool $assoc = true)
    {
        return json_decode($this->body, $assoc);
    }

    /**
     * Get response headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get a specific header
     *
     * @param string $name
     * @return string|null
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Check if response is successful (2xx)
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Check if response is a client error (4xx)
     *
     * @return bool
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Check if response is a server error (5xx)
     *
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Check if response is OK (200)
     *
     * @return bool
     */
    public function isOk(): bool
    {
        return $this->statusCode === 200;
    }

    /**
     * Check if response is created (201)
     *
     * @return bool
     */
    public function isCreated(): bool
    {
        return $this->statusCode === 201;
    }

    /**
     * Check if response is not found (404)
     *
     * @return bool
     */
    public function isNotFound(): bool
    {
        return $this->statusCode === 404;
    }

    /**
     * Convert response to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->body;
    }
}