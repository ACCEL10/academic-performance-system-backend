<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/db.php';

$pdo = getPDO();

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

    $pdo->exec("TRUNCATE TABLE advisees");
    $pdo->exec("TRUNCATE TABLE enrollments");
    $pdo->exec("TRUNCATE TABLE marks");
    $pdo->exec("TRUNCATE TABLE components");
    $pdo->exec("TRUNCATE TABLE final_exams");
    $pdo->exec("TRUNCATE TABLE remark_requests");
    $pdo->exec("TRUNCATE TABLE courses");
    $pdo->exec("TRUNCATE TABLE users");

    $pdo->exec("ALTER TABLE users AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE courses AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE components AUTO_INCREMENT = 1");

    // Insert admin
    $pdo->exec("
        INSERT INTO users (role, name, email, password_hash, pin) VALUES
        ('admin', 'Admin User', 'admin@example.com', 'admin123', '0')
    ");

    // Insert lecturers
    $pdo->exec("
        INSERT INTO users (role, name, email, password_hash, pin) VALUES
        ('lecturer', 'Dr. Alice Tan', 'alice.tan@example.com', 'alice123', '0'),
        ('lecturer', 'Dr. Bob Lee', 'bob.lee@example.com', 'bob123', '0')
    ");

    // Insert advisors
    $pdo->exec("
        INSERT INTO users (role, name, email, password_hash, pin) VALUES
        ('advisor', 'Dr. Nora Yunus', 'nora.yunus@example.com', 'nora123', '0'),
        ('advisor', 'Dr. Hafiz Rahman', 'hafiz.rahman@example.com', 'hafiz123', '0')
    ");

    // Insert students
    $pdo->exec("
        INSERT INTO users (role, name, email, matric_no, pin, password_hash) VALUES
        ('student', 'John Doe', 'john@example.com', 'A21CS001', '123456', 'studentpass'),
        ('student', 'Jane Smith', 'jane@example.com', 'A21CS002', '234567', 'studentpass'),
        ('student', 'Ali Ahmad', 'ali@example.com', 'A21CS003', '345678', 'studentpass'),
        ('student', 'Eizam Rosli', 'eizam@example.com', 'B23CS054', '456789', 'studentpass'),
        ('student', 'Nur Amirah', 'amirah@example.com', 'B23CS090', '987654', 'studentpass'),
        ('student', 'Nur Aisyah', 'aisyah@example.com', 'A21CS004', '567890', 'studentpass'),
        ('student', 'Daniel Lim', 'daniel.lim@example.com', 'B23CS101', '112233', 'studentpass'),
        ('student', 'Siti Hajar', 'siti.hajar@example.com', 'B23CS102', '221144', 'studentpass'),
        ('student', 'Kumar Raj', 'kumar.raj@example.com', 'B23CS103', '332255', 'studentpass'),
        ('student', 'Wong Mei Yee', 'mei.yee@example.com', 'B23CS104', '443366', 'studentpass')
    ");


    // Insert courses (lecturer_id = 1 or 2)
    $pdo->exec("
        INSERT INTO courses (code, title, lecturer_id) VALUES
        ('CSCI101', 'Intro to Computer Science', 1),
        ('CSCI102', 'Data Structures', 1),
        ('CSCI201', 'Database Systems', 2),
        ('CSCI202', 'Operating Systems', 2),
        ('CSCI203', 'Networks', 1),
        ('CSCI204', 'Web Programming', 2)
    ");

    // Insert enrollments – 3 courses per student (student_id = 3–6, course_id = 1–6)
    $pdo->exec("
        INSERT INTO enrollments (student_id, course_id) VALUES
        (5, 1), (5, 2), (5, 3),     -- John Doe
        (6, 1), (6, 4), (6, 5),     -- Jane Smith
        (7, 2), (7, 3), (7, 6),     -- Ali Ahmad
        (8, 1), (8, 5), (8, 6),     -- Eizam Rosli
        (9, 1), (9, 2), (9, 4),     -- Nur Amirah
        (10, 3), (10, 4), (10, 6),  -- Nur Aisyah
        (11, 2), (11, 3), (11, 5),  -- Daniel Lim
        (12, 1), (12, 5), (12, 6),  -- Siti Hajar
        (13, 2), (13, 4), (13, 6),  -- Kumar Raj
        (14, 3), (14, 4), (14, 5)   -- Wong Mei Yee
    ");


    // Insert components for each course based on 70% structure
    $pdo->exec("
        INSERT INTO components (course_id, name, weight, max_mark) VALUES
        -- Course 1 (CSCI101)
        (1, 'Quiz 1', 5.00, 5.00),
        (1, 'Quiz 2', 5.00, 5.00),
        (1, 'Quiz 3', 5.00, 5.00),
        (1, 'Test', 15.00, 15.00),
        (1, 'Assignment 1', 10.00, 10.00),
        (1, 'Assignment 2', 10.00, 10.00),
        (1, 'Project', 20.00, 20.00),

        -- Course 2 (CSCI102)
        (2, 'Quiz 1', 10.00, 10.00),
        (2, 'Quiz 2', 10.00, 10.00),
        (2, 'Assignment', 20.00, 20.00),
        (2, 'Midterm Test', 10.00, 10.00),
        (2, 'Project', 20.00, 20.00),

        -- Course 3 (CSCI201)
        (3, 'Quiz', 10.00, 10.00),
        (3, 'Lab Report', 20.00, 20.00),
        (3, 'Assignment', 10.00, 10.00),
        (3, 'Project', 30.00, 30.00),

        -- Course 4 (CSCI202)
        (4, 'Quiz', 10.00, 10.00),
        (4, 'Assignment 1', 15.00, 15.00),
        (4, 'Assignment 2', 15.00, 15.00),
        (4, 'Group Project', 30.00, 30.00),

        -- Course 5 (CSCI203)
        (5, 'Mini Quiz', 10.00, 10.00),
        (5, 'Case Study', 20.00, 20.00),
        (5, 'Assignment', 20.00, 20.00),
        (5, 'Presentation', 20.00, 20.00),

        -- Course 6 (CSCI204)
        (6, 'HTML Assignment', 10.00, 10.00),
        (6, 'CSS Quiz', 10.00, 10.00),
        (6, 'JS Test', 20.00, 20.00),
        (6, 'Web App Project', 30.00, 30.00)
    ");

    // Seed marks table with dummy marks for each student and each component
    $studentEnrollments = $pdo->query("
        SELECT e.student_id, e.course_id
        FROM enrollments e
        JOIN users u ON e.student_id = u.id
        WHERE u.role = 'student'
    ")->fetchAll(PDO::FETCH_ASSOC);

    $components = $pdo->query("SELECT id, course_id, max_mark FROM components")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($studentEnrollments as $enrollment) {
        $studentId = $enrollment['student_id'];
        $courseId = $enrollment['course_id'];

        foreach ($components as $component) {
            if ($component['course_id'] == $courseId) {
                $componentId = $component['id'];
                $maxMark = (float)$component['max_mark'];
                $mark = rand(0, $maxMark); // random dummy mark
                $stmt = $pdo->prepare("INSERT INTO marks (component_id, student_id, mark_obtained) VALUES (?, ?, ?)");
                $stmt->execute([$componentId, $studentId, $mark]);
            }
        }
    }

    // Seed final_exams table with dummy final marks
    foreach ($studentEnrollments as $enrollment) {
        $studentId = $enrollment['student_id'];
        $courseId = $enrollment['course_id'];
        $mark = rand(40, 100); // simulate pass/fail range
        $stmt = $pdo->prepare("INSERT INTO final_exams (student_id, course_id, mark) VALUES (?, ?, ?)");
        $stmt->execute([$studentId, $courseId, $mark]);
    }

    // Insert advisor-advisee relationships (assign all students to advisors for demo)
    $pdo->exec("
        INSERT INTO advisees (advisor_id, student_id) VALUES
        (3, 5), (3, 6), (3, 7), (3, 8), (3, 9),
        (4, 10), (4, 11), (4, 12), (4, 13), (4, 14)
    ");

    // Insert advisor notes for some students
    $pdo->exec("
        INSERT INTO advisor_notes (advisor_id, student_id, note) VALUES
        (3, 5, 'Met John Doe on 2025-06-01. Discussed progress.'),
        (3, 6, 'Jane Smith needs to improve attendance.'),
        (4, 10, 'Nur Aisyah is at risk, scheduled follow-up.'),
        (4, 14, 'Wong Mei Yee is performing well.')
    ");

    // Insert remark requests (simplified to match your table structure)
    $pdo->exec("
        INSERT INTO remark_requests (student_id, course_id, component_id, justification, status) VALUES
        (5, 1, 1, 'I believe my answer is correct for Q2.', 'pending'),
        (6, 2, 9, 'The assignment was submitted on time but marked late.', 'pending'),
        (7, 3, 13, 'I followed all the rubric requirements.', 'pending')
    ");


    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

    echo "✅ Database seeded successfully.\n";
} catch (PDOException $e) {
    echo "❌ Error seeding data: " . $e->getMessage() . "\n";
}
