<?php
require_once __DIR__ . '/../Config/database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("Could not connect to database.");
    }

    // Check existing columns
    $stmt = $db->query("DESCRIBE my_basic_info");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('bio_greeting', $columns)) {
        $db->exec("ALTER TABLE my_basic_info ADD COLUMN bio_greeting VARCHAR(255) DEFAULT \"Hi, I'm Clein!\"");
        echo "Added column: bio_greeting\n";
    } else {
        echo "Column bio_greeting already exists.\n";
    }

    if (!in_array('bio_paragraphs', $columns)) {
        $db->exec("ALTER TABLE my_basic_info ADD COLUMN bio_paragraphs TEXT NULL");
        echo "Added column: bio_paragraphs\n";
    } else {
        echo "Column bio_paragraphs already exists.\n";
    }

    // Populate initial biography from portfolio_data.json if current row has empty bio_paragraphs
    $row = $db->query("SELECT * FROM my_basic_info WHERE id = 1 LIMIT 1")->fetch();
    if ($row && empty($row['bio_paragraphs'])) {
        $jsonFile = __DIR__ . '/../portfolio_data.json';
        if (file_exists($jsonFile)) {
            $data = json_decode(file_get_contents($jsonFile), true);
            $bioGreeting = $data['my_basic_info']['bio_greeting'] ?? "Hi, I'm Clein!";
            $bioParas = $data['my_basic_info']['bio_paragraphs'] ?? [];
            $bioText = is_array($bioParas) ? implode("\n\n", $bioParas) : (string)$bioParas;

            $updateStmt = $db->prepare("UPDATE my_basic_info SET bio_greeting = :greeting, bio_paragraphs = :paras WHERE id = 1");
            $updateStmt->execute([
                ':greeting' => $bioGreeting,
                ':paras' => $bioText
            ]);
            echo "Populated default bio narrative into my_basic_info.\n";
        }
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
