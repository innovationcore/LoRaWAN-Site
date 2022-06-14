<?php

include_once MODELS_DIR . 'User.php';
include_once MODELS_DIR . 'UserSession.php';

class RootController {
    public static function index(UserSession $userSession) {
        require VIEWS_DIR . 'home.php';
    }

    public static function login() {
        if (!is_null(UserSession::withSessionID(session_id())))
            header('Location: /');
        require VIEWS_DIR . 'login.php';
    }

    public static function do_login($linkblue, $password, $remember_me) {
        if (!is_null(UserSession::withSessionID(session_id())))
            header('/');
        try {
            $config = include CONFIG_FILE;
            if (
                (
                    !$config['ldap']['enabled'] && self::check_password_fake($linkblue, $password)
                ) ||
                (
                    self::check_ldap_password($linkblue, $password)
                )
            ) {
                $user = User::withLinkblue($linkblue);
                if (is_null($user)) {
                    if ($config['users']['auto-create'] || in_array($linkblue, $config['users']['permitted']))
                        $user = User::create($linkblue, in_array($linkblue, $config['admins']));
                    else
                        throw new Exception("User with linkblue [{$linkblue}] does not exist");
                }
                UserSession::create(session_id(), $user, $remember_me);
                self::post_login_redirect();
            } else
                throw new Exception("Invalid username/password");
        } catch (Exception $e) {
            throw new Exception("Error authenticating, please try again: " . $e->getMessage());
        }
    }

    public static function logout() {
        UserSession::delete(session_id());
        session_destroy();
        session_start();
        session_regenerate_id();
        header('Location: /login');
    }

    private static function check_password_fake($linkblue, $password): bool {
        return !empty($linkblue) && !empty($password);
    }

    private static function check_ldap_password($linkblue, $password): bool {
        try {
            $config = include CONFIG_FILE;
            $ldapconn = ldap_connect($config['ldap']['host']);
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
            if ($ldapconn) {
                set_error_handler(function (int $errno, string $errstr) {
                    throw new Exception("Authentication failed [{$errno}]: {$errstr}");
                });
                $ldapbind = ldap_bind(
                    $ldapconn,
                    "{$config['ldap']['prefix']}{$linkblue}{$config['ldap']['suffix']}",
                    $password
                );
                restore_error_handler();
                if ($ldapbind) {
                    return true;
                } else {
                    return false;
                }
            } else {
                throw new Exception("Failed to initialize connection object with host " .
                    "[{$config['ldap']['host']}], prefix [{$config['ldap']['prefix']}]," .
                    ", and suffix [{$config['ldap']['suffix']}]");
            }
        } catch (Exception $e) {
            throw new Exception('Could not connect to authentication server: ' . $e->getMessage());
        }
    }

    private static function post_login_redirect() {
        if (!isset($_SESSION['redirect']) || is_null($_SESSION['redirect']))
            header('Location: /');
        else {
            $redirect = $_SESSION['redirect'];
            $_SESSION['redirect'] = null;
            header('Location: ' . $redirect);
        }
    }
}