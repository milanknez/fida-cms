<?php

class AuthManager {
    /**
     * Start session if not started.
     */
    public static function initSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Verify if user is logged in, exit with 403 Forbidden if not.
     */
    public static function checkAuth(): void {
        self::initSession();
        if (!self::isAuthenticated()) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
    }

    /**
     * Check if current session is authenticated.
     */
    public static function isAuthenticated(): bool {
        self::initSession();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Authenticate user with password.
     */
    public static function login(string $password, string $correctHash): bool {
        self::initSession();
        if (password_verify($password, $correctHash)) {
            $_SESSION['logged_in'] = true;
            return true;
        }
        return false;
    }

    /**
     * Destroy session.
     */
    public static function logout(): void {
        self::initSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
