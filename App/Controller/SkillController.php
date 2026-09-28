<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Skill.php';

class SkillController extends BaseController {
    private Skill $model;
    private array $allowedCategories = ['technical', 'soft'];

    public function __construct(?PDO $db) {
        parent::__construct($db);
        $this->model = new Skill($this->conn);
    }

    /**
     * Retrieve all skill groups.
     * Optionally filter by category: GET /api/skills?category=technical
     * Public endpoint
     *
     * @return void
     */
    public function getAll(): void {
        $category = null;
        if (isset($_GET['category']) && in_array(strtolower(trim($_GET['category'])), $this->allowedCategories, true)) {
            $category = strtolower(trim($_GET['category']));
        }

        $skills = $this->model->getAll($category);

        // Attach parsed skills array to each group for convenience
        foreach ($skills as &$group) {
            $group['skills_array'] = $this->model->getSkillsArray($group['skills_list'] ?? '');
        }

        $this->sendResponse($skills, 'Skills retrieved successfully.');
    }

    /**
     * Retrieve a single skill group by ID.
     * Public endpoint: GET /api/skills/{id}
     *
     * @param int $id
     * @return void
     */
    public function getById(int $id): void {
        $skill = $this->model->getById($id);
        if (!$skill) {
            $this->sendError("Skill group with ID {$id} not found.", [], 404);
        }

        $skill['skills_array'] = $this->model->getSkillsArray($skill['skills_list'] ?? '');
        $this->sendResponse($skill, 'Skill group retrieved successfully.');
    }

    /**
     * Create a new skill category group.
     * Admin-only endpoint: POST /api/skills
     *
     * @return void
     */
    public function create(): void {
        $this->requireAuth();

        $input = $this->getJsonInput();

        $missing = $this->validateRequired($input, ['skill_category', 'category_label']);
        if (!empty($missing)) {
            $this->sendError('Missing required skill fields.', $missing, 400);
        }

        $category = strtolower(trim($input['skill_category']));
        if (!in_array($category, $this->allowedCategories, true)) {
            $this->sendError('Invalid skill_category. Allowed: technical, soft', [], 400);
        }

        $newId = $this->model->create($input);
        if ($newId) {
            $created = $this->model->getById($newId);
            $created['skills_array'] = $this->model->getSkillsArray($created['skills_list'] ?? '');
            $this->sendResponse($created, 'Skill group created successfully.', 201);
        } else {
            $this->sendError('Failed to create skill group.', [], 500);
        }
    }

    /**
     * Update an existing skill group.
     * Admin-only endpoint: PUT /api/skills/{id}
     *
     * @param int $id
     * @return void
     */
    public function update(int $id): void {
        $this->requireAuth();

        $existing = $this->model->getById($id);
        if (!$existing) {
            $this->sendError("Skill group with ID {$id} not found.", [], 404);
        }

        $input = $this->getJsonInput();
        if (empty($input)) {
            $this->sendError('No update data provided in request body.', [], 400);
        }

        $merged = array_merge($existing, $input);

        $category = strtolower(trim($merged['skill_category']));
        if (!in_array($category, $this->allowedCategories, true)) {
            $this->sendError('Invalid skill_category. Allowed: technical, soft', [], 400);
        }

        $success = $this->model->update($merged, $id);
        if ($success) {
            $updated = $this->model->getById($id);
            $updated['skills_array'] = $this->model->getSkillsArray($updated['skills_list'] ?? '');
            $this->sendResponse($updated, 'Skill group updated successfully.');
        } else {
            $this->sendError('Failed to update skill group.', [], 500);
        }
    }

    /**
     * Delete a skill group.
     * Admin-only endpoint: DELETE /api/skills/{id}
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void {
        $this->requireAuth();

        $existing = $this->model->getById($id);
        if (!$existing) {
            $this->sendError("Skill group with ID {$id} not found.", [], 404);
        }

        $success = $this->model->delete($id);
        if ($success) {
            $this->sendResponse(['id' => $id], 'Skill group deleted successfully.');
        } else {
            $this->sendError('Failed to delete skill group.', [], 500);
        }
    }
}
