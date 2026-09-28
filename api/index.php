<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../Config/database.php';

// Autoload or require models & controllers
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
require_once __DIR__ . '/../App/Models/AdminUser.php';

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

// Parse URI endpoint
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$endpointPath = trim(str_replace($scriptName, '', $requestUri), '/');

// Support fallback ?endpoint= query parameter if mod_rewrite is not active
if (empty($endpointPath) && isset($_GET['endpoint'])) {
    $endpointPath = trim($_GET['endpoint'], '/');
}

$segments = !empty($endpointPath) ? explode('/', $endpointPath) : [];
$resource = $segments[0] ?? '';
$id = $segments[1] ?? null;

// Router skeleton: to be implemented with core business logic
switch ($resource) {
    case 'auth':
        $controller = new AuthController($db);
        $action = $segments[1] ?? '';
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
        break;

    case 'sections':
        $controller = new PageSectionController($db);
        break;

    case 'basic-info':
        $controller = new BasicInfoController($db);
        break;

    case 'contact-info':
        $controller = new ContactInfoController($db);
        break;

    case 'experience':
        $controller = new ExperienceController($db);
        break;

    case 'skills':
        $controller = new SkillController($db);
        break;

    case 'education':
        $controller = new EducationController($db);
        break;

    case 'projects':
        $controller = new ProjectController($db);
        break;

    case 'certificates':
        $controller = new CertificateController($db);
        break;

    case 'inquiries':
        $controller = new ContactInquiryController($db);
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
