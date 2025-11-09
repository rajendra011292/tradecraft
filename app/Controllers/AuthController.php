<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Token;
use App\Support\Mailer;

class AuthController extends Controller
{
    public function showLogin(): void { $this->view('auth/login'); }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = (new User($this->app))->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            $this->app->flash->error('Invalid credentials');
            $this->redirect('/auth/login');
        }
        $this->app->session->set('user_id', (int)$user['id']);
        $this->app->flash->success('Welcome back!');
        $this->redirect('/app/dashboard');
    }

    public function showRegister(): void { $this->view('auth/register'); }

    public function register(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$name || !$email || strlen($password) < 8) {
            $this->app->flash->error('Please fill all fields (password >= 8).');
            $this->redirect('/auth/register');
        }
        $userModel = new User($this->app);
        if ($userModel->findByEmail($email)) {
            $this->app->flash->error('Email already registered.');
            $this->redirect('/auth/register');
        }
        $uid = $userModel->create($name, $email, $password);
        $token = (new Token($this->app))->create($uid, 'verify');
        $link = $this->app->env->get('APP_URL','http://localhost').'/auth/verify?token='.$token;
        (new Mailer($this->app->env))->send($email, 'Verify your email', 'Click: '.$link);
        $this->app->flash->success('Account created. Check storage/mail for verification link.');
        $this->redirect('/auth/login');
    }

    public function verifyEmail(): void
    {
        $token = $_GET['token'] ?? '';
        $uid = (new Token($this->app))->consume($token, 'verify');
        if (!$uid) { echo 'Invalid token'; return; }
        (new User($this->app))->markVerified($uid);
        $this->app->flash->success('Email verified. You can login.');
        $this->redirect('/auth/login');
    }

    public function showForgot(): void { $this->view('auth/forgot'); }

    public function sendReset(): void
    {
        $email = trim($_POST['email'] ?? '');
        $user = (new User($this->app))->findByEmail($email);
        if ($user) {
            $token = (new Token($this->app))->create((int)$user['id'], 'reset');
            $link = $this->app->env->get('APP_URL','http://localhost').'/auth/reset?token='.$token;
            (new Mailer($this->app->env))->send($email, 'Reset Password', 'Click: '.$link);
        }
        $this->app->flash->success('If the email exists, a reset link was created in storage/mail.');
        $this->redirect('/auth/forgot');
    }

    public function showReset(): void { $this->view('auth/reset'); }

    public function resetPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $uid = (new Token($this->app))->consume($token, 'reset');
        if (!$uid || strlen($password)<8) {
            $this->app->flash->error('Invalid token or weak password.');
            $this->redirect('/auth/reset?token=' . urlencode($token));
        }
        (new User($this->app))->updatePassword($uid, $password);
        $this->app->flash->success('Password updated. Please login.');
        $this->redirect('/auth/login');
    }

    public function logout(): void
    {
        $this->app->session->destroy();
        $this->redirect('/');
    }
}
