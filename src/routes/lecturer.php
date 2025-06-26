<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$resend = Resend::client($_ENV['RESEND_API_KEY']);
// === POST /login ===
$app->post('/login-lecturer', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = json_decode($request->getBody()->getContents(), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'lecturer'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // TODO: Replace plain comparison with password_verify for real security
    if ($user && $password === $user['password_hash']) {
        $response->getBody()->write(json_encode([
            'message' => 'Login successful',
            'user' => $user
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));
    return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
});

// === GET /lecturer/courses ===
// Fetches all courses belonging to a specific lecturer
$app->get('/lecturer/courses', function (Request $request, Response $response) {
    $pdo = getPDO();
    $lecturerId = $request->getQueryParams()['lecturer_id'] ?? null;

    $stmt = $pdo->prepare("SELECT * FROM courses WHERE lecturer_id = ?");
    $stmt->execute([$lecturerId]);
    $courses = $stmt->fetchAll();

    $response->getBody()->write(json_encode($courses));
    return $response->withHeader('Content-Type', 'application/json');
});
// === DELETE /lecturer/course/{id} ===
// Deletes a course and its related data (enrollments, components, marks, final exams)
$app->delete('/lecturer/course/{id}', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $courseId = $args['id'];

    // Delete related data first to prevent foreign key constraint errors
    $pdo->prepare("DELETE FROM marks WHERE component_id IN (SELECT id FROM components WHERE course_id = ?)")->execute([$courseId]);
    $pdo->prepare("DELETE FROM final_exams WHERE course_id = ?")->execute([$courseId]);
    $pdo->prepare("DELETE FROM enrollments WHERE course_id = ?")->execute([$courseId]);
    $pdo->prepare("DELETE FROM components WHERE course_id = ?")->execute([$courseId]);

    // Now delete the course
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$courseId]);

    $response->getBody()->write(json_encode(['message' => 'Course deleted']));
    return $response->withHeader('Content-Type', 'application/json');
});

// === POST /lecturer/course ===
// Creates a new course
$app->post('/lecturer/course', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = json_decode($request->getBody()->getContents(), true);

    $stmt = $pdo->prepare("INSERT INTO courses (code, title, lecturer_id) VALUES (?, ?, ?)");
    $stmt->execute([$data['code'], $data['title'], $data['lecturer_id']]);

    $response->getBody()->write(json_encode(['message' => 'Course created']));
    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
});

// === GET /lecturer/course/{id}/students ===
// Fetches all students enrolled in a specific course
$app->get('/lecturer/course/{id}/students', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $courseId = $args['id'];

    $stmt = $pdo->prepare("
        SELECT u.* FROM enrollments e
        JOIN users u ON e.student_id = u.id
        WHERE e.course_id = ?
    ");
    $stmt->execute([$courseId]);
    $students = $stmt->fetchAll();

    $response->getBody()->write(json_encode($students));
    return $response->withHeader('Content-Type', 'application/json');
});

