<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Education.php';

class EducationController extends BaseController {
    private Education $model;

    public function __construct(?PDO $db) {
        parent::__construct($db);
        $this->model = new Education($this->conn);
    }

    /**
     * Retrieve all education records sorted chronologically.
     * Public endpoint: GET /api/education
     *
     * @return void
     */
    public function getAll(): void {
        $educations = $this->model->getAll();
        $this->sendResponse($educations, 'Education records retrieved successfully.');
    }

    /**
     * Retrieve a single education record by ID.
     * Public endpoint: GET /api/education/{id}
     *
     * @param int $id
     * @return void
     */
    public function getById(int $id): void {
        $education = $this->model->getById($id);
        if (!$education) {
            $this->sendError("Education record with ID {$id} not found.", [], 404);
        }
        $this->sendResponse($education, 'Education record retrieved successfully.');
    }

    /**
     * Create a new education record.
     * Admin-only endpoint: POST /api/education
     *
     * @return void
     */
    public function create(): void {
        $this->requireAuth();

        $input = $this->getJsonInput();

        $required = ['school_name', 'course', 'date_display'];
        $missing = $this->validateRequired($input, $required);
        if (!empty($missing)) {
            $this->sendError('Missing required education fields.', $missing, 400);
        }

        if (empty($input['date_start'])) {
            $input['date_start'] = date('Y-m-d');
        }

        $this->validateDates($input);

        $newId = $this->model->create($input);
        if ($newId) {
            $created = $this->model->getById($newId);
            $this->sendResponse($created, 'Education record created successfully.', 201);
        } else {
            $this->sendError('Failed to create education record.', [], 500);
        }
    }

    /**
     * Update an existing education record.
     * Admin-only endpoint: PUT /api/education/{id}
     *
     * @param int $id
     * @return void
     */
    public function update(int $id): void {
        $this->requireAuth();

        $existing = $this->model->getById($id);
        if (!$existing) {
            $this->sendError("Education record with ID {$id} not found.", [], 404);
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
            $this->sendResponse($updated, 'Education record updated successfully.');
        } else {
            $this->sendError('Failed to update education record.', [], 500);
        }
    }

    /**
     * Delete an education record.
     * Admin-only endpoint: DELETE /api/education/{id}
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void {
        $this->requireAuth();

        $existing = $this->model->getById($id);
        if (!$existing) {
            $this->sendError("Education record with ID {$id} not found.", [], 404);
        }

        $success = $this->model->delete($id);
        if ($success) {
            $this->sendResponse(['id' => $id], 'Education record deleted successfully.');
        } else {
            $this->sendError('Failed to delete education record.', [], 500);
        }
    }

    /**
     * Validate date_start and date_end formats.
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
