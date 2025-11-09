<?php

include_once __DIR__ . '/../core/Database.php';
include_once __DIR__ . '/../models/Class.php';

class ClassRepository
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function getAllClasses(): array
  {
    $query = 'SELECT
              CLASS.id_class AS class_id,
              CLASS.public_id AS class_public_id,
              CLASS.name AS class_name,
              CLASS.year_number AS class_year_number,
              SCHOOL.id_school AS school_id,
              SCHOOL.public_id AS school_public_id,
              SCHOOL.name AS school_name,
              SCHOOL.city AS school_city,
              SCHOOL.department_number AS school_department_number
              FROM CLASS, SCHOOL
              WHERE CLASS.school_id = SCHOOL.id_school';

    try {
      $stmt = $this->db->prepare($query);

      $stmt->execute();
      $rows = $stmt->fetchAll();

      usort($rows, function ($a, $b) {
        $schoolCompare = strcmp($a['school_name'], $b['school_name']);

        if ($schoolCompare !== 0) {
          return $schoolCompare;
        }

        return strcmp($a['class_name'], $b['class_name']);
      });

      return $rows;
    } catch (PDOException) {
      return [];
    }
  }

  public function getClassByStudentId(int $studentId): array
  {
    $query = 'SELECT
              CLASS.id_class AS class_id,
              CLASS.public_id AS class_public_id,
              CLASS.name AS class_name,
              CLASS.year_number AS class_year_number,
              SCHOOL.id_school AS school_id,
              SCHOOL.public_id AS school_public_id,
              SCHOOL.name AS school_name,
              SCHOOL.city AS school_city,
              SCHOOL.department_number AS school_department_number
              FROM USER, CLASS, SCHOOL
              WHERE USER.student_class_id = CLASS.id_class
              AND CLASS.school_id = SCHOOL.id_school
              AND USER.id_user = :studentId';

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':studentId', $studentId);

      $stmt->execute();
      $rows = $stmt->fetchAll();

      $classArray = [];

      foreach ($rows as $row) {
        $school = new SchoolModel(
          $row['school_id'],
          $row['school_public_id'],
          $row['school_name'],
          $row['school_city'],
          $row['school_department_number']
        );

        $class = new ClassModel(
          $row['class_id'],
          $row['class_public_id'],
          $row['class_name'],
          $row['class_year_number'],
          0,
          $school
        );

        $classArray[] = $class;
      }

      usort($classArray, fn($a, $b) => strcmp($a->classFullName, $b->classFullName));

      return $classArray;
    } catch (PDOException) {
      return [];
    }
  }

  public function getClassByTeacherId(int $teacherId, ?bool $groupedBySchool = false): array
  {
    $query = 'SELECT
              CLASS.id_class AS class_id,
              CLASS.public_id AS class_public_id,
              CLASS.name AS class_name,
              CLASS.year_number AS class_year_number,
              SCHOOL.id_school AS school_id,
              SCHOOL.public_id AS school_public_id,
              SCHOOL.name AS school_name,
              SCHOOL.city AS school_city,
              SCHOOL.department_number AS school_department_number,
              (
                SELECT COUNT(*) 
                FROM USER 
                WHERE USER.student_class_id = CLASS.id_class
              ) AS student_count
              FROM TEACH, CLASS, SCHOOL
              WHERE TEACH.class_id = CLASS.id_class
              AND CLASS.school_id = SCHOOL.id_school
              AND TEACH.teacher_id = :teacherId';

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':teacherId', $teacherId);

      $stmt->execute();
      $rows = $stmt->fetchAll();

      $classesArray = [];

      foreach ($rows as $row) {
        $school = new SchoolModel(
          $row['school_id'],
          $row['school_public_id'],
          $row['school_name'],
          $row['school_city'],
          $row['school_department_number']
        );

        $class = new ClassModel(
          $row['class_id'],
          $row['class_public_id'],
          $row['class_name'],
          $row['class_year_number'],
          $row['student_count'],
          $school
        );

        $classesArray[] = $class;
      }

      if ($groupedBySchool) {
        $groupedArray = [];

        foreach ($classesArray as $classArray) {
          $schoolId = $classArray->school->schoolId;

          if (!isset($groupedArray[$schoolId])) {
            $groupedArray[$schoolId] = [
              'school' => $classArray->school,
              'classes' => []
            ];
          }

          $groupedArray[$schoolId]['classes'][] = $classArray;
        }

        foreach ($groupedArray as &$groupArray) {
          usort($groupArray['classes'], fn($a, $b) => strcmp($a->classFullName, $b->classFullName));
        }
        unset($group);

        return array_values($groupedArray);
      } else {
        usort($classesArray, function ($a, $b) {
          $schoolCmp = strcmp($a->school->schoolName, $b->school->schoolName);
          if ($schoolCmp !== 0) {
            return $schoolCmp;
          }
          return strcmp($a->classFullName, $b->classFullName);
        });

        return $classesArray;
      }
    } catch (PDOException) {
      return [];
    }
  }

  public function getClassByPublicId(string $publicId): ?ClassModel
  {
    $query = 'SELECT
              CLASS.id_class AS class_id,
              CLASS.public_id AS class_public_id,
              CLASS.name AS class_name,
              CLASS.year_number AS class_year_number,
              SCHOOL.id_school AS school_id,
              SCHOOL.public_id AS school_public_id,
              SCHOOL.name AS school_name,
              SCHOOL.city AS school_city,
              SCHOOL.department_number AS school_department_number,
              (
                SELECT COUNT(*) 
                FROM USER 
                WHERE USER.student_class_id = CLASS.id_class
              ) AS student_count
              FROM CLASS, SCHOOL
              WHERE CLASS.school_id = SCHOOL.id_school
              AND CLASS.public_id = :publicId';

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':publicId', $publicId);

      $stmt->execute();
      $row = $stmt->fetch();

      if ($row) {
        return new ClassModel(
          $row['class_id'],
          $row['class_public_id'],
          $row['class_name'],
          $row['class_year_number'],
          $row['student_count'],
          new SchoolModel(
            $row['school_id'],
            $row['school_public_id'],
            $row['school_name'],
            $row['school_city'],
            $row['school_department_number']
          )
        );
      } else {
        return null;
      }
    } catch (PDOException) {
      return null;
    }
  }

  public function verifyTeacherInClass(int $teacherId, int $classId): bool
  {
    $query = 'SELECT *
              FROM TEACH
              WHERE teacher_id = :teacherId
              AND class_id = :classId';

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':teacherId', $teacherId);
      $stmt->bindParam(':classId', $classId);

      $stmt->execute();
      $row = $stmt->fetchAll();

      if (count($row) > 0) {
        return true;
      } else {
        return false;
      }
    } catch (PDOException) {
      return false;
    }
  }

  public function verifyTeacherTeachingStudent(int $teacherId, int $studentId): bool
  {
    $query = 'SELECT id_user
              FROM TEACH, USER
              WHERE TEACH.class_id = USER.student_class_id
              AND TEACH.teacher_id = :teacherId
              AND USER.id_user = :studentId';
    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':teacherId', $teacherId);
      $stmt->bindParam(':studentId', $studentId);

      $stmt->execute();
      $row = $stmt->fetchAll();

      if (count($row) > 0) {
        return true;
      } else {
        return false;
      }
    } catch (PDOException) {
      return false;
    }
  }

  public function getAllStudentsByClassId(int $classId): array
  {
    $query = 'SELECT
              USER.id_user AS student_id,
              USER.public_id AS student_public_id,
              USER.last_name AS student_last_name,
              USER.first_name AS student_first_name,
              USER.birth_date AS student_birth_date,
              (
                SELECT COUNT(*) 
                FROM REPORT 
                WHERE REPORT.student_id = USER.id_user
              ) AS report_count,
              USER.student_class_id AS student_class_id
              FROM USER
              WHERE USER.student_class_id = :classId';

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':classId', $classId);

      $stmt->execute();
      $rows = $stmt->fetchAll();

      return $rows;
    } catch (PDOException) {
      return [];
    }
  }

  public function getStudentByPublicId(string $publicId): ?array
  {
    $query = 'SELECT
              USER.id_user AS student_id,
              USER.public_id AS student_public_id,
              USER.last_name AS student_last_name,
              USER.first_name AS student_first_name,
              USER.birth_date AS student_birth_date,
              (
                SELECT COUNT(*) 
                FROM REPORT 
                WHERE REPORT.student_id = USER.id_user
              ) AS report_count,
              USER.student_class_id AS student_class_id
              FROM USER
              WHERE USER.public_id = :publicId';

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':publicId', $publicId);

      $stmt->execute();
      $row = $stmt->fetch();

      if ($row) {
        return $row;
      } else {
        return null;
      }
    } catch (PDOException) {
      return null;
    }
  }

  public function removeClassToStudent(int $studentId): bool
  {
    $query = 'UPDATE USER SET student_class_id = NULL WHERE id_user = :studentId';

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':studentId', $studentId);

      return $stmt->execute();
    } catch (PDOException) {
      return false;
    }
  }

  public function removeClassesToTeacher(int $teacherId): bool
  {
    $query = 'DELETE FROM TEACH WHERE teacher_id = :teacherId';

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':teacherId', $teacherId);

      return $stmt->execute();
    } catch (PDOException) {
      return false;
    }
  }

  public function addClassToStudent(int $studentId, int $classId): bool
  {
    $query = 'UPDATE USER SET student_class_id = :classId WHERE id_user = :studentId';

    try {
      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':studentId', $studentId);
      $stmt->bindParam(':classId', $classId);

      return $stmt->execute();
    } catch (PDOException) {
      return false;
    }
  }

  public function addClassesToTeacher(int $teacherId, array $classIds): bool
  {
    $query = 'INSERT INTO TEACH (teacher_id, class_id) VALUES (:teacherId, :classId)';

    try {
      $stmt = $this->db->prepare($query);

      foreach ($classIds as $classId) {
        $stmt->bindParam(':teacherId', $teacherId);
        $stmt->bindParam(':classId', $classId);

        if (!$stmt->execute()) {
          return false;
        }
      }

      return true;
    } catch (PDOException) {
      return false;
    }
  }
}
