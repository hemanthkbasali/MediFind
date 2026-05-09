<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function current_role(): ?string
{
    return $_SESSION['role'] ?? null;
}

function is_logged_in(string $role): bool
{
    return current_role() === $role && !empty($_SESSION[$role . '_id']);
}

function require_login(string $role): void
{
    if (!is_logged_in($role)) {
        set_flash('warning', 'Please log in to continue.');
        redirect($role . '/login.php');
    }
}

function login_session(string $role, int $id, string $name): void
{
    session_regenerate_id(true);
    $_SESSION['role'] = $role;
    $_SESSION[$role . '_id'] = $id;
    $_SESSION[$role . '_name'] = $name;
}

function logout_session(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}

function current_user_id(string $role): int
{
    return (int) ($_SESSION[$role . '_id'] ?? 0);
}

function current_user_name(string $role): string
{
    return (string) ($_SESSION[$role . '_name'] ?? 'User');
}
