<?php

include_once __DIR__ . '/../core/Database.php';
include_once __DIR__ . '/../models/User.php';

class UserRepository
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function getUserByEmail(string $email)
  {
    $query = "SELECT * FROM USER WHERE email = :email";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $data = $stmt->fetch();

    if ($data) {
      return new User(
        $data['id_user'],
        $data['public_id'],
        $data['last_name'],
        $data['first_name'],
        $data['email'],
        $data['password'],
        $data['temporary_password'],
        $data['birth_date'],
        $data['status'],
        $data['admin'],
        $data['student_class_id']
      );
    }

    return null;
  }

  public function getUserById(int $idUser)
  {
    $query = "SELECT * FROM USER WHERE id_user = :id_user";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':id_user', $idUser);
    $stmt->execute();
    $data = $stmt->fetch();

    if ($data) {
      return new User(
        $data['id_user'],
        $data['public_id'],
        $data['last_name'],
        $data['first_name'],
        $data['email'],
        $data['password'],
        $data['temporary_password'],
        $data['birth_date'],
        $data['status'],
        $data['admin'],
        $data['student_class_id']
      );
    }

    return null;
  }

  public function changeUserPassword(int $idUser, string $newPassword): bool
  {
    $query = "UPDATE USER SET password = :password WHERE id_user = :id_user";

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':password', $newPassword);
      $stmt->bindParam(':id_user', $idUser);

      return $stmt->execute();
    } catch (PDOException) {
      return false;
    }
  }

  public function changeTemporaryPassword(int $idUser): bool
  {
    $temporaryStatus = 0;

    $query = "UPDATE USER SET temporary_password = :temporary_password WHERE id_user = :id_user";

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':temporary_password', $temporaryStatus);
      $stmt->bindParam(':id_user', $idUser);

      return $stmt->execute();
    } catch (PDOException) {
      return false;
    }
  }

  public function resetUserPassword(int $idUser, string $password): bool
  {
    $query = "UPDATE USER
              SET password = :password,
              temporary_password = 1
              WHERE id_user = :id_user";

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':password', $password);
      $stmt->bindParam(':id_user', $idUser);

      return $stmt->execute();
    } catch (PDOException) {
      return false;
    }
  }
}
