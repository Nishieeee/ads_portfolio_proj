<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/AdminUser.php';

class AuthController extends BaseController {
    private AdminUser $model;

    public function __construct(?PDO $db) {
        parent::__construct($db);
        $this->model = new AdminUser($this->conn);
    }

    /**
     * Authenticate admin user with username and password.
     * POST /api/auth/login
     *
     * @return void
     */
    public function login(): void {
        $input = $this->getJsonInput(false); // Do not escape password special characters

        $missing = $this->validateRequired($input, ['username', 'password']);
        if (!empty($missing)) {
            $this->sendError('Username and password are required.', $missing, 400);
        }

        $username = trim($input['username']);
        $password = (string) $input['password'];

        $user = $this->model->getByUsername($username);
        if (!$user || !$this->model->verifyPassword($password, $user['password_hash'])) {
            $this->sendError('Invalid username or password.', [], 401);
        }

        // Generate a 7-day cryptographically secure Bearer token
        $token = $this->model->generateToken((int) $user['id']);

        // Start session as optional fallback
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        $_SESSION['admin_user'] = [
            'id'       => (int) $user['id'],
            'username' => $user['username'],
        ];

        $this->sendResponse([
            'token' => $token,
            'user'  => [
                'id'       => (int) $user['id'],
                'username' => $user['username'],
            ],
        ], 'Login successful.');
    }

    /**
     * Invalidate active session and revoke Bearer token.
     * POST /api/auth/logout
     *
     * @return void
     */
    public function logout(): void {
        $token = $this->getBearerToken();

        if ($token) {
            $user = $this->model->getByToken($token);
            if ($user) {
                $this->model->revokeToken((int) $user['id']);
            }
        }

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }

        $this->sendResponse(null, 'Logged out successfully.');
    }

    /**
     * Get current authenticated user details.
     * GET /api/auth/me
     *
     * @return void
     */
    public function me(): void {
        $user = $this->requireAuth();
        $this->sendResponse([
            'id'       => (int) $user['id'],
            'username' => $user['username'],
        ], 'Authentication verified.');
    }
}
