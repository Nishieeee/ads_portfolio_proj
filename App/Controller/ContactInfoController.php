<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/ContactInfo.php';

class ContactInfoController extends BaseController {
    private ContactInfo $model;
    private array $allowedTypes = ['email', 'phone_no', 'url'];

    public function __construct(?PDO $db) {
        parent::__construct($db);
        $this->model = new ContactInfo($this->conn);
    }

    /**
     * Retrieve all available contact channels.
     * Public endpoint: GET /api/contact-info
     *
     * @return void
     */
    public function getAll(): void {
        $contacts = $this->model->getAll();
        $this->sendResponse($contacts, 'Contact channels retrieved successfully.');
    }

    /**
     * Retrieve a single contact channel by ID.
     * Public endpoint: GET /api/contact-info/{id}
     *
     * @param int $id
     * @return void
     */
    public function getById(int $id): void {
        $contact = $this->model->getById($id);
        if (!$contact) {
            $this->sendError("Contact channel with ID {$id} not found.", [], 404);
        }
        $this->sendResponse($contact, 'Contact channel retrieved successfully.');
    }

    /**
     * Create a new contact channel.
     * Admin-only endpoint: POST /api/contact-info
     *
     * @return void
     */
    public function create(): void {
        $this->requireAuth();

        $input = $this->getJsonInput();

        $missing = $this->validateRequired($input, ['contact_name', 'contact_type', 'contact_info']);
        if (!empty($missing)) {
            $this->sendError('Missing required contact fields.', $missing, 400);
        }

        $type = trim($input['contact_type']);
        if (!in_array($type, $this->allowedTypes, true)) {
            $this->sendError('Invalid contact_type. Allowed types: ' . implode(', ', $this->allowedTypes), [], 400);
        }

        // Validate type-specific formats
        $this->validateContactValue($type, trim($input['contact_info']));

        $newId = $this->model->create($input);
        if ($newId) {
            $created = $this->model->getById($newId);
            $this->sendResponse($created, 'Contact channel created successfully.', 201);
        } else {
            $this->sendError('Failed to create contact channel.', [], 500);
        }
    }

    /**
     * Update an existing contact channel.
     * Admin-only endpoint: PUT /api/contact-info/{id}
     *
     * @param int $id
     * @return void
     */
    public function update(int $id): void {
        $this->requireAuth();

        $existing = $this->model->getById($id);
        if (!$existing) {
            $this->sendError("Contact channel with ID {$id} not found.", [], 404);
        }

        $input = $this->getJsonInput();
        if (empty($input)) {
            $this->sendError('No update data provided in request body.', [], 400);
        }

        $merged = array_merge($existing, $input);

        $type = trim($merged['contact_type']);
        if (!in_array($type, $this->allowedTypes, true)) {
            $this->sendError('Invalid contact_type. Allowed types: ' . implode(', ', $this->allowedTypes), [], 400);
        }

        $this->validateContactValue($type, trim($merged['contact_info']));

        $success = $this->model->update($merged, $id);
        if ($success) {
            $updated = $this->model->getById($id);
            $this->sendResponse($updated, 'Contact channel updated successfully.');
        } else {
            $this->sendError('Failed to update contact channel.', [], 500);
        }
    }

    /**
     * Delete a contact channel.
     * Admin-only endpoint: DELETE /api/contact-info/{id}
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void {
        $this->requireAuth();

        $existing = $this->model->getById($id);
        if (!$existing) {
            $this->sendError("Contact channel with ID {$id} not found.", [], 404);
        }

        $success = $this->model->delete($id);
        if ($success) {
            $this->sendResponse(['id' => $id], 'Contact channel deleted successfully.');
        } else {
            $this->sendError('Failed to delete contact channel.', [], 500);
        }
    }

    /**
     * Validate contact value according to contact type.
     *
     * @param string $type
     * @param string $value
     * @return void
     */
    private function validateContactValue(string $type, string $value): void {
        if ($type === 'email' && !$this->validateEmail($value)) {
            $this->sendError('Invalid email format for contact_info.', ['contact_info'], 400);
        }

        if ($type === 'url' && !$this->validateUrl($value)) {
            $this->sendError('Invalid URL format for contact_info. Must be a valid web address.', ['contact_info'], 400);
        }
    }
}