// === POST /lecturer/course/{id}/enroll ===
// Enrolls a student into a course using their matric number
$app->post('/lecturer/course/{id}/enroll', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $courseId = $args['id'];
    $data = json_decode($request->getBody()->getContents(), true);

    $matricNo = $data['matric_no'] ?? null;

    if (!$matricNo) {
        $response->getBody()->write(json_encode(['error' => 'matric_no is required']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    // Find student by matric number
    $stmt = $pdo->prepare("SELECT id FROM users WHERE matric_no = ? AND role = 'student'");
    $stmt->execute([$matricNo]);
    $student = $stmt->fetch();

    if (!$student) {
        $response->getBody()->write(json_encode(['error' => 'Student not found']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    // Insert enrollment
    $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
    $stmt->execute([$student['id'], $courseId]);

    $response->getBody()->write(json_encode(['message' => 'Student enrolled']));
    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
});


// === PUT /lecturer/student/{id} ===
// Updates a student's name, email, or matric number
$app->put('/lecturer/student/{id}', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $studentId = $args['id'];
    $data = json_decode($request->getBody()->getContents(), true);

    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, matric_no = ? WHERE id = ? AND role = 'student'");
    $stmt->execute([$data['name'], $data['email'], $data['matric_no'], $studentId]);

    $response->getBody()->write(json_encode(['message' => 'Student updated']));
    return $response->withHeader('Content-Type', 'application/json');
});

// === DELETE /lecturer/course/{course_id}/student/{student_id} ===
// Unenrolls (removes) a student from a course
$app->delete('/lecturer/course/{course_id}/student/{student_id}', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE course_id = ? AND student_id = ?");
    $stmt->execute([$args['course_id'], $args['student_id']]);

    $response->getBody()->write(json_encode(['message' => 'Student unenrolled']));
    return $response->withHeader('Content-Type', 'application/json');
});

// === POST /lecturer/course/{id}/component ===
// Adds a new continuous assessment component to a course
$app->post('/lecturer/course/{id}/component', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $courseId = $args['id'];
    $data = json_decode($request->getBody()->getContents(), true);

    $stmt = $pdo->prepare("INSERT INTO components (course_id, name, weight, max_mark) VALUES (?, ?, ?, ?)");
    $stmt->execute([$courseId, $data['name'], $data['weight'], $data['max_mark']]);

    $response->getBody()->write(json_encode(['message' => 'Component added']));
    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
});

// === PUT /lecturer/component/{id} ===
// Updates a component's name, weight, or max mark
$app->put('/lecturer/component/{id}', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $id = $args['id'];
    $data = json_decode($request->getBody()->getContents(), true);

    $stmt = $pdo->prepare("UPDATE components SET name = ?, weight = ?, max_mark = ? WHERE id = ?");
    $stmt->execute([$data['name'], $data['weight'], $data['max_mark'], $id]);

    $response->getBody()->write(json_encode(['message' => 'Component updated']));
    return $response->withHeader('Content-Type', 'application/json');
});

// === POST /lecturer/component/{id}/mark ===
// Records a student's mark for a specific component
$app->post('/lecturer/component/{id}/mark', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $componentId = $args['id'];
    $data = json_decode($request->getBody()->getContents(), true);

    $stmt = $pdo->prepare("INSERT INTO marks (component_id, student_id, mark_obtained) VALUES (?, ?, ?)");
    $stmt->execute([$componentId, $data['student_id'], $data['mark_obtained']]);

    $response->getBody()->write(json_encode(['message' => 'Mark recorded']));
    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
});

// === POST /lecturer/course/{id}/final-exam ===
// Records a student's final exam mark for the course
$app->post('/lecturer/course/{id}/final-exam', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $courseId = $args['id'];
    $data = json_decode($request->getBody()->getContents(), true);

    $stmt = $pdo->prepare("INSERT INTO final_exams (student_id, course_id, mark) VALUES (?, ?, ?)");
    $stmt->execute([$data['student_id'], $courseId, $data['mark']]);

    $response->getBody()->write(json_encode(['message' => 'Final exam mark added']));
    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
});

// === GET /lecturer/course/{id}/analytics ===
// Returns CA score, final exam score, and total score for each student in a course
$app->get('/lecturer/course/{id}/analytics', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $courseId = $args['id'];

    $stmt = $pdo->prepare("
        SELECT u.name, u.matric_no, 
               SUM(m.mark_obtained * c.weight / c.max_mark) AS ca_score,
               fe.mark AS final_exam,
               (SUM(m.mark_obtained * c.weight / c.max_mark) + fe.mark * 0.3) AS total_score
        FROM users u
        JOIN enrollments e ON u.id = e.student_id
        LEFT JOIN marks m ON m.student_id = u.id
        LEFT JOIN components c ON c.id = m.component_id AND c.course_id = ?
        LEFT JOIN final_exams fe ON fe.student_id = u.id AND fe.course_id = ?
        WHERE e.course_id = ?
        GROUP BY u.id
    ");
    $stmt->execute([$courseId, $courseId, $courseId]);
    $analytics = $stmt->fetchAll();

    $response->getBody()->write(json_encode($analytics));
    return $response->withHeader('Content-Type', 'application/json');
});

