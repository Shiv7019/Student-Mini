<?php
// services/StudentService.php

class StudentService
{
    public function __construct(private mysqli $db) {}

    /**
     * Validates input and creates a student.
     * Throws InvalidArgumentException on bad input,
     * mysqli_sql_exception (code 1062) on duplicate email.
     */
    public function create(array $input): int
    {
        $name     = trim($input['full_name'] ?? '');
        $email    = strtolower(trim($input['email'] ?? ''));
        $mobile   = trim($input['mobile'] ?? '');
        $password = $input['password'] ?? '';

        if (
            $name === '' ||
            strlen($name) > 100 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            strlen($email) > 150 ||
            !preg_match('/^[0-9+\-\s()]{7,20}$/', $mobile) ||
            !is_string($password) ||
            strlen($password) < 8
        ) {
            throw new InvalidArgumentException('Invalid student details.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            'INSERT INTO students (full_name, email, mobile, password_hash)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param('ssss', $name, $email, $mobile, $hash);
        $stmt->execute();

        return $this->db->insert_id;
    }
}