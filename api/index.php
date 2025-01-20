<?php

header("Content-Type: application/json");
require_once '../backend/User.php';
require_once '../backend/Conversation.php';
require_once '../backend/Message.php';

$user = new User();
$conversation = new Conversation();
$message = new Message();

$input = json_decode(file_get_contents('php://input'), true);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        if ($email && $password) {
            $result = $user->register($email, $password);
            echo json_encode($result);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Email et mot de passe requis']);
        }
        break;

    case 'login':
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        if ($email && $password) {
            $result = $user->login($email, $password);
            echo json_encode($result);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Email et mot de passe requis']);
        }
        break;

    case 'getUsers':
        $result = $user->getUsers();
        echo json_encode($result);
        break;

    case 'getConversations':
        $userId = $_GET['userId'] ?? '';
        $conversations = $conversation->getConversations($userId);
        echo json_encode(['status' => 'success', 'conversations' => $conversations]);
        break;

    case 'sendMessage':
        $conversationId = $input['conversationId'] ?? '';
        $userId = $input['userId'] ?? '';
        $messageContent = $input['message'] ?? '';
        if ($conversation->sendMessage($conversationId, $userId, $messageContent)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
        break;

    case 'getMessages':
        $conversationId = $_GET['conversationId'] ?? '';
        $messages = $message->getMessages($conversationId);
        echo json_encode(['status' => 'success', 'messages' => $messages]);
        break;

    case 'createConversation':
        $user1 = $input['user1'] ?? '';
        $user2 = $input['user2'] ?? '';
        if ($user1 && $user2) {
            $conversationId = $conversation->create($user1, $user2);
            echo json_encode(['status' => 'success', 'conversationId' => $conversationId]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Missing user IDs']);
        }
        break;

    case 'checkNewMessages':
        $conversationId = $_GET['conversationId'] ?? '';
        $lastMessageId = $_GET['lastMessageId'] ?? '';
        if ($conversationId) {
            $messages = $conversation->getMessages($conversationId);
            $newMessages = array_filter($messages, function ($msg) use ($lastMessageId) {
                return $msg['id'] !== $lastMessageId;
            });
            echo json_encode(['status' => 'success', 'messages' => array_values($newMessages)]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Conversation ID requis']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Action not found']);
        break;
}