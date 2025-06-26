<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// === POST /login-advisor ===
$app->post('/login-advisor', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = json_decode($request->getBody()->getContents(), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'advisor'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if($user && $password === $user['password_hash']) {
        $response->getBody()->write(json_encode([
            'message' => 'Advisor login successful',
            'user' => $user
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    $response->getBody()->write(json_encode(['error' => 'Invalid advisor credentials']));
    return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
});


// === GET /advisor/advisees ===
$app->get('/advisor/advisees', function (Request $request, Response $response) {
    $pdo = getPDO();
    $advisorId = $request->getQueryParams()['advisor_id'] ?? null;

    if (!$advisorId) {
        $response->getBody()->write(json_encode(['error' => 'advisor_id is required']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    // Get advisees
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.matric_no
        FROM advisees a
        JOIN users u ON a.student_id = u.id
        WHERE a.advisor_id = ?
    ");
    $stmt->execute([$advisorId]);
    $advisees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // For each advisee, get courses, marks, GPA
    foreach ($advisees as &$student) {
        // Courses
        $coursesStmt = $pdo->prepare("
            SELECT c.id as course_id, c.title, c.code
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            WHERE e.student_id = ?
        ");
        $coursesStmt->execute([$student['id']]);
        $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

        // For each course, get marks
        foreach ($courses as &$course) {
            // Components
            $compStmt = $pdo->prepare("
                SELECT comp.name, comp.weight, m.mark_obtained, comp.max_mark
                FROM components comp
                LEFT JOIN marks m ON m.component_id = comp.id AND m.student_id = ?
                WHERE comp.course_id = ?
            ");
            $compStmt->execute([$student['id'], $course['course_id']]);
            $course['components'] = $compStmt->fetchAll(PDO::FETCH_ASSOC);

            // Final exam
            $finalStmt = $pdo->prepare("
                SELECT mark FROM final_exams WHERE student_id = ? AND course_id = ?
            ");
            $finalStmt->execute([$student['id'], $course['course_id']]);
            $course['final_exam'] = $finalStmt->fetchColumn();

            // Calculate total mark (70% CA + 30% Final)
            $ca = 0;
            $caWeight = 0;
            foreach ($course['components'] as $comp) {
                if ($comp['max_mark'] > 0) {
                    $ca += ($comp['mark_obtained'] / $comp['max_mark']) * $comp['weight'];
                    $caWeight += $comp['weight'];
                }
            }
            $caScore = $caWeight > 0 ? ($ca / $caWeight) * 70 : 0;
            $finalScore = $course['final_exam'] !== false ? ($course['final_exam'] / 100) * 30 : 0;
            $course['total_mark'] = round($caScore + $finalScore, 2);
        }

        // GPA: average of total_mark/25 (simple 4.0 scale)
        $gpa = 0;
        if (count($courses) > 0) {
            $gpa = array_sum(array_column($courses, 'total_mark')) / (count($courses) * 25);
        }
        $student['courses'] = $courses;
        $student['gpa'] = round($gpa, 2);
    }

    // Optionally: calculate bottom 20% threshold
    $gpas = array_column($advisees, 'gpa');
    sort($gpas);
    $threshold = $gpas ? $gpas[(int)floor(count($gpas) * 0.2)] : 0;
    foreach ($advisees as &$student) {
        $student['at_risk'] = $student['gpa'] < 2.0 || $student['gpa'] <= $threshold;
    }

    $response->getBody()->write(json_encode($advisees));
    return $response->withHeader('Content-Type', 'application/json');
});

// === POST /advisor/note ===
$app->post('/advisor/note', function (Request $request, Response $response) {
    $pdo = getPDO();
    $data = json_decode($request->getBody()->getContents(), true);
    $advisorId = $data['advisor_id'] ?? null;
    $studentId = $data['student_id'] ?? null;
    $note = $data['note'] ?? '';

    if (!$advisorId || !$studentId) {
        $response->getBody()->write(json_encode(['error' => 'advisor_id and student_id required']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    // Upsert note
    $stmt = $pdo->prepare("
        INSERT INTO advisor_notes (advisor_id, student_id, note)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE note = VALUES(note), updated_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$advisorId, $studentId, $note]);

    $response->getBody()->write(json_encode(['message' => 'Note saved']));
    return $response->withHeader('Content-Type', 'application/json');
});

// === GET /advisor/note ===
$app->get('/advisor/note', function (Request $request, Response $response) {
    $pdo = getPDO();
    $advisorId = $request->getQueryParams()['advisor_id'] ?? null;
    $studentId = $request->getQueryParams()['student_id'] ?? null;

    if (!$advisorId || !$studentId) {
        $response->getBody()->write(json_encode(['error' => 'advisor_id and student_id required']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $stmt = $pdo->prepare("SELECT note FROM advisor_notes WHERE advisor_id = ? AND student_id = ?");
    $stmt->execute([$advisorId, $studentId]);
    $note = $stmt->fetchColumn();

    $response->getBody()->write(json_encode(['note' => $note ?: '']));
    return $response->withHeader('Content-Type', 'application/json');
});