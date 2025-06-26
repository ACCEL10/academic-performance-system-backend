<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;


// === POST /login-admin ===
$app->post('/login-admin', function (Request $request, Response $response) {
    $pdo = getPDO();

    $data = json_decode($request->getBody()->getContents(), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password === $user['password_hash']) {
        $response->getBody()->write(json_encode([
            'message' => 'Admin login successful',
            'user' => $user
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    $response->getBody()->write(json_encode(['error' => 'Invalid admin credentials']));
    return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
});


// === GET /admin/users ===
// Get all users (for admin panel)
$app->get('/admin/users', function (Request $request, Response $response) {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT id, name, email, role FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($users));
    return $response->withHeader('Content-Type', 'application/json');
});

// === GET /users ===
    $app->get('/users', function (Request $request, Response $response) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT id, name, email, role FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // === PUT /users/{id}/role ===
    $app->put('/users/{id}/role', function (Request $request, Response $response, array $args) {
        $pdo = getPDO();
        $id = $args['id'];
        $data = json_decode($request->getBody()->getContents(), true);
        $newRole = $data['role'] ?? null;

        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $success = $stmt->execute([$newRole, $id]);

        if ($success) {
            $response->getBody()->write(json_encode(['message' => 'User role updated successfully.']));
        } else {
            $response->getBody()->write(json_encode(['error' => 'Failed to update user role.']));
            return $response->withStatus(500);
        }

        return $response->withHeader('Content-Type', 'application/json');
    });

// === GET /lecturers ===
$app->get('/lecturers', function (Request $request, Response $response) {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE role = 'lecturer'");
    $stmt->execute();
    $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($lecturers));
    return $response->withHeader('Content-Type', 'application/json');
});

// === GET /courses ===
$app->get('/courses', function (Request $request, Response $response) {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT c.id, c.code, c.title, c.lecturer_id, u.name AS lecturer_name
                           FROM courses c
                           LEFT JOIN users u ON c.lecturer_id = u.id");
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($courses));
    return $response->withHeader('Content-Type', 'application/json');
});

// === PUT /courses/{id} ===
$app->put('/courses/{id}', function (Request $request, Response $response, array $args) {
    $pdo = getPDO();
    $id = $args['id'];
    $data = json_decode($request->getBody()->getContents(), true);
    $lecturerId = $data['lecturer_id'] ?? null;

    $stmt = $pdo->prepare("UPDATE courses SET lecturer_id = ? WHERE id = ?");
    $success = $stmt->execute([$lecturerId, $id]);

    if ($success) {
        $response->getBody()->write(json_encode(['message' => 'Lecturer assigned successfully']));
    } else {
        $response->getBody()->write(json_encode(['error' => 'Failed to assign lecturer']));
        return $response->withStatus(500);
    }

    return $response->withHeader('Content-Type', 'application/json');
});

 // === GET /mark-updates ===
    $app->get('/mark-updates', function (Request $request, Response $response) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT m.id, m.component_id, c.name AS component_name, m.student_id, u.name AS student_name, m.mark_obtained
                               FROM marks m
                               LEFT JOIN users u ON m.student_id = u.id
                               LEFT JOIN components c ON m.component_id = c.id
                               ORDER BY m.id DESC");
        $stmt->execute();
        $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($marks));
        return $response->withHeader('Content-Type', 'application/json');
    });



