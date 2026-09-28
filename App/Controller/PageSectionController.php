<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/PageSection.php';

class PageSectionController extends BaseController {
    private PageSection $model;

    public function __construct(?PDO $db) {
        parent::__construct($db);
        $this->model = new PageSection($this->conn);
    }

    /**
     * Retrieve all page sections ordered by order_index.
     * GET /api/sections
     *
     * @return void
     */
    public function getAll(): void {
        $visibleOnly = isset($_GET['visible']) && in_array($_GET['visible'], ['1', 'true'], true);
        $sections = $this->model->getAll($visibleOnly);
        $this->sendResponse($sections, 'Sections retrieved successfully.');
    }

    /**
     * Retrieve a single page section by ID.
     * GET /api/sections/{id}
     *
     * @param int $id
     * @return void
     */
    public function getById(int $id): void {
        $section = $this->model->getById($id);
        if (!$section) {
            $this->sendError("Page section with ID {$id} not found.", [], 404);
        }
        $this->sendResponse($section, 'Section retrieved successfully.');
    }

    /**
     * Update a page section's labels, titles, and visibility.
     * PUT /api/sections/{id}
     *
     * @param int $id
     * @return void
     */
    public function update(int $id): void {
        // Enforce admin authentication
        $this->requireAuth();

        $section = $this->model->getById($id);
        if (!$section) {
            $this->sendError("Page section with ID {$id} not found.", [], 404);
        }

        $input = $this->getJsonInput();
        if (empty($input)) {
            $this->sendError('No update data provided in request body.', [], 400);
        }

        // Merge existing attributes with input to ensure partial updates work cleanly
        $mergedData = array_merge($section, $input);

        $success = $this->model->update($mergedData, $id);
        if ($success) {
            $updated = $this->model->getById($id);
            $this->sendResponse($updated, 'Page section updated successfully.');
        } else {
            $this->sendError('Failed to update page section.', [], 500);
        }
    }

    /**
     * Batch update section display order indices (drag-and-drop support).
     * POST /api/sections/reorder
     *
     * @return void
     */
    public function reorder(): void {
        // Enforce admin authentication
        $this->requireAuth();

        $input = $this->getJsonInput();

        // Support payload as either direct array or { "sections": [...] }
        $orderList = isset($input['sections']) && is_array($input['sections']) ? $input['sections'] : $input;

        if (empty($orderList) || !is_array($orderList)) {
            $this->sendError('Invalid or empty reorder list provided.', [], 400);
        }

        $success = $this->model->reorder($orderList);
        if ($success) {
            $freshList = $this->model->getAll();
            $this->sendResponse($freshList, 'Sections reordered successfully.');
        } else {
            $this->sendError('Failed to reorder page sections.', [], 500);
        }
    }

    /**
     * Toggle the visibility of a page section.
     * PATCH /api/sections/{id}/toggle-visibility
     *
     * @param int $id
     * @return void
     */
    public function toggleVisibility(int $id): void {
        // Enforce admin authentication
        $this->requireAuth();

        $section = $this->model->getById($id);
        if (!$section) {
            $this->sendError("Page section with ID {$id} not found.", [], 404);
        }

        $input = $this->getJsonInput();
        $targetVisibility = isset($input['is_visible']) ? (bool) $input['is_visible'] : null;

        $success = $this->model->toggleVisibility($id, $targetVisibility);
        if ($success) {
            $updated = $this->model->getById($id);
            $statusText = $updated['is_visible'] ? 'visible' : 'hidden';
            $this->sendResponse($updated, "Page section is now {$statusText}.");
        } else {
            $this->sendError('Failed to toggle page section visibility.', [], 500);
        }
    }
}
