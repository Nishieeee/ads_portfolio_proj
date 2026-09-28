<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/ContactInquiry.php';

class ContactInquiryController extends BaseController {
    private ContactInquiry $model;

    public function __construct(?PDO $db) {
        parent::__construct($db);
        $this->model = new ContactInquiry($this->conn);
    }

    /**
     * Submit an inquiry from the public portfolio landing page.
     * Public endpoint: POST /api/inquiries
     *
     * @return void
     */
    public function submit(): void {
        $input = $this->getJsonInput();

        $required = ['sender_name', 'sender_email', 'subject', 'message'];
        $missing = $this->validateRequired($input, $required);
        if (!empty($missing)) {
            $this->sendError('All contact form fields are required.', $missing, 400);
        }

        if (!$this->validateEmail($input['sender_email'])) {
            $this->sendError('Invalid email address format.', ['sender_email'], 400);
        }

        if (!$this->validateLength($input['message'], 10, 5000)) {
            $this->sendError('Message must be between 10 and 5,000 characters.', ['message'], 400);
        }

        $newId = $this->model->create($input);
        if ($newId) {
            $this->sendResponse(['id' => $newId], 'Thank you for reaching out! Your message has been sent successfully.', 201);
        } else {
            $this->sendError('Failed to submit inquiry. Please try again later.', [], 500);
        }
    }

    /**
     * Retrieve list of inquiries with optional unread filter.
     * Admin-only endpoint: GET /api/inquiries
     *
     * @return void
     */
    public function getAll(): void {
        $this->requireAuth();

        $unreadOnly = isset($_GET['unread_only']) && in_array($_GET['unread_only'], ['1', 'true'], true);
        $inquiries = $this->model->getAll($unreadOnly);
        $unreadCount = $this->model->getUnreadCount();

        $this->sendResponse([
            'unread_count' => $unreadCount,
            'items'        => $inquiries,
        ], 'Inquiries retrieved successfully.');
    }

    /**
     * Retrieve a single inquiry by ID.
     * Admin-only endpoint: GET /api/inquiries/{id}
     *
     * @param int $id
     * @return void
     */
    public function getById(int $id): void {
        $this->requireAuth();

        $inquiry = $this->model->getById($id);
        if (!$inquiry) {
            $this->sendError("Inquiry with ID {$id} not found.", [], 404);
        }

        // Automatically mark message as read upon inspection
        if (empty($inquiry['is_read'])) {
            $this->model->setReadStatus($id, true);
            $inquiry['is_read'] = true;
        }

        $this->sendResponse($inquiry, 'Inquiry retrieved successfully.');
    }

    /**
     * Toggle or explicitly set read/unread status.
     * Admin-only endpoint: PATCH /api/inquiries/{id}/read
     *
     * @param int $id
     * @return void
     */
    public function toggleRead(int $id): void {
        $this->requireAuth();

        $existing = $this->model->getById($id);
        if (!$existing) {
            $this->sendError("Inquiry with ID {$id} not found.", [], 404);
        }

        $input = $this->getJsonInput();
        $isRead = isset($input['is_read']) ? (bool) $input['is_read'] : !((bool) $existing['is_read']);

        $success = $this->model->setReadStatus($id, $isRead);
        if ($success) {
            $updated = $this->model->getById($id);
            $statusText = $updated['is_read'] ? 'read' : 'unread';
            $this->sendResponse($updated, "Inquiry marked as {$statusText}.");
        } else {
            $this->sendError('Failed to update inquiry read status.', [], 500);
        }
    }

    /**
     * Delete an inquiry by ID.
     * Admin-only endpoint: DELETE /api/inquiries/{id}
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void {
        $this->requireAuth();

        $existing = $this->model->getById($id);
        if (!$existing) {
            $this->sendError("Inquiry with ID {$id} not found.", [], 404);
        }

        $success = $this->model->delete($id);
        if ($success) {
            $this->sendResponse(['id' => $id], 'Inquiry deleted successfully.');
        } else {
            $this->sendError('Failed to delete inquiry.', [], 500);
        }
    }
}
