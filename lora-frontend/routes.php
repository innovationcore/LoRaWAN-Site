<?php
    define('CONFIG_FILE', __DIR__ . '/config.php');
    define('MODELS_DIR', __DIR__ . '/models/');
    define('UTILITIES_DIR', __DIR__ . '/utilities/');
    define('VIEWS_DIR', __DIR__ . '/views/');
    define('DEVICE_VIEWS_DIR', __DIR__ . '/views/device/');

    include_once __DIR__ . '/controllers/RootController.php';
    include_once __DIR__ . '/controllers/DeviceController.php';
    include_once __DIR__ . '/controllers/UsersController.php';

    /**
     * @param string $redirect
     * @param false $requires_admin
     * @param string $admin_redirect
     * @return UserSession
     */
    function get_session($redirect = '/login', $requires_admin = false, $admin_redirect = '/'): UserSession {
        $session = UserSession::withSessionID(session_id());
        if (is_null($session))
            header('Location: ' . $redirect);
        else {
            if (is_null($session->getUser()))
                header('Location: /logout');
            else if ($requires_admin && !$session->getUser()->isAdmin())
                header('Location: ' . $admin_redirect);
        }
        return $session;
    }

    $router = new AltoRouter();

    /* RootController routes */
    try {
        $router->map('GET', '/', function() {
            RootController::index(get_session());
        }, 'dashboard');
        $router->map('GET', '/login', function() {
            RootController::login();
        }, 'login');
        $router->map('POST', '/login', function() {
            try {
                RootController::do_login(strtolower($_POST['user']), $_POST['password'], 0);
            } catch (Exception $e) {
                $_SESSION['LOGIN_ERROR'] = $e->getMessage();
                header('Location: /login');
            }
        });
        $router->map('GET', '/logout', function() {
            RootController::logout();
        }, 'logout');
    } catch (Exception $e) {
        die("Failed to create route(s) from RootController section: " . $e->getMessage());
    }

    /* DeviceController routes */
    try {
        $router->map('GET', '/devices', function() {
            DeviceController::index(get_session());
        }, 'devices-index');
        $router->map('POST', '/devices/add-device', function() {
            DeviceController::addDevice(get_session());
        }, 'devices-add');
        $router->map('POST', '/devices/update-device', function() {
            DeviceController::updateDevice(get_session());
        }, 'devices-update');
        $router->map('GET', '/devices/list', function() {
            DeviceController::listDTSimple(get_session());
        }, 'devices-list-dt-simple');
        $router->map('GET', '/devices/get-device', function() {
            DeviceController::getDevice(get_session());
        }, 'devices-get');
        $router->map('GET', '/devices/delete-device', function() {
            DeviceController::deleteDevice(get_session());
        }, 'devices-delete');
        $router->map('POST', '/devices/uplink', function() {
            DeviceController::handleUplink();
        }, 'devices-uplink');
        $router->map('POST', '/devices/join', function() {
            DeviceController::handleJoin();
        }, 'devices-join');
        $router->map('POST', '/devices/ack', function() {
            DeviceController::handleACK();
        }, 'devices-ack');
        $router->map('POST', '/devices/error', function() {
            DeviceController::handleError();
        }, 'devices-error');
        
    } catch (Exception $e) {
        die("Failed to create route(s) from Page1Controller section: " . $e->getMessage());
    }

    try {
        /* UsersController routes */
        $router->map('GET', '/users', function() {
            UsersController::index(get_session("/", true));
        }, 'users-index');
        $router->map('GET', '/users/list', function() {
            UsersController::listForDatatable(get_session("/", true));
        }, 'users-for-datatable');
        $router->map('POST', '/users/submit', function() {
            UsersController::submit(get_session("/", true));
        }, 'users-update');
        $router->map('GET', '/users/getUser', function() {
            UsersController::getUser(get_session());
        }, 'users-get');
    } catch (Exception $e) {
        die("Failed to create route(s) from UsersController section: " . $e->getMessage());
    }

    /* DEBUG routes */
    try {
        $router->map('GET', '/info', function() {
            phpinfo();
        });
    } catch (Exception $e) {
        die("Failed to create route(s) from DEBUG section: " . $e->getMessage());
    }

    $match = $router->match();

    // Call closure or throw 404 status
    if ($match && is_callable($match['target'])) {
        call_user_func_array($match['target'], $match['params']);
    } else {
        // No route was matched
        //header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
        $from = $_SERVER['REQUEST_URI'];
        if (strlen($from) > 20)
            $from = substr($from, 0, 20) . '...';
        $_SESSION['FLASH_ERROR'] = "No page exists at {$from}";
        if(isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header('Location: /');
        }
    }