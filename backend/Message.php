<?php

class Message {
    private $conversationsFile = '../data/conversations.json';

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