<?php

include_once __DIR__ . '/../core/Database.php';
include_once __DIR__ . '/../models/Report.php';

class ReportRepository
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function createReport(string $title, string $date, string $content, int $studentId): bool
  {
    $publicId = $this->generateUniquePublicId();
    $reportDate = date('Y-m-d H:i:s', strtotime($date));

    $query = "INSERT INTO REPORT 
    (
      public_id,
      title,
      date,
      content,
      student_id
    ) VALUES (
      :public_id,
      :title,
      :date,
      :content,
      :student_id
    )";

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':public_id', $publicId);
      $stmt->bindParam(':title', $title);
      $stmt->bindParam(':date', $reportDate);
      $stmt->bindParam(':content', $content);
      $stmt->bindParam(':student_id', $studentId);

      $stmt->execute();
    } catch (PDOException) {
      return false;
    }
    return true;
  }

  public function getAllReportsByStudentId(int $studentId): array
  {
    $query = "SELECT * FROM REPORT WHERE student_id = :student_id ORDER BY date DESC";

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':student_id', $studentId);

      $stmt->execute();
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $reports = [];

      foreach ($rows as $row) {
        $report = new Report(
          $row['id_report'],
          $row['public_id'],
          $row['title'],
          $row['content'],
          $row['date'],
          $row['student_id']
        );
        $reports[] = $report;
      }
      return $reports;
    } catch (PDOException) {
      return [];
    }
  }

  public function getReportByPublicId(string $publicId): ?Report
  {
    $query = "SELECT * FROM REPORT WHERE public_id = :public_id";

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':public_id', $publicId);

      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($row) {
        return new Report(
          $row['id_report'],
          $row['public_id'],
          $row['title'],
          $row['content'],
          $row['date'],
          $row['student_id']
        );
      }
    } catch (PDOException) {
      return null;
    }
    return null;
  }

  public function updateReport(int $reportId, string $title, string $date, string $content): bool
  {
    $reportDate = date('Y-m-d H:i:s', strtotime($date));

    $query = "UPDATE REPORT SET title = :title, date = :date, content = :content WHERE id_report = :id_report";

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':id_report', $reportId);
      $stmt->bindParam(':title', $title);
      $stmt->bindParam(':date', $reportDate);
      $stmt->bindParam(':content', $content);

      $stmt->execute();
    } catch (PDOException) {
      return false;
    }
    return true;
  }

  public function deleteReport(int $reportId): bool
  {
    $query = "DELETE FROM REPORT WHERE id_report = :id_report";

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':id_report', $reportId);

      $stmt->execute();
    } catch (PDOException) {
      return false;
    }
    return true;
  }

  private function generateUniquePublicId(): string
  {
    do {
      $publicId = bin2hex(random_bytes(4));

      $query = "SELECT COUNT(*) FROM REPORT WHERE public_id = :public_id";
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':public_id', $publicId);

      $stmt->execute();
      $count = $stmt->fetchColumn();
    } while ($count > 0);

    return $publicId;
  }
}
