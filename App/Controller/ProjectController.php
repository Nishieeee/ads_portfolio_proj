<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Project.php';

class ProjectController extends BaseController {
    private Project $model;

    public function __construct(?PDO $db) {
        parent::__construct($db);
        $this->model = new Project($this->conn);
    }

    /**
     * Retrieve all projects, optionally filtered by featured status.
     * Public endpoint: GET /api/projects?featured=1
     *
     * @return void
     */
    public function getAll(): void {
        $featuredOnly = isset($_GET['featured']) && in_array($_GET['featured'], ['1', 'true'], true);
        $projects = $this->model->getAll($featuredOnly);

        // Attach parsed technology tags array to each project
        foreach ($projects as &$project) {
            $project['technologies_array'] = $this->model->getTechnologiesArray($project['technologies'] ?? '');
        }

        $this->sendResponse($projects, 'Projects retrieved successfully.');
    }

    /**
     * Retrieve a single project by ID.
     * Public endpoint: GET /api/projects/{id}
     *
     * @param int $id
     * @return void
     */
    public function getById(int $id): void {
        $project = $this->model->getById($id);
        if (!$project) {
            $this->sendError("Project with ID {$id} not found.", [], 404);
        }

        $project['technologies_array'] = $this->model->getTechnologiesArray($project['technologies'] ?? '');
        $this->sendResponse($project, 'Project retrieved successfully.');
    }

    /**
     * Create a new project.
     * Admin-only endpoint: POST /api/projects
     *
     * @return void
     */
    public function create(): void {
        $this->requireAuth();

        $input = $this->getJsonInput();

        $required = ['project_name', 'description', 'technologies'];
        $missing = $this->validateRequired($input, $required);
        if (!empty($missing)) {
            $this->sendError('Missing required project fields.', $missing, 400);
        }

        if (empty($input['date_start'])) {
            $input['date_start'] = date('Y-m-d');
        }

        $this->validateDates($input);

        // Validate URLs if provided
        if (!empty($input['url']) && !$this->validateUrl($input['url'])) {
            $this->sendError('Invalid live demo URL format.', ['url'], 400);
        }
        if (!empty($input['github_repo']) && !$this->validateUrl($input['github_repo'])) {
            $this->sendError('Invalid GitHub repository URL format.', ['github_repo'], 400);
        }

        $newId = $this->model->create($input);
        if ($newId) {
            $created = $this->model->getById($newId);
            $created['technologies_array'] = $this->model->getTechnologiesArray($created['technologies'] ?? '');
            $this->sendResponse($created, 'Project created successfully.', 201);
        } else {
            $this->sendError('Failed to create project.', [], 500);
        }
    }

    /**
     * Update an existing project.
     * Admin-only endpoint: PUT /api/projects/{id}
     *
     * @param int $id
     * @return void
     */
    public function update(int $id): void {
        $this->requireAuth();

        $existing = $this->model->getById($id);
        if (!$existing) {
            $this->sendError("Project with ID {$id} not found.", [], 404);
        }

        $input = $this->getJsonInput();
        if (empty($input)) {
            $this->sendError('No update data provided in request body.', [], 400);
        }

        $merged = array_merge($existing, $input);
        $this->validateDates($merged);

        if (!empty($merged['url']) && !$this->validateUrl($merged['url'])) {
            $this->sendError('Invalid live demo URL format.', ['url'], 400);
        }
        if (!empty($merged['github_repo']) && !$this->validateUrl($merged['github_repo'])) {
            $this->sendError('Invalid GitHub repository URL format.', ['github_repo'], 400);
        }

        $success = $this->model->update($merged, $id);
        if ($success) {
            $updated = $this->model->getById($id);
            $updated['technologies_array'] = $this->model->getTechnologiesArray($updated['technologies'] ?? '');
            $this->sendResponse($updated, 'Project updated successfully.');
        } else {
            $this->sendError('Failed to update project.', [], 500);
        }
    }

    /**
     * Delete a project.
     * Admin-only endpoint: DELETE /api/projects/{id}
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void {
        $this->requireAuth();

        $existing = $this->model->getById($id);
        if (!$existing) {
            $this->sendError("Project with ID {$id} not found.", [], 404);
        }

        $success = $this->model->delete($id);
        if ($success) {
            $this->sendResponse(['id' => $id], 'Project deleted successfully.');
        } else {
            $this->sendError('Failed to delete project.', [], 500);
        }
    }

    /**
     * Validate date formats.
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
