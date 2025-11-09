<?php

include_once __DIR__ . '/../core/Database.php';
include_once __DIR__ . '/../models/Session.php';

class SessionRepository
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function createSession(int $userId, string $sessionExpiresAt): string
  {
    $sessionToken = bin2hex(random_bytes(32));
    $createdAt = date('Y-m-d H:i:s');
    $expiresAt = date('Y-m-d H:i:s', $sessionExpiresAt);

    $query = "INSERT INTO SESSION 
    (
      user_id,
      session_token,
      created_at,
      expires_at
    ) VALUES (
      :user_id,
      :session_token,
      :created_at,
      :expires_at
    )";

    $stmt = $this->db->prepare($query);

    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':session_token', $sessionToken);
    $stmt->bindParam(':created_at', $createdAt);
    $stmt->bindParam(':expires_at', $expiresAt);

    $stmt->execute();

    return $sessionToken;
  }

  public function getSessionByToken(string $sessionToken): ?Session
  {
    $query = "SELECT * FROM SESSION WHERE session_token = :session_token";

    $stmt = $this->db->prepare($query);

    $stmt->bindParam(':session_token', $sessionToken);

    $stmt->execute();
    $data = $stmt->fetch();

    if ($data) {
      return new Session(
        (int) $data['id_session'],
        (int) $data['user_id'],
        $data['session_token'],
        $data['created_at'],
        $data['expires_at']
      );
    }

    return null;
  }

  public function deleteSession(string $sessionToken): void
  {
    $query = "DELETE FROM SESSION WHERE session_token = :session_token";

    $stmt = $this->db->prepare($query);

    $stmt->bindParam(':session_token', $sessionToken);

    $stmt->execute();
  }
}