// === GET /lecturer/course/{id}/export ===
// Downloads the analytics data as a CSV file
$app->get('/lecturer/course/{id}/export', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $courseId = $args['id'];

    $stmt = $pdo->prepare("
        SELECT u.name, u.matric_no, 
               SUM(m.mark_obtained * c.weight / c.max_mark) AS ca_score,
               fe.mark AS final_exam,
               (SUM(m.mark_obtained * c.weight / c.max_mark) + fe.mark * 0.3) AS total_score
        FROM users u
        JOIN enrollments e ON u.id = e.student_id
        LEFT JOIN marks m ON m.student_id = u.id
        LEFT JOIN components c ON c.id = m.component_id AND c.course_id = ?
        LEFT JOIN final_exams fe ON fe.student_id = u.id AND fe.course_id = ?
        WHERE e.course_id = ?
        GROUP BY u.id
    ");
    $stmt->execute([$courseId, $courseId, $courseId]);
    $results = $stmt->fetchAll();

    $filename = "course_export_" . $courseId . ".csv";
    $csv = fopen("php://temp", "r+");
    fputcsv($csv, ['Name', 'Matric No', 'CA Score', 'Final Exam', 'Total']);

    foreach ($results as $row) {
        fputcsv($csv, [$row['name'], $row['matric_no'], $row['ca_score'], $row['final_exam'], $row['total_score']]);
    }

    rewind($csv);
    $csvOutput = stream_get_contents($csv);
    fclose($csv);

    $response->getBody()->write($csvOutput);
    return $response
        ->withHeader('Content-Type', 'text/csv')
        ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
});

// === GET /lecturer/course/{id}/components ===
// Fetches all CA components of a course
$app->get('/lecturer/course/{id}/components', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $courseId = $args['id'];

    $stmt = $pdo->prepare("SELECT * FROM components WHERE course_id = ?");
    $stmt->execute([$courseId]);
    $components = $stmt->fetchAll();

    $response->getBody()->write(json_encode($components));
    return $response->withHeader('Content-Type', 'application/json');
});

// === GET /lecturer/component/{id}/marks ===
// Retrieves all marks recorded for a specific component
$app->get('/lecturer/component/{id}/marks', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $componentId = $args['id'];

    $stmt = $pdo->prepare("
        SELECT student_id, mark_obtained 
        FROM marks 
        WHERE component_id = ?
    ");
    $stmt->execute([$componentId]);
    $marks = $stmt->fetchAll();

    $response->getBody()->write(json_encode($marks));
    return $response->withHeader('Content-Type', 'application/json');
});

