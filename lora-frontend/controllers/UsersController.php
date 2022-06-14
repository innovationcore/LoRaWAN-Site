<?php

const USERS_VIEWS_DIR = __DIR__ . '/../views/users/';

include_once MODELS_DIR . 'User.php';
include_once MODELS_DIR . 'UserSession.php';

class UsersController {
    public static function index(UserSession $userSession) {
        require USERS_VIEWS_DIR . 'index.php';
    }

    public static function listForDatatable(UserSession $userSession) {
        header("Content-Type: application/json");
        $start = 0;
        if (isset($_GET['start']))
            $start = intval($_GET['start']);
        $length = 0;
        if (isset($_GET['length']))
            $length = intval($_GET['length']);
        $filter = '';
        if (isset($_GET['search']['value']))
            $filter = $_GET['search']['value'];
        $order_by = '';//'symbol';
        if (isset($_GET['order'][0]['column']))
            $order_by = $_GET['order'][0]['column'];
        $order_dir = 'desc';
        if (isset($_GET['order'][0]['dir']))
            $order_dir = $_GET['order'][0]['dir'];
        $data = array();
        $idx = $start;
        $results = User::listForDatatable($start, $length, $order_by, $order_dir, $filter);
        foreach ($results as $result) {
            $data_row = $result->jsonSerialize();
            $data_row['DT_RowId'] = $idx++;
            array_push($data, $data_row);
        }
        echo json_encode(
            array(
                'draw' => (isset($_GET['draw'])) ? intval($_GET['draw']) : 0,
                'recordsTotal' => intval(User::countForDatatable()),
                'recordsFiltered' => intval(User::countFilteredForDatatable($filter)),
                'data' => $data,
            )
        );
    }

    public static function submit(UserSession $userSession) {
        header("Content-Type: application/json");
        $success = false;
        $error_message = null;
        $user = null;
        $action = "update";
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            $action = "create";
        }
        if (!isset($_POST['linkblue'])) {
            $error_message = "You must supply account linkblue to submit user information";
        } else if (!isset($_POST['role'])) {
            $error_message = "You must supply account privileges to submit user information";
        } else {
            $id = $_POST['id'];
            $linkblue = $_POST['linkblue'];
            $role = $_POST['role'];
            try {
                if ($action == "create") {
                    $user = User::withLinkblue($linkblue);
                    if ($user != null) {
                        $error_message = "User {$user->getLinkblue()} already exists";
                    } else {
                        $user = User::create($linkblue, $role);
                        $success = true;
                    }
                } else if ($action == "update") {
                    $user = User::update($id, $role);
                    $success = true;
                } else {
                    $error_message = "An invalid action has been attempted";
                }
            } catch (Exception $e) {
                $error_message = $e->getMessage();
            }
        }
        $ret = array('success' => $success, 'action' => $action, 'error_message' => $error_message, 'user' => $user);
        echo json_encode((object) array_filter($ret, function($value) { return $value !== null; }));
    }

    public static function getUser(UserSession $userSession) {
        header("Content-Type: application/json");
        $success = false;
        $error_message = null;
        $user = $userSession->getUser()->getLinkblue();

        try {
            $userinfo = User::withLinkblue($user);
            $success = true;
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        $ret = array('success' => $success, 'error_message' => $error_message, 'user' => $userinfo);
        echo json_encode((object) array_filter($ret, function($value) { return $value !== null; }));
    }

    public static function getRoles(UserSession $userSession) {
        header("Content-Type: application/json");
        $success = false;
        $error_message = null;

        try {
            $roles = User::getAllRoles();
            $success = true;
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
        $ret = array('success' => $success, 'error_message' => $error_message, 'roles' => $roles);
        echo json_encode((object) array_filter($ret, function($value) { return $value !== null; }));
    }

    public static function deleteUser(UserSession $userSession) {
        header("Content-Type: application/json");
        $success = false;
        $error_message = null;
        $user = null;
        
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            $error_message = "User ID not found.";
        }
        else {
            $id = $_POST['id'];
            try {
                $user = User::withId($id);
                if ($user != null) {
                    $status = User::delete($id);
                    if ($status) {
                        $success = true;
                    } else {
                        $error_message = "User could not be deleted.";
                    }
                } else {
                    $error_message = "User not found.";
                }
                
            } catch (Exception $e) {
                $error_message = $e->getMessage();
            }
        }
        $ret = array('success' => $success, 'error_message' => $error_message);
        echo json_encode((object) array_filter($ret, function($value) { return $value !== null; }));
    }
}