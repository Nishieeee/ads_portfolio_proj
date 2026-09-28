<?php

require_once __DIR__ . '/../Models/AdminUser.php';

abstract class BaseController {
    protected ?PDO $conn;
    protected ?array $currentUser = null;

    /**
     * Constructor accepting PDO database connection.
     *
     * @param PDO|null $db
     */
    public function __construct(?PDO $db) {
        $this->conn = $db;
    }

    /**
     * Parse, decode, and sanitize raw JSON request body into an associative array.
     *
     * @param bool $sanitize Whether to apply XSS/HTML sanitization (default: true)
     * @return array
     */
    protected function getJsonInput(bool $sanitize = true): array {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        return $sanitize ? $this->sanitizeInput($decoded) : $decoded;
    }

    /**
     * Recursively sanitize input data to guard against XSS and control character injection.
     *
     * @param mixed $data
     * @return mixed
     */
    protected function sanitizeInput(mixed $data): mixed {
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                $cleanKey = is_string($key) ? trim(strip_tags($key)) : $key;
                $sanitized[$cleanKey] = $this->sanitizeInput($value);
            }
            return $sanitized;
        }

        if (is_string($data)) {
            // Remove null-bytes and trim leading/trailing whitespace
            $clean = str_replace(chr(0), '', trim($data));
            // Convert special characters to HTML entities to prevent stored XSS
            return htmlspecialchars($clean, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        return $data;
    }

    /**
     * Validate the presence of non-empty required fields in an input array.
     * Returns an array of missing field names.
     *
     * @param array $data
     * @param array $requiredFields
     * @return array
     */
    protected function validateRequired(array $data, array $requiredFields): array {
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                $missing[] = $field;
            }
        }
        return $missing;
    }

    /**
     * Validate an email address format.
     *
     * @param string $email
     * @return bool
     */
    protected function validateEmail(string $email): bool {
        return (bool) filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate a URL format (requires http:// or https://).
     *
     * @param string $url
     * @return bool
     */
    protected function validateUrl(string $url): bool {
        $trimmed = trim($url);
        if (!filter_var($trimmed, FILTER_VALIDATE_URL)) {
            return false;
        }
        return (bool) preg_match('/^https?:\/\//i', $trimmed);
    }

    /**
     * Validate string length range.
     *
     * @param string $val
     * @param int $min
     * @param int $max
     * @return bool
     */
    protected function validateLength(string $val, int $min = 1, int $max = 255): bool {
        $len = mb_strlen(trim($val), 'UTF-8');
        return $len >= $min && $len <= $max;
    }

    /**
     * Authentication Guard: Require an active authenticated session or valid Bearer token.
     * Terminates execution with 401 Unauthorized if verification fails.
     *
     * @return array Authenticated user information
     */
    protected function requireAuth(): array {
        // 1. Check Bearer token from headers
        $token = $this->getBearerToken();

        if ($token) {
            $userModel = new AdminUser($this->conn);
            $user = $userModel->getByToken($token);
            if ($user) {
                $this->currentUser = $user;
                return $user;
            }
        }

        // 2. Check active PHP session as fallback
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        if (isset($_SESSION['admin_user']) && is_array($_SESSION['admin_user'])) {
            $this->currentUser = $_SESSION['admin_user'];
            return $_SESSION['admin_user'];
        }

        // Unauthorized
        $this->sendError('Unauthorized access. A valid authentication token or session is required.', [], 401);
        exit();
    }

    /**
     * Extract the Bearer token from the incoming HTTP Authorization header.
     *
     * @return string|null
     */
    protected function getBearerToken(): ?string {
        $headers = null;

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } elseif (function_exists('getallheaders')) {
            $headers = getallheaders();
        }

        $authHeader = null;
        if (is_array($headers)) {
            // Case-insensitive lookup for Authorization or X-Auth-Token
            foreach ($headers as $key => $value) {
                if (strcasecmp($key, 'Authorization') === 0) {
                    $authHeader = $value;
                    break;
                }
                if (strcasecmp($key, 'X-Auth-Token') === 0) {
                    return trim($value);
                }
            }
        }

        // Fallback to server variables
        if (!$authHeader && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (!$authHeader && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        if ($authHeader && preg_match('/Bearer\s(\S+)/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Send a standardized JSON success response and terminate execution.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return void
     */
    protected function sendResponse($data = null, string $message = '', int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');

        $response = [
            'success' => true,
        ];

        if (!empty($message)) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Send a standardized JSON error response and terminate execution.
     *
     * @param string $message
     * @param array $errors
     * @param int $statusCode
     * @return void
     */
    protected function sendError(string $message, array $errors = [], int $statusCode = 400): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');

        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        echo json_encode($response);
        exit();
    }
}