// === GET /lecturer/course/{id}/final-exam ===
// Retrieves all final exam marks for students in a course
$app->get('/lecturer/course/{id}/final-exam', function (Request $request, Response $response, $args) {
    $pdo = getPDO();
    $courseId = $args['id'];

    $stmt = $pdo->prepare("
        SELECT student_id, mark 
        FROM final_exams 
        WHERE course_id = ?
    ");
    $stmt->execute([$courseId]);
    $finals = $stmt->fetchAll();

    $response->getBody()->write(json_encode($finals));
    return $response->withHeader('Content-Type', 'application/json');
});


// Record or update component mark and notify student
$app->post('/lecturer/component/{id}/mark-and-notify', function (Request $request, Response $response, $args) use ($resend) {
    $pdo = getPDO();
    $componentId = $args['id'];
    $data = json_decode($request->getBody()->getContents(), true);

    $studentId = $data['student_id'] ?? null;
    $mark = $data['mark_obtained'] ?? null;

    if (!$studentId || $mark === null) {
        $response->getBody()->write(json_encode(['error' => 'student_id and mark_obtained are required']));
        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }

    // Insert or update mark
    $stmt = $pdo->prepare("
        INSERT INTO marks (component_id, student_id, mark_obtained)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE mark_obtained = VALUES(mark_obtained)
    ");
    $stmt->execute([$componentId, $studentId, $mark]);

    // Get student info
    $stmt = $pdo->prepare("SELECT email, name FROM users WHERE id = ?");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();

    // Get component info
    $stmt = $pdo->prepare("SELECT name FROM components WHERE id = ?");
    $stmt->execute([$componentId]);
    $component = $stmt->fetch();

    // Send email
    try {
        $resend->emails->send([
            'from' => 'onboarding@resend.dev',
            'to' => $student['email'],
            'subject' => 'Notification: Assessment Mark Recorded',
            'html' => "
                <div style='font-family: Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #333; padding: 20px;'>
                    <p>Dear {$student['name']},</p>
                    <p>We hope this message finds you well.</p>
                    <p>This is to inform you that your mark for the assessment component <strong>\"{$component['name']}\"</strong> 
                    has been successfully recorded or updated in the academic system.</p>
                    <p><strong>Recorded Mark:</strong> {$mark}</p>
                    <p>You may log in to your student dashboard to view the full details and track your progress.</p>
                    <p>Should you have any questions, feel free to contact your lecturer.</p>
                    <p>Best regards,<br>
                    Academic Affairs Unit<br>
                    TrendyNest Academic System</p>
                </div>
            "
        ]);
    } catch (\Exception $e) {
        error_log("Resend error: " . $e->getMessage());
    }

    $response->getBody()->write(json_encode(['message' => 'Mark recorded or updated and notification sent']));
    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
});


// Record or update final exam mark and notify student
$app->post('/lecturer/course/{id}/final-exam-and-notify', function (Request $request, Response $response, $args) use ($resend) {
    $pdo = getPDO();
    $courseId = $args['id'];
    $data = json_decode($request->getBody()->getContents(), true);

    $studentId = $data['student_id'] ?? null;
    $mark = $data['mark'] ?? null;

    if (!$studentId || $mark === null) {
        $response->getBody()->write(json_encode(['error' => 'student_id and mark are required']));
        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }

    // Insert or update final exam mark
    $stmt = $pdo->prepare("
        INSERT INTO final_exams (student_id, course_id, mark)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE mark = VALUES(mark)
    ");
    $stmt->execute([$studentId, $courseId, $mark]);

    // Get student info
    $stmt = $pdo->prepare("SELECT email, name FROM users WHERE id = ?");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();

    // Get course info
    $stmt = $pdo->prepare("SELECT title FROM courses WHERE id = ?");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();

    // Send email
    try {
        $resend->emails->send([
            'from' => 'onboarding@resend.dev',
            'to' => $student['email'],
            'subject' => 'Notification: Final Exam Mark Recorded',
            'html' => "
                <div style='font-family: Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #333; padding: 20px;'>
                    <p>Dear {$student['name']},</p>
                    <p>We hope this message finds you well.</p>
                    <p>This is to inform you that your <strong>final exam mark</strong> for the course <strong>\"{$course['title']}\"</strong>
                    has been successfully recorded or updated in the academic system.</p>
                    <p><strong>Recorded Mark:</strong> {$mark}</p>
                    <p>You may log in to your student dashboard to view the complete results and breakdown.</p>
                    <p>Should you have any questions, feel free to contact your lecturer.</p>
                    <p>Best regards,<br>
                    Academic Affairs Unit<br>
                    TrendyNest Academic System</p>
                </div>
            "
        ]);
    } catch (\Exception $e) {
        error_log("Resend error: " . $e->getMessage());
    }

    $response->getBody()->write(json_encode(['message' => 'Final exam mark recorded or updated and email sent']));
    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
});



// === CORS Middleware ===
// Allows cross-origin requests (frontend to backend communication)
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
});
