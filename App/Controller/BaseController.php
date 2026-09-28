<?php

abstract class BaseController {
    protected ?PDO $conn;

    /**
     * Constructor accepting PDO database connection.
     *
     * @param PDO|null $db
     */
    public function __construct(?PDO $db) {
        $this->conn = $db;
    }

    /**
     * Parse and decode raw JSON request body into an associative array.
     *
     * @return array
     */
    protected function getJsonInput(): array {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
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
}
