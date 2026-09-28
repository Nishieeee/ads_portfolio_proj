<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/BasicInfo.php';

class BasicInfoController extends BaseController {
    private BasicInfo $model;

    public function __construct(?PDO $db) {
        parent::__construct($db);
        $this->model = new BasicInfo($this->conn);
    }

    /**
     * Retrieve the primary developer profile information.
     * Public endpoint: GET /api/basic-info
     *
     * @return void
     */
    public function get(): void {
        $info = $this->model->get();

        if (!$info) {
            $this->sendResponse(null, 'No profile information recorded yet.', 200);
        }

        // Include computed helpers
        $info['full_name'] = $this->model->getFullName($info);
        $info['age'] = $this->model->getAge($info['birth_date'] ?? null);

        $this->sendResponse($info, 'Basic information retrieved successfully.');
    }

    /**
     * Update the primary profile information.
     * Admin-only endpoint: PUT /api/basic-info
     *
     * @return void
     */
    public function update(): void {
        // Enforce admin authentication
        $this->requireAuth();

        $input = $this->getJsonInput();
        if (empty($input)) {
            $this->sendError('No update data provided in request body.', [], 400);
        }

        // Validate essential required fields
        $missing = $this->validateRequired($input, ['first_name', 'last_name', 'role_title', 'tagline']);
        if (!empty($missing)) {
            $this->sendError('Missing required profile fields.', $missing, 400);
        }

        // Validate birth_date format if provided
        if (!empty($input['birth_date'])) {
            $d = DateTime::createFromFormat('Y-m-d', $input['birth_date']);
            if (!$d || $d->format('Y-m-d') !== $input['birth_date']) {
                $this->sendError('Invalid birth_date format. Expected YYYY-MM-DD.', ['birth_date'], 400);
            }
        }

        // Fetch current record to safely merge partial updates
        $current = $this->model->get();
        $payload = $current ? array_merge($current, $input) : $input;

        $success = $this->model->save($payload);

        if ($success) {
            $fresh = $this->model->get();
            $fresh['full_name'] = $this->model->getFullName($fresh);
            $fresh['age'] = $this->model->getAge($fresh['birth_date'] ?? null);

            $this->sendResponse($fresh, 'Basic information updated successfully.');
        } else {
            $this->sendError('Failed to update basic information.', [], 500);
        }
    }
}