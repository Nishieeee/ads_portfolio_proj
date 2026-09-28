<?php
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../App/Models/BasicInfo.php';
require_once __DIR__ . '/../App/Models/ContactInfo.php';
require_once __DIR__ . '/../App/Models/Experience.php';
require_once __DIR__ . '/../App/Models/Skill.php';
require_once __DIR__ . '/../App/Models/Education.php';
require_once __DIR__ . '/../App/Models/Project.php';
require_once __DIR__ . '/../App/Models/Certificate.php';
require_once __DIR__ . '/../App/Models/ContactInquiry.php';

$db = (new Database())->getConnection();
$jsonFile = __DIR__ . '/../portfolio_data.json';

if (!file_exists($jsonFile)) {
    die("portfolio_data.json not found.\n");
}

$data = json_decode(file_get_contents($jsonFile), true);

// 1. Basic Info
$basicModel = new BasicInfo($db);
if (!$basicModel->get() && isset($data['my_basic_info'])) {
    $basicModel->create($data['my_basic_info']);
    echo "Seeded: my_basic_info\n";
}

// 2. Contact Info
$contactModel = new ContactInfo($db);
if (empty($contactModel->getAll()) && isset($data['my_contact_info'])) {
    foreach ($data['my_contact_info'] as $c) {
        $contactModel->create($c);
    }
    echo "Seeded: my_contact_info (" . count($data['my_contact_info']) . " items)\n";
}

// 3. Experience
$expModel = new Experience($db);
if (empty($expModel->getAll()) && isset($data['my_experience'])) {
    foreach ($data['my_experience'] as $e) {
        $expModel->create($e);
    }
    echo "Seeded: my_experience (" . count($data['my_experience']) . " items)\n";
}

// 4. Skills
$skillModel = new Skill($db);
if (empty($skillModel->getAll()) && isset($data['my_skills'])) {
    foreach ($data['my_skills'] as $s) {
        $skillModel->create($s);
    }
    echo "Seeded: my_skills (" . count($data['my_skills']) . " items)\n";
}

// 5. Education
$eduModel = new Education($db);
if (empty($eduModel->getAll()) && isset($data['my_education'])) {
    foreach ($data['my_education'] as $ed) {
        $eduModel->create($ed);
    }
    echo "Seeded: my_education (" . count($data['my_education']) . " items)\n";
}

// 6. Projects
$projModel = new Project($db);
if (empty($projModel->getAll()) && isset($data['my_projects'])) {
    foreach ($data['my_projects'] as $p) {
        $projModel->create($p);
    }
    echo "Seeded: my_projects (" . count($data['my_projects']) . " items)\n";
}

// 7. Certificates
$certModel = new Certificate($db);
if (empty($certModel->getAll()) && isset($data['my_certificates'])) {
    foreach ($data['my_certificates'] as $crt) {
        $certModel->create($crt);
    }
    echo "Seeded: my_certificates (" . count($data['my_certificates']) . " items)\n";
}

echo "Database seeding finished successfully!\n";
