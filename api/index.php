<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-Auth-Token");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../Config/database.php';

// Domain Models
require_once __DIR__ . '/../App/Models/AdminUser.php';
require_once __DIR__ . '/../App/Models/SiteSetting.php';
require_once __DIR__ . '/../App/Models/PageSection.php';
require_once __DIR__ . '/../App/Models/BasicInfo.php';
require_once __DIR__ . '/../App/Models/ContactInfo.php';
require_once __DIR__ . '/../App/Models/Experience.php';
require_once __DIR__ . '/../App/Models/Skill.php';
require_once __DIR__ . '/../App/Models/Education.php';
require_once __DIR__ . '/../App/Models/Project.php';
require_once __DIR__ . '/../App/Models/Certificate.php';
require_once __DIR__ . '/../App/Models/ContactInquiry.php';

// Controllers
require_once __DIR__ . '/../App/Controller/BaseController.php';
require_once __DIR__ . '/../App/Controller/AuthController.php';
require_once __DIR__ . '/../App/Controller/SiteSettingController.php';
require_once __DIR__ . '/../App/Controller/PageSectionController.php';
require_once __DIR__ . '/../App/Controller/BasicInfoController.php';
require_once __DIR__ . '/../App/Controller/ContactInfoController.php';
require_once __DIR__ . '/../App/Controller/ExperienceController.php';
require_once __DIR__ . '/../App/Controller/SkillController.php';
require_once __DIR__ . '/../App/Controller/EducationController.php';
require_once __DIR__ . '/../App/Controller/ProjectController.php';
require_once __DIR__ . '/../App/Controller/CertificateController.php';
require_once __DIR__ . '/../App/Controller/ContactInquiryController.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

// Parse URI endpoint path
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$endpointPath = trim(str_replace($scriptName, '', $requestUri), '/');

// Support fallback ?endpoint= query parameter if mod_rewrite is not active
if (empty($endpointPath) && isset($_GET['endpoint'])) {
    $endpointPath = trim($_GET['endpoint'], '/');
}

$segments = !empty($endpointPath) ? explode('/', $endpointPath) : [];
$resource = $segments[0] ?? '';
$id = isset($segments[1]) && is_numeric($segments[1]) ? (int) $segments[1] : null;
$action = $segments[1] ?? '';
$subAction = $segments[2] ?? '';

/**
 * Standard REST resource dispatcher for CRUD controllers.
 */
function dispatchCrud($controller, string $method, ?int $id): void {
    if ($method === 'GET') {
        if ($id !== null) {
            $controller->getById($id);
        } else {
            $controller->getAll();
        }
    } elseif ($method === 'POST') {
        $controller->create();
    } elseif ($method === 'PUT') {
        if ($id !== null) {
            $controller->update($id);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Resource ID required for update.']);
            exit();
        }
    } elseif ($method === 'DELETE') {
        if ($id !== null) {
            $controller->delete($id);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Resource ID required for delete.']);
            exit();
        }
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
        exit();
    }
}

// REST Dispatcher
switch ($resource) {
    case 'auth':
        $controller = new AuthController($db);
        if ($action === 'login' && $method === 'POST') {
            $controller->login();
        } elseif ($action === 'logout' && $method === 'POST') {
            $controller->logout();
        } elseif ($action === 'me' && $method === 'GET') {
            $controller->me();
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Auth endpoint not found.']);
            exit();
        }
        break;

    case 'settings':
        $controller = new SiteSettingController($db);
        if ($method === 'GET') {
            $controller->getAll();
        } elseif ($method === 'PUT') {
            $controller->update();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit();
        }
        break;

    case 'sections':
        $controller = new PageSectionController($db);
        if ($action === 'reorder' && $method === 'POST') {
            $controller->reorder();
        } elseif ($id !== null && $subAction === 'toggle-visibility' && $method === 'PATCH') {
            $controller->toggleVisibility($id);
        } else {
            dispatchCrud($controller, $method, $id);
        }
        break;

    case 'basic-info':
        $controller = new BasicInfoController($db);
        if ($method === 'GET') {
            $controller->get();
        } elseif ($method === 'PUT') {
            $controller->update();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit();
        }
        break;

    case 'contact-info':
        dispatchCrud(new ContactInfoController($db), $method, $id);
        break;

    case 'experience':
        dispatchCrud(new ExperienceController($db), $method, $id);
        break;

    case 'skills':
        dispatchCrud(new SkillController($db), $method, $id);
        break;

    case 'education':
        dispatchCrud(new EducationController($db), $method, $id);
        break;

    case 'projects':
        dispatchCrud(new ProjectController($db), $method, $id);
        break;

    case 'certificates':
        dispatchCrud(new CertificateController($db), $method, $id);
        break;

    case 'inquiries':
        $controller = new ContactInquiryController($db);
        if ($method === 'POST') {
            $controller->submit();
        } elseif ($id !== null && $subAction === 'read' && $method === 'PATCH') {
            $controller->toggleRead($id);
        } elseif ($method === 'GET') {
            if ($id !== null) {
                $controller->getById($id);
            } else {
                $controller->getAll();
            }
        } elseif ($method === 'DELETE' && $id !== null) {
            $controller->delete($id);
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            exit();
        }
        break;

    default:
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Portfolio REST API Router ready.",
            "version" => "1.0.0"
        ]);
        exit();
}
