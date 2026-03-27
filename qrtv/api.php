<?php
header('Content-Type: application/json');
$dataFile = 'data.json';

// Leer datos actuales
$jsonData = file_get_contents($dataFile);
$data = json_decode($jsonData, true);

// Lógica de borrado automático cada 24hs (86400 segundos)
if ($data['settings']['auto_delete']) {
    $now = time();
    $filteredMessages = [];
    foreach ($data['messages'] as $msg) {
        if (($now - $msg['timestamp']) < 86400) {
            $filteredMessages[] = $msg;
        }
    }
    $data['messages'] = $filteredMessages;
    file_put_contents($dataFile, json_encode($data));
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'get';

if ($action === 'get') {
    echo json_encode($data);
    exit;
}

if ($action === 'add') {
    $text = filter_input(INPUT_POST, 'text', FILTER_SANITIZE_STRING);
    if ($text) {
        $data['messages'][] = [
            'id' => uniqid(),
            'text' => $text,
            'timestamp' => time()
        ];
        file_put_contents($dataFile, json_encode($data));
        echo json_encode(['status' => 'ok']);
    }
    exit;
}

if ($action === 'delete') {
    $id = $_POST['id'];
    $data['messages'] = array_filter($data['messages'], function($msg) use ($id) {
        return $msg['id'] !== $id;
    });
    $data['messages'] = array_values($data['messages']); // Reindexar
    file_put_contents($dataFile, json_encode($data));
    echo json_encode(['status' => 'ok']);
    exit;
}

if ($action === 'delete_all') {
    $data['messages'] = [];
    file_put_contents($dataFile, json_encode($data));
    echo json_encode(['status' => 'ok']);
    exit;
}

if ($action === 'update_settings') {
    $data['settings']['color'] = $_POST['color'];
    $data['settings']['auto_delete'] = filter_var($_POST['auto_delete'], FILTER_VALIDATE_BOOLEAN);
    file_put_contents($dataFile, json_encode($data));
    echo json_encode(['status' => 'ok']);
    exit;
}
?>