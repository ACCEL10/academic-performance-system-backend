<?php

use Psr\Http\Message\ResponseInterface as Response;;
use Psr\Http\Message\ServerRequestInterface as Request;

        $app->post('/login-student', function (Request $request, Response $response) {
            $pdo = getPDO();
            $data = json_decode($request->getBody()->getContents(), true);
            $matric = $data['matric'] ?? '';
            $pin = $data['pin'] ?? '';

            $stmt = $pdo->prepare("SELECT * FROM users WHERE matric_no = ? AND role = 'student'");
            $stmt->execute([$matric]);
            $user = $stmt->fetch();

            if ($user && $pin === $user['pin']) {
                $response->getBody()->write(json_encode([
                    'message' => 'Student login successful',
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'matric_no' => $user['matric_no']
                    ]
                ]));
                return $response->withHeader('Content-Type', 'application/json');
            }

        $response->getBody()->write(json_encode(['error' => 'Invalid matric number or PIN']));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    });

    $app->get('/student/{id}/courses-marks', function (Request $request, Response $response, $args) {
        $pdo = getPDO();
        $studentId = $args['id'];

        // Fetch student name
        $stmtName = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $stmtName->execute([$studentId]);
        $student = $stmtName->fetch();

        $stmt = $pdo->prepare("
            SELECT 
                c.id as course_id,
                c.code,
                c.title,
                COALESCE(SUM(m.mark_obtained * cmp.weight / cmp.max_mark), 0) AS ca_score,
                fe.mark AS final_exam,
                (COALESCE(SUM(m.mark_obtained * cmp.weight / cmp.max_mark), 0) + COALESCE(fe.mark * 0.3, 0)) AS total_score
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            LEFT JOIN marks m ON m.student_id = e.student_id
            LEFT JOIN components cmp ON cmp.id = m.component_id AND cmp.course_id = c.id
            LEFT JOIN final_exams fe ON fe.course_id = c.id AND fe.student_id = e.student_id
            WHERE e.student_id = ?
            GROUP BY c.id
        ");
        $stmt->execute([$studentId]);
        $courses = $stmt->fetchAll();

        $result = [
        'name' => $student ? $student['name'] : '',
        'courses' => $courses
    ];

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/student/{student_id}/course/{course_id}/marks', function (Request $request, Response $response, array $args) {
        $pdo = getPDO();
        $student_id = $args['student_id'];
        $course_id = $args['course_id'];

        // Get all components and marks
        $stmt = $pdo->prepare("
            SELECT c.name AS component_name, c.weight, c.max_mark, m.mark_obtained
            FROM components c
            LEFT JOIN marks m ON c.id = m.component_id AND m.student_id = ?
            WHERE c.course_id = ?
        ");
        $stmt->execute([$student_id, $course_id]);
        $rows = $stmt->fetchAll();

        $components = [];
        $total = 0;

        foreach ($rows as $row) {
            $obtained = $row['mark_obtained'] ?? 0;
            $max = $row['max_mark'] ?: 1;
            $weight = $row['weight'] ?: 0;

            $weighted = ($obtained / $max) * $weight;

            $components[] = [
                'name' => $row['component_name'],
                'mark_obtained' => $obtained,
                'max_mark' => $max,
                'weight' => $weight,
                'weighted_score' => round($weighted, 2)
            ];

            $total += $weighted;
        }

        // Fetch final exam mark from final_exams or final_marks table
        $stmtFinal = $pdo->prepare("SELECT mark FROM final_exams WHERE student_id = ? AND course_id = ?");
        $stmtFinal->execute([$student_id, $course_id]);
        $finalExam = $stmtFinal->fetchColumn();

        $finalExamWeighted = null;
        if ($finalExam !== false && $finalExam !== null) {
            $finalExamWeighted = round($finalExam * 0.3, 2);
        }

        $response->getBody()->write(json_encode([
            'components' => $components,
            'total_score' => round($total, 2),
            'final_exam' => $finalExam !== false ? (float)$finalExam : null,
            'final_exam_weighted' => $finalExamWeighted
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/student/{id}/compare-marks/{courseId}', function (Request $request, Response $response, array $args) {
        $pdo = getPDO();
        $studentId = (int)$args['id'];
        $courseId = (int)$args['courseId'];

        // Get all students in the course
        $stmt = $pdo->prepare("
            SELECT u.id AS student_id, u.name
            FROM enrollments e
            JOIN users u ON e.student_id = u.id
            WHERE e.course_id = ?
        ");
        $stmt->execute([$courseId]);
        $students = $stmt->fetchAll();

        $results = [];
        $anonIndex = 0;

        foreach ($students as $student) {
            $sid = $student['student_id'];

            // Get total component marks (70%)
            $compStmt = $pdo->prepare("
                SELECT SUM(m.mark_obtained / c.max_mark * c.weight) AS comp_total
                FROM marks m
                JOIN components c ON m.component_id = c.id
                WHERE m.student_id = ? AND c.course_id = ?
            ");
            $compStmt->execute([$sid, $courseId]);
            $componentScore = $compStmt->fetchColumn() ?? 0;

            // Get final exam mark (30%)
            $finalStmt = $pdo->prepare("
                SELECT mark FROM final_exams
                WHERE student_id = ? AND course_id = ?
            ");
            $finalStmt->execute([$sid, $courseId]);
            $finalScore = $finalStmt->fetchColumn();

            // Scale final exam to 30%
            $finalWeighted = is_numeric($finalScore) ? ($finalScore * 0.30) : 0;

            $total = round($componentScore + $finalWeighted, 2);

            // Label the user as "You", others as "Student A", "Student B", etc.
            $isSelf = $sid === $studentId;
            $label = $isSelf ? 'You' : 'Student ' . chr(65 + $anonIndex++); // A, B, C...

            $results[] = [
                'student_id' => $sid,
                'label' => $label,
                'total' => $total,
                'isSelf' => $isSelf
            ];
        }

        // Sort results descending by total marks (optional)
        usort($results, fn($a, $b) => $b['total'] <=> $a['total']);

        $response->getBody()->write(json_encode($results));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/student/{id}/courses', function (Request $request, Response $response, array $args) {
        $pdo = getPDO();
        $studentId = (int)$args['id'];

        $stmt = $pdo->prepare("
            SELECT c.id, c.code, c.title
            FROM enrollments e
            JOIN courses c ON e.course_id = c.id
            WHERE e.student_id = ?
        ");
        $stmt->execute([$studentId]);
        $courses = $stmt->fetchAll();

        $response->getBody()->write(json_encode($courses));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/student/{id}/rank/{courseId}', function ($request, $response, $args) {
        $pdo = getPDO();
        $studentId = (int)$args['id'];
        $courseId = (int)$args['courseId'];

        // Get all students' total marks in this course
        $stmt = $pdo->prepare("
            SELECT u.id AS student_id, u.name,
                COALESCE(SUM(m.mark_obtained / c.max_mark * c.weight), 0) AS comp_total,
                COALESCE(fe.mark, 0) AS final_mark
            FROM enrollments e
            JOIN users u ON u.id = e.student_id
            LEFT JOIN marks m ON m.student_id = u.id
            LEFT JOIN components c ON m.component_id = c.id AND c.course_id = e.course_id
            LEFT JOIN final_exams fe ON fe.student_id = u.id AND fe.course_id = e.course_id
            WHERE e.course_id = ?
            GROUP BY u.id
        ");
        $stmt->execute([$courseId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($students as $s) {
            $compTotal = (float)$s['comp_total'];
            $finalWeighted = is_numeric($s['final_mark']) ? ($s['final_mark'] * 0.30) : 0;
            $total = round($compTotal + $finalWeighted, 2);

            $results[] = [
                'student_id' => $s['student_id'],
                'name' => $s['name'],
                'comp_total' => round($compTotal, 2),
                'final_weighted' => round($finalWeighted, 2),
                'total' => $total
            ];
        }

        usort($results, fn($a, $b) => $b['total'] <=> $a['total']);

        $rank = 1;
        $studentData = null;

        foreach ($results as $r) {
            if ($r['student_id'] == $studentId) {
                $studentData = $r;
                break;
            }
            $rank++;
        }

        $percentile = 0;
        if ($studentData) {
            $numStudents = count($results);
            $percentile = round((($numStudents - $rank) / $numStudents) * 100, 2);
        }

        $response->getBody()->write(json_encode([
            'rank' => $rank,
            'percentile' => $percentile,
            'total_students' => count($results),
            'score' => $studentData['total'] ?? null,
            'component_score' => $studentData['comp_total'] ?? null,
            'final_exam_score' => $studentData['final_weighted'] ?? null
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/student/{id}/course/{courseId}/class-averages', function ($request, $response, $args) {
        $pdo = getPDO();
        $courseId = (int)$args['courseId'];

        $stmt = $pdo->prepare("
            SELECT c.name AS component, c.max_mark, c.weight,
                ROUND(AVG(m.mark_obtained), 2) AS average_mark
            FROM components c
            JOIN marks m ON c.id = m.component_id
            WHERE c.course_id = ?
            GROUP BY c.id
            ORDER BY c.id
        ");
        $stmt->execute([$courseId]);
        $averages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($averages));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/remark-request', function ($request, $response) {
        $pdo = getPDO();
        $data = json_decode($request->getBody(), true);

        $studentId = (int) $data['student_id'];
        $courseId = (int) $data['course_id'];
        $componentId = (int) $data['component_id'];
        $justification = trim($data['justification']);

        $stmt = $pdo->prepare("
            INSERT INTO remark_requests (student_id, course_id, component_id, justification, status)
            VALUES (?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$studentId, $courseId, $componentId, $justification]);

        $response->getBody()->write(json_encode(['message' => 'Remark request submitted successfully.']));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/course/{courseId}/components', function ($request, $response, $args) {
        $pdo = getPDO();
        $courseId = (int) $args['courseId'];

        $stmt = $pdo->prepare("
            SELECT id, name, weight, max_mark
            FROM components
            WHERE course_id = ?
            ORDER BY id
        ");
        $stmt->execute([$courseId]);
        $components = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($components));
        return $response->withHeader('Content-Type', 'application/json');
    });

?>


