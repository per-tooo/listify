<?php
  require "secret.php";

  header("Content-Type: application/json");

  $dbHost = "toooserv";
  $dbUser = "listify";
  $dbName = "listify";

  $dsn = "mysql:host=$host;dbname=$dbName";
  $options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ];
  
  try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
  } catch (PDOException $exception) {
    http_response_code(500);
    echo json_encode([ "error" => "Database connection failed." ]);
    exit;
  }

  $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
  $method = $_SERVER["REQUEST_METHOD"];

  switch (true) {
    // create list
    case $path === '/list/create' && $method === 'POST':
      $data = json_decode(file_get_contents("php://input"), true);

      if (!isset($data["title"], $data["content"])) {
        http_response_code(404);
        echo json_encode([ "error" => "Missing title or content section." ]);
        exit;
      }

      $stmt = "INSERT INTO lists (title, content) VALUES (:title, :content)";
      $stmt->execute([
        ":title" => $data["title"],
        ":content" => json_encode($data["content"])
      ]);

      echo json_encode([ "success" => true, "id" => $pdo->lastInsertId() ]);
    break;
    
    // delete list
    case preg_match("#^/list/delete/(\d+)$#", $path, $matches):
      $id = (int)$matches[1];

      $stmt = "DELETE FROM lists WHERE id=?";
      $stmt->execute([$id]);

      echo json_encode([ "success" => $stmt->rowCount() > 0 ]);
    break;

    // view list
    case preg_match("#^/list/view/(\d+)$#", $path, $matches):
      $id = (int)$matches[1];

      $stmt = $pdo->prepare("SELECT * FROM lists WHERE id=?");
      $stmt->execute([$id]);
      $item = $stmt->fetch();

      if ($item)
        echo json_encode($item);
      else {
        http_response_code(404);
        echo json_encode([ "error" => "List not found." ]);
      }
    break;

    // listAll
    case $path === '/list/listAll':
      $stmt = $pdo->query("SELECT id, title from lists");
      $lists = $stmt->fetchAll();
      echo json_encode($lists);
    break;

    default:
      http_response_code(404);
      echo json_encode([ "error" => "Endpoint not found." ]);
    break;
  }

  $pdo = null;
?>