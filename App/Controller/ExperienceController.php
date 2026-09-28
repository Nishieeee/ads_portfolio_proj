<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Experience.php';

class ExperienceController extends BaseController {
    private Experience $model;

    public function __construct(?PDO $db) {
        parent::__construct($db);
        $this->model = new Experience($this->conn);
    }

    /**
     * Retrieve all work experience entries sorted chronologically.
     * Public endpoint: GET /api/experience
     *
     * @return void
     */
    public function getAll(): void {
        $experiences = $this->model->getAll();
        $this->sendResponse($experiences, 'Work experience entries retrieved successfully.');
    }

    /**
     * Retrieve a single work experience entry by ID.
     * Public endpoint: GET /api/experience/{id}
     *
     * @param int $id
     * @return void
     */
    public function getById(int $id): void {
        $experience = $this->model->getById($id);
        if (!$experience) {
            $this->sendError("Experience entry with ID {$id} not found.", [], 404);
        }
        $this->sendResponse($experience, 'Experience entry retrieved successfully.');
    }

    /**
     * Create a new work experience entry.
     * Admin-only endpoint: POST /api/experience
     *
     * @return void
     */
    public function create(): void {
        $this->requireAuth();

        $input = $this->getJsonInput();

        $required = ['job_title', 'company_name', 'description_1', 'description_2', 'description_3', 'date_start', 'date_display'];
        $missing = $this->validateRequired($input, $required);
        if (!empty($missing)) {
            $this->sendError('Missing required experience fields.', $missing, 400);
        }

        $this->validateDates($input);

        $newId = $this->model->create($input);
        if ($newId) {
            $created = $this->model->getById($newId);
            $this->sendResponse($created, 'Experience entry created successfully.', 201);
        } else {
            $this->sendError('Failed to create experience entry.', [], 500);
        }
    }

    /**
     * Update an existing work experience entry.
     * Admin-only endpoint: PUT /api/experience/{id}
     *
     * @param int $id
     * @return void
     */
    public function update(int $id): void {
        $this->requireAuth();

        $existing = $this->model->getById($id);
        if (!$existing) {
            $this->sendError("Experience entry with ID {$id} not found.", [], 404);
        }

        $input = $this->getJsonInput();
        if (empty($input)) {
            $this->sendError('No update data provided in request body.', [], 400);
        }

        $merged = array_merge($existing, $input);
        $this->validateDates($merged);

        $success = $this->model->update($merged, $id);
        if ($success) {
            $updated = $this->model->getById($id);
            $this->sendResponse($updated, 'Experience entry updated successfully.');
        } else {
            $this->sendError('Failed to update experience entry.', [], 500);
        }
    }

    /**
     * Delete an experience entry by ID.
     * Admin-only endpoint: DELETE /api/experience/{id}
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void {
        $this->requireAuth();

        $existing = $this->model->getById($id);
        if (!$existing) {
            $this->sendError("Experience entry with ID {$id} not found.", [], 404);
        }

        $success = $this->model->delete($id);
        if ($success) {
            $this->sendResponse(['id' => $id], 'Experience entry deleted successfully.');
        } else {
            $this->sendError('Failed to delete experience entry.', [], 500);
        }
    }

    /**
     * Validate date_start and optional date_end formatting.
     *
     * @param array $data
     * @return void
     */
    private function validateDates(array $data): void {
        if (!empty($data['date_start'])) {
            $d = DateTime::createFromFormat('Y-m-d', $data['date_start']);
            if (!$d || $d->format('Y-m-d') !== $data['date_start']) {
                $this->sendError('Invalid date_start format. Expected YYYY-MM-DD.', ['date_start'], 400);
            }
        }

        if (!empty($data['date_end'])) {
            $d = DateTime::createFromFormat('Y-m-d', $data['date_end']);
            if (!$d || $d->format('Y-m-d') !== $data['date_end']) {
                $this->sendError('Invalid date_end format. Expected YYYY-MM-DD.', ['date_end'], 400);
            }
        }
    }
}
