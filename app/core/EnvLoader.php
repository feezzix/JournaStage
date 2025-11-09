<?php

/**
 * Charge les variables d'environnement depuis le fichier .env
 */
function loadEnv(string $path): void
{
  if (!file_exists($path)) {
    throw new Exception(".env file not found at: $path");
  }

  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

  foreach ($lines as $line) {
    // Ignorer les commentaires
    if (str_starts_with(trim($line), '#')) {
      continue;
    }

    [$name, $value] = explode('=', $line, 2);
    $name = trim($name);
    $value = trim($value);

    // Enlever les guillemets éventuels
    $value = trim($value, "\"'");

    // Définir dans $_ENV et comme variable d'environnement
    $_ENV[$name] = $value;
    putenv("$name=$value");
  }
}

// Charger les variables depuis la racine du projet
loadEnv(__DIR__ . '/../../.env');

$config = [
  'DB_DSN' => sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $_ENV['DB_HOST'],
    $_ENV['DB_NAME'],
    $_ENV['DB_CHARSET']
  ),
  'DB_USER' => $_ENV['DB_USER'],
  'DB_PASS' => $_ENV['DB_PASS'],
];

try {
  $pdo = new PDO(
    $config['DB_DSN'],
    $config['DB_USER'],
    $config['DB_PASS'],
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );
} catch (PDOException $e) {
  die('❌ Erreur de connexion à la base de données : ' . $e->getMessage());
}

return $pdo;
