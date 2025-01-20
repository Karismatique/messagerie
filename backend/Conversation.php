<?php

class Conversation {
    private $conversationsFile = '../data/conversations.json';
    private $user;

    public function __construct() {
        $this->user = new User();
    }

   
    public function create($user1, $user2) {
        $conversations = json_decode(file_get_contents($this->conversationsFile), true);

       
        foreach ($conversations as $conversation) {
            if (in_array($user1, $conversation['users']) && in_array($user2, $conversation['users'])) {
                return $conversation['id'];
            }
        }

       
        $conversationId = uniqid();
        $conversations[] = [
            'id' => $conversationId,
            'users' => [$user1, $user2],
            'messages' => []
        ];
        file_put_contents($this->conversationsFile, json_encode($conversations));
        return $conversationId;
    }

   
    public function getConversations($userId) {
        $conversations = json_decode(file_get_contents($this->conversationsFile), true);
        $userConversations = [];
        foreach ($conversations as $conversation) {
            if (in_array($userId, $conversation['users'])) {
               
                $otherUserId = $conversation['users'][0] === $userId ? $conversation['users'][1] : $conversation['users'][0];

               
                $otherUser = $this->user->getUserById($otherUserId);
                $conversation['otherUserEmail'] = $otherUser ? $otherUser['email'] : 'Utilisateur inconnu';

                $userConversations[] = $conversation;
            }
        }
        return $userConversations;
    }

   
    public function sendMessage($conversationId, $userId, $message) {
        $conversations = json_decode(file_get_contents($this->conversationsFile), true);
        foreach ($conversations as &$conversation) {
            if ($conversation['id'] === $conversationId) {
                $conversation['messages'][] = [
                    'id' => uniqid(),
                    'author' => $userId,
                    'content' => $message,
                    'timestamp' => time()
                ];
                break;
            }
        }
        file_put_contents($this->conversationsFile, json_encode($conversations));
        return true;
    }

   
    public function getMessages($conversationId) {
        $conversations = json_decode(file_get_contents($this->conversationsFile), true);
        foreach ($conversations as $conversation) {
            if ($conversation['id'] === $conversationId) {
                return $conversation['messages'];
            }
        }
        return [];
    }
}