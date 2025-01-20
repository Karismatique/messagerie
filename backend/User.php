<?php

class User {
    private $usersFile = '../data/users.json';

   
    public function register($email, $password) {
        $users = json_decode(file_get_contents($this->usersFile), true);

       
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return ['status' => 'error', 'message' => 'Email déjà utilisé'];
            }
        }

       
        $users[] = [
            'id' => uniqid(),
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT)
        ];

       
        file_put_contents($this->usersFile, json_encode($users));

        return ['status' => 'success'];
    }

   
    public function login($email, $password) {
        $users = json_decode(file_get_contents($this->usersFile), true);
        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                return ['status' => 'success', 'userId' => $user['id']];
            }
        }
        return ['status' => 'error', 'message' => 'Email ou mot de passe incorrect'];
    }

   
    public function getUsers() {
        $users = json_decode(file_get_contents($this->usersFile), true);
        return ['status' => 'success', 'users' => $users];
    }

    public function getUserById($userId) {
        $users = json_decode(file_get_contents($this->usersFile), true);
        foreach ($users as $user) {
            if ($user['id'] === $userId) {
                return $user;
            }
        }
        return null;
    }
}