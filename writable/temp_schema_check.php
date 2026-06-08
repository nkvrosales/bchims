<?php
require_once __DIR__ . '/../app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';
$db = \Config\Database::connect();
echo "=== request table ===\n";
$query = $db->query('DESCRIBE request');
foreach ($query->getResultArray() as $row) echo implode(' | ', $row) . "\n";
echo "\n=== supply table ===\n";
$query = $db->query('DESCRIBE supply');
foreach ($query->getResultArray() as $row) echo implode(' | ', $row) . "\n";
echo "\n=== department_supply table ===\n";
$query = $db->query('DESCRIBE department_supply');
foreach ($query->getResultArray() as $row) echo implode(' | ', $row) . "\n";
