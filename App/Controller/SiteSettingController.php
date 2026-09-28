<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/SiteSetting.php';

class SiteSettingController extends BaseController {
    private SiteSetting $model;

    public function __construct(?PDO $db) {
        parent::__construct($db);
        $this->model = new SiteSetting($this->conn);
    }

    /**
     * Retrieve all site settings key-value pairs.
     * GET /api/settings
     *
     * @return void
     */
    public function getAll(): void {
        $settings = $this->model->getAll();
        $this->sendResponse($settings, 'Settings retrieved successfully.');
    }

    /**
     * Update one or more global site settings.
     * PUT /api/settings
     *
     * @return void
     */
    public function update(): void {
        // Enforce admin authentication
        $this->requireAuth();

        $input = $this->getJsonInput();

        if (empty($input)) {
            $this->sendError('No settings data provided in request body.', [], 400);
        }

        // Validate theme value if present
        if (isset($input['theme'])) {
            $allowedThemes = ['monochrome', 'midnight', 'emerald', 'amber', 'light'];
            if (!in_array(trim($input['theme']), $allowedThemes, true)) {
                $this->sendError('Invalid theme. Allowed themes: ' . implode(', ', $allowedThemes), [], 400);
            }
        }

        $success = $this->model->updateMultiple($input);

        if ($success) {
            $updated = $this->model->getAll();
            $this->sendResponse($updated, 'Settings updated successfully.');
        } else {
            $this->sendError('Failed to update site settings.', [], 500);
        }
    }
}
