<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Certificate.php';

class CertificateController extends BaseController {
    private Certificate $model;

    public function __construct(?PDO $db) {
        parent::__construct($db);
        $this->model = new Certificate($this->conn);
    }

    /**
     * Retrieve all certificates & awards.
     * Public endpoint: GET /api/certificates
     *
     * @return void
     */
    public function getAll(): void {
        $certificates = $this->model->getAll();
        $this->sendResponse($certificates, 'Certificates retrieved successfully.');
    }

    /**
     * Retrieve a single certificate by ID.
     * Public endpoint: GET /api/certificates/{id}
     *
     * @param int $id
     * @return void
     */
    public function getById(int $id): void {
        $certificate = $this->model->getById($id);
        if (!$certificate) {
            $this->sendError("Certificate with ID {$id} not found.", [], 404);
        }
        $this->sendResponse($certificate, 'Certificate retrieved successfully.');
    }

    /**
     * Create a new certificate or award.
     * Admin-only endpoint: POST /api/certificates
     *
     * @return void
     */
    public function create(): void {
        $this->requireAuth();

        $input = $this->getJsonInput();

        $missing = $this->validateRequired($input, ['title', 'issuer']);
        if (!empty($missing)) {
            $this->sendError('Missing required certificate fields.', $missing, 400);
        }

        if (!empty($input['cert_url']) && !$this->validateUrl($input['cert_url'])) {
            $this->sendError('Invalid certificate verification URL format.', ['cert_url'], 400);
        }

        $newId = $this->model->create($input);
        if ($newId) {
            $created = $this->model->getById($newId);
            $this->sendResponse($created, 'Certificate created successfully.', 201);
        } else {
            $this->sendError('Failed to create certificate.', [], 500);
        }
    }

    /**
     * Update an existing certificate.
     * Admin-only endpoint: PUT /api/certificates/{id}
     *
     * @param int $id
     * @return void
     */
    public function update(int $id): void {
        $this->requireAuth();

        $existing = $this->model->getById($id);
        if (!$existing) {
            $this->sendError("Certificate with ID {$id} not found.", [], 404);
        }

        $input = $this->getJsonInput();
        if (empty($input)) {
            $this->sendError('No update data provided in request body.', [], 400);
        }

        $merged = array_merge($existing, $input);

        if (!empty($merged['cert_url']) && !$this->validateUrl($merged['cert_url'])) {
            $this->sendError('Invalid certificate verification URL format.', ['cert_url'], 400);
        }

        $success = $this->model->update($merged, $id);
        if ($success) {
            $updated = $this->model->getById($id);
            $this->sendResponse($updated, 'Certificate updated successfully.');
        } else {
            $this->sendError('Failed to update certificate.', [], 500);
        }
    }

    /**
     * Delete a certificate.
     * Admin-only endpoint: DELETE /api/certificates/{id}
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void {
        $this->requireAuth();

        $existing = $this->model->getById($id);
        if (!$existing) {
            $this->sendError("Certificate with ID {$id} not found.", [], 404);
        }

        $success = $this->model->delete($id);
        if ($success) {
            $this->sendResponse(['id' => $id], 'Certificate deleted successfully.');
        } else {
            $this->sendError('Failed to delete certificate.', [], 500);
        }
    }
}
