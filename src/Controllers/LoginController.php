<?php

namespace App\Controllers;

use App\Models\Question;
use App\Models\UserAnswer;
use App\Models\User;

class LoginController extends BaseController
{ 
    public function loginForm() {
        $this->initializeSession();

        // Default value for remaining attempts
        $data = [
            'remaining_attempts' => null
        ];

        return $this->renderView('login-form', $data);
    }

    public function login() {
        $this->initializeSession();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                return $this->handleLoginFailure(["email and password are required."]);
            }

            $user = new User();
            $verified = $user->verifyAccess($email, $password);

            if ($verified) {
                $this->onSuccessfulLogin($email);
            } else {
                return $this->incrementLoginAttempts();
            }
        } else {
            return $this->loginForm();
        }
    }

    private function onSuccessfulLogin($email) {
        // Reset attempts and store session data
        $_SESSION['login_attempts'] = 0;
        $_SESSION['is_logged_in'] = true;
        $_SESSION['email'] = $email;

        if (!isset($_SESSION['user_id'])) {
            $obj = new User();
            $_SESSION['user_id'] = $obj->getUserID($email);
        }

        // Redirect after successful login
        header("Location: /exam");
        exit;
    }

    private function incrementLoginAttempts() {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $remainingAttempts = 3 - $_SESSION['login_attempts'];

        if ($remainingAttempts <= 0) {
            return $this->handleLoginFailure(
                ["Too many failed login attempts. Please try again later."], true
            );
        }

        return $this->handleLoginFailure(
            ["Invalid email or password. Attempts remaining: $remainingAttempts."], false, $remainingAttempts
        );
    }

    private function handleLoginFailure($errors, $formDisabled = false, $remainingAttempts = null) {
        return $this->renderView('login-form', [
            'errors' => $errors,
            'form_disabled' => $formDisabled,
            'remaining_attempts' => $remainingAttempts
        ]);
    }

    

    public function logout() {
        $this->initializeSession();
        session_destroy();
        header("Location: /");
        exit;
    }

    private function renderView($template, $data = []) {
        return $this->render($template, $data);
    }
}