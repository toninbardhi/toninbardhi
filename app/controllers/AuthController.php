<?php
/**
 * Login / logout dell'area riservata.
 */
class AuthController
{
    public static function loginForm(): void
    {
        if (is_logged_in()) {
            redirect('/admin');
        }
        view('auth/login', ['title' => 'Accesso', 'hideSidebar' => true]);
    }

    public static function login(): void
    {
        csrf_check();
        $email = input('email');
        $password = input('password');

        $user = User::verify($email, $password);
        if (!$user) {
            flash('error', 'Email o password non corretti.');
            redirect('/login');
        }

        // Rigenera l'ID di sessione dopo il login (anti session fixation).
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'    => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];
        flash('success', 'Benvenuto, ' . $user['name'] . '!');
        redirect('/admin');
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        redirect('/login');
    }
}
