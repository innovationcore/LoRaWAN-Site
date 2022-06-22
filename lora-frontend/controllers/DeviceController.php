<?php

require_once __DIR__ . '/../utilities/db.php';
include_once MODELS_DIR . 'User.php';

class DeviceController {
    public static function index(UserSession $userSession) {
        require DEVICE_VIEWS_DIR . 'index.php';
    }

    public static function addDevice(UserSession $userSession) {
        header('Content-Type: application/json');

        $success = false;
        $message = null;
        $noErrors = true;
        if(!($_POST['join-mode'] == 'OTAA') || ($_POST['join-mode'] == 'ABP')){
            $message = "An error has occured, please refresh the page and try again.";
        } else {
            if (empty($_POST['device-name'])){
                $message = "Device name is required.";
                $noErrors = false;
            } else if (strlen($_POST['device-name']) > 32){
                $message = "Device name must be less than 32 characters.";
                $noErrors = false;
            }
            if (empty($_POST['device-desc'])) {
                $message = "Device description is required.";
                $noErrors = false;
            } else if (strlen($_POST['device-desc']) > 64) {
                $message = "Device description must be less than 64 characters.";
                $noErrors = false;
            }
            if (empty($_POST['device-EUI'])) {
                $message = "Device EUI is required.";
                $noErrors = false;
            } else if (strlen($_POST['device-EUI']) != 16){
                $message = "Device EUI must be 16 characters.";
                $noErrors = false;
            }
            if (empty($_POST['app-key'])){
                if($_POST['join-mode'] == "OTAA") {
                    $message = "Application key is required for OTAA.";
                    $noErrors = false;
                } else {
                    $_POST['app-key'] = "";
                }
            } else if (strlen($_POST['app-key']) != 32){
                $message = "Application key must be 32 characters.";
                $noErrors = false;
            }
            if (empty($_POST['dev-addr'])) {
                if ($_POST['join-mode'] == "ABP") {
                    $message = "Device address is required for ABP.";
                    $noErrors = false;
                } else {
                    $_POST['dev-addr'] = "";
                }
            } else if (strlen($_POST['dev-addr']) != 8){
                $message = "Device address must be 8 characters.";
                $noErrors = false;
            }
            if (empty($_POST['netskey'])){
                if ($_POST['join-mode'] == "ABP") {
                    $message = "Network session key is required for ABP.";
                    $noErrors = false;
                } else {
                    $_POST['netskey'] = "";
                }
            } else if (strlen($_POST['netskey']) != 32){
                $message = "Network session key must be 32 characters.";
                $noErrors = false;
            }
            if (empty($_POST['appskey'])){
                if ($_POST['join-mode'] == "ABP") {
                    $message = "Application session key is required for ABP.";
                    $noErrors = false;
                } else {
                    $_POST['appskey'] = "";
                }
            } else if (strlen($_POST['appskey']) != 32){
                $message = "Application session key must be 32 characters.";
                $noErrors = false;
            }
            if ($noErrors){
                try {
                    $personUpdating = $userSession->getUser()->getLinkblue();
                    DB::run("INSERT INTO devices (dev_name, dev_desc, dev_eui, join_mode, app_key, dev_addr, netskey, appskey, created_by, last_updated) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [$_POST['device-name'], $_POST['device-desc'], $_POST['device-EUI'], $_POST['join-mode'], $_POST['app-key'], $_POST['dev-addr'], $_POST['netskey'], $_POST['appskey'], $personUpdating, $personUpdating]);
                    $message = "Successfully added device.";
                    $success = true;

                } catch (Exception $e){
                    $message = $e->getMessage();
                }
            }
        }
        $ret = array('success' => $success, 'message' => $message);
        echo json_encode((object) array_filter($ret, function($value) { return $value !== null; }));
    }

    public static function updateDevice(UserSession $userSession) {
        header('Content-Type: application/json');

        $success = false;
        $message = null;
        $noErrors = true;
        if(!($_POST['join-mode'] == 'OTAA') || ($_POST['join-mode'] == 'ABP')){
            $message = "An error has occured, please refresh the page and try again.";
        } else {
            if (empty($_POST['device-name'])){
                $message = "Device name is required.";
                $noErrors = false;
            } else if (strlen($_POST['device-name']) > 32){
                $message = "Device name must be less than 32 characters.";
                $noErrors = false;
            }
            if (empty($_POST['device-desc'])) {
                $message = "Device description is required.";
                $noErrors = false;
            } else if (strlen($_POST['device-desc']) > 64) {
                $message = "Device description must be less than 64 characters.";
                $noErrors = false;
            }
            if (empty($_POST['device-EUI'])) {
                $message = "Device EUI is required.";
                $noErrors = false;
            } else if (strlen($_POST['device-EUI']) != 16){
                $message = "Device EUI must be 16 characters.";
                $noErrors = false;
            }
            if (empty($_POST['old-eui'])) {
                $message = "An error has occured, please refresh the page and try again.";
                $noErrors = false;
            } else if (strlen($_POST['old-eui']) != 16){
                $message = "An error has occured, please refresh the page and try again.";
                $noErrors = false;
            }
            if (empty($_POST['app-key'])){
                if($_POST['join-mode'] == "OTAA") {
                    $message = "Application key is required for OTAA.";
                    $noErrors = false;
                } else {
                    $_POST['app-key'] = "";
                }
            } else if (strlen($_POST['app-key']) != 32){
                $message = "Application key must be 32 characters.";
                $noErrors = false;
            }
            if (empty($_POST['dev-addr'])) {
                if ($_POST['join-mode'] == "ABP") {
                    $message = "Device address is required for ABP.";
                    $noErrors = false;
                } else {
                    $_POST['dev-addr'] = "";
                }
            } else if (strlen($_POST['dev-addr']) != 8){
                $message = "Device address must be 8 characters.";
                $noErrors = false;
            }
            if (empty($_POST['netskey'])){
                if ($_POST['join-mode'] == "ABP") {
                    $message = "Network session key is required for ABP.";
                    $noErrors = false;
                } else {
                    $_POST['netskey'] = "";
                }
            } else if (strlen($_POST['netskey']) != 32){
                $message = "Network session key must be 32 characters.";
                $noErrors = false;
            }
            if (empty($_POST['appskey'])){
                if ($_POST['join-mode'] == "ABP") {
                    $message = "Application session key is required for ABP.";
                    $noErrors = false;
                } else {
                    $_POST['appskey'] = "";
                }
            } else if (strlen($_POST['appskey']) != 32){
                $message = "Application session key must be 32 characters.";
                $noErrors = false;
            }
            if ($noErrors){
                try {
                    $personUpdating = $userSession->getUser()->getLinkblue();
                    DB::run("UPDATE devices set dev_name = ?, dev_desc = ?, dev_eui = ?, join_mode = ?, app_key = ?, dev_addr = ?, netskey = ?, appskey = ?, last_updated = ?, last_updated_time = GETDATE() WHERE dev_eui = ? AND created_by = ?", [$_POST['device-name'], $_POST['device-desc'], $_POST['device-EUI'], $_POST['join-mode'], $_POST['app-key'], $_POST['dev-addr'], $_POST['netskey'], $_POST['appskey'], $personUpdating, $_POST['old-eui'], $personUpdating]);
                    $message = "Successfully updated device.";
                    $success = true;

                } catch (Exception $e){
                    $message = $e->getMessage();
                }
            }
        }
        $ret = array('success' => $success, 'message' => $message);
        echo json_encode((object) array_filter($ret, function($value) { return $value !== null; }));
    }

    public static function listDTSimple(UserSession $userSession) {
        header('Content-Type: application/json');

        $device_names = array();
        $device_euis = array();
        $join_modes = array();

        $stmt = DB::run("SELECT dev_name, dev_eui, join_mode FROM devices WHERE created_by = '".$userSession->getUser()->getLinkblue()."'");  //Can run db statements here
        while ($row = $stmt->fetch(PDO::FETCH_LAZY)){
            array_push($device_names, $row['dev_name']);
            array_push($device_euis, $row['dev_eui']);
            array_push($join_modes, $row['join_mode']);
        }

        $start = 0;
        $length = count($device_names);
        if (isset($_GET['start']))
            $start = intval($_GET['start']);
        if (isset($_GET['length']))
            $length = intval($_GET['length']);

        $data = array();
        for ($i=$start; $i < count($device_names) && $i < ($start + $length); $i++) {
            $data_row = array();
            $data_row['DT_RowId'] = $i;
            $data_row['device_name'] = $device_names[$i];
            $data_row['dev_eui'] = $device_euis[$i];
            $data_row['join_mode'] = $join_modes[$i];
        
            array_push($data, $data_row);
        }
        echo json_encode(
            array(
                "draw" => (isset($_GET['draw'])) ? intval($_GET['draw']) : 0,
                "recordsTotal" => intval(sizeof($device_names)),
                "recordsFiltered" => intval(sizeof($device_names)),
                "data" => $data,
            )
        );
    }

    public static function getDevice(UserSession $userSession){
        header('Content-Type: application/json');

        $success = false;
        $data = null;
        $message = null;
        try {
            $personUpdating = $userSession->getUser()->getLinkblue();
            $stmt = DB::run("SELECT * FROM devices WHERE created_by = '".$personUpdating."' AND dev_eui = '".$_GET['dev_eui']."'");
            if ($row = $stmt->fetch(PDO::FETCH_LAZY)){
                $data['dev_name'] = $row['dev_name'];
                $data['dev_desc'] = $row['dev_desc'];
                $data['dev_eui'] = $row['dev_eui'];
                $data['join_mode'] = $row['join_mode'];
                $data['app_key'] = $row['app_key'];
                $data['dev_addr'] = $row['dev_addr'];
                $data['netskey'] = $row['netskey'];
                $data['appskey'] = $row['appskey'];
                $data['created_by'] = $row['created_by'];
                $data['created_at'] = $row['created_at'];
                $data['last_updated'] = $row['last_updated'];
                $data['last_updated_time'] = $row['last_updated_time'];
            }
            $success = true;

        } catch (Exception $e){
            $message = $e->getMessage();
        }
        $ret = array('success' => $success, 'message' => $message, 'data' => $data);
        echo json_encode((object) array_filter($ret, function($value) { return $value !== null; }));
    }

    public static function deleteDevice(UserSession $userSession){
        header('Content-Type: application/json');

        $success = false;
        $message = null;
        
        try {
            $personUpdating = $userSession->getUser()->getLinkblue();
            DB::run("DELETE FROM devices WHERE created_by = ? AND dev_eui = ?", [$personUpdating, $_GET['dev_eui']]);
            $message = "Successfully deleted device.";
            $success = true;

        } catch (Exception $e){
            $message = $e->getMessage();
        }
        $ret = array('success' => $success, 'message' => $message);
        echo json_encode((object) array_filter($ret, function($value) { return $value !== null; }));
    }

    public static function handleUplink(){
        header('Content-Type: application/json');
        $body = file_get_contents('php://input'); // this gets the entire body of the POST request
        var_dump($body);
        

        // Converts it into a PHP object
        // $data = json_decode($json);
        // {
        //     'applicationID':'1',
        //     'applicationName':'Test-App',
        //     'deviceName':'Dragino LDS01',
        //     'devEUI':'a84041e46182965e',
        //     'rxInfo':[
        //         {
        //             'mac':'24e124fffef47838',
        //             'time':'2022-06-13T20:50:41.251515Z',
        //             'rssi':-58,
        //             'loRaSNR':13.8,
        //             'name':'Local Gateway',
        //             'latitude':38.04194,
        //             'longitude':-84.49871,
        //             'altitude':297
        //         }
        //     ],
        //     'txInfo':{
        //         'frequency':904100000,
        //         'dataRate':{
        //             'modulation':'LORA',
        //             'bandwidth':125,
        //             'spreadFactor':7
        //         },
        //         'adr':True,
        //         'codeRate':'4/5'
        //     },
        //     'fCnt':6,
        //     'fPort':10,
        //     'data':'i9YBAAADAAANAA==',
        //     'time':'2022-06-13T20:50:41.251515Z'
        // }



        // try {
        //     DB::run("INSERT INTO uplink_packets (application_id, application_name, dev_eui, rx_info, tx_info, fCnt, fPort, data, time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [$_POST['applicationID'], $_POST['applicationName'], $_POST['devEUI'], $_POST['rxInfo'][0], $_POST['txInfo'][0], $_POST['fCnt'], $_POST['fPort'], $_POST['data'], $_POST['time']]);
        // } catch (Exception $e) {
        //     //probably should report this to an error table/logs
        //     echo json_encode(array('message' => $e));
        // }
        // echo json_encode(array('message' => $data));
        return $requestMethod;

    }

    public static function handleJoin(){
        // {
        //     'applicationID':'1',
        //     'applicationName':'Test-App',
        //     'deviceName':'Dragino LDS01',
        //     'devEUI':'a84041e46182965e',
        //     'devAddr':'07fd2b6a',
        //     'time':'2022-06-14T10:36:23-04:00'
        // }
    }

    public static function handleACK(){
        // ???
    }

    public static function handleError(){
        // ???
    }
}