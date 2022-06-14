<?php
/** @var UserSession $userSession */
$page = 'devices';
include_once __DIR__ . '/../_header.php';
?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h4">Registered Devices</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button id="add-dev-btn" type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#add-new-device">
                <i class="fas fa-plus"></i>
                Add Device
            </button>
        </div>
    </div>


    <div class="modal fade" id="add-new-device" tabindex="-1" role="dialog" aria-labelledby="add-new-device-label" aria-hidden="true" data-update="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="add-new-device-label">Add New Device</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="col-md-12 mb-3 form-floating">
                        <input type="text" class="form-control" id="device-name" autofocus />
                        <label for="device-name">Device Name</label>
                    </div>
                    <div class="col-md-12 mb-3 form-floating">
                        <input type="text" class="form-control" id="device-desc" />
                        <label for="device-desc">Description</label>
                    </div>
                    <div class="col-md-12 mb-3 form-floating">
                        <input type="text" class="form-control" id="device-EUI" />
                        <label for="device-EUI">Device EUI</label>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="OTAA-ABP">Join Mode</label>
                        <select id="OTAA-ABP" name="OTAA-ABP">
                            <option selected value="OTAA">OTAA</option>
                            <option value="ABP">ABP</option>
                        </select> 
                    </div>
                    <div class="col-md-12 mb-3 form-floating">
                        <input type="text" class="form-control" id="device-app-key" />
                        <label for="device-app-key">Application Key</label>
                    </div>
                    <div class="col-md-12 mb-3 form-floating">
                        <input type="text" class="form-control" id="device-addr" disabled />
                        <label for="device-addr">Device Address</label>
                    </div>
                    <div class="col-md-12 mb-3 form-floating">
                        <input type="text" class="form-control" id="device-net-s-key" disabled />
                        <label for="device-net-s-key">Network Session Key</label>
                    </div>
                    <div class="col-md-12 mb-3 form-floating">
                        <input type="text" class="form-control" id="device-app-s-key" disabled />
                        <label for="device-app-s-key">Application Session Key</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button id="dev-submit-btn" type="button" class="btn btn-primary" onclick="addNewDevice();">Submit</button>
                </div>
            </div>
        </div>
    </div>



    <div class="row">
        <div class="col">
            <table id="collection" class="table table-bordered dt-responsive responsive-text" style="width:100%">
                <thead>
                <tr>
                    <th>Name</th>
                    <th style="text-align: center;">Device EUI</th>
                    <th style="text-align: center;">Join Mode</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                <tr>
                    <th>Name</th>
                    <th style="text-align: center;">Device EUI</th>
                    <th style="text-align: center;">Join Mode</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
   
    <script type="text/javascript">
        var collection = {};
        var collectionTable = $('#collection');
        var collectionDataTable = null;
        var old_eui = null;

        $(function() {
            collectionDataTable = collectionTable.DataTable({
                serverSide: false,
                ajax: {
                    url: "/devices/list"
                },
                order: [[ 0, "asc" ]],
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'pageLength', 'colvis'
                ],
                columnDefs: [
                    {
                        className: "dt-center",
                        targets: [0, 1, 2, 3]
                    },
                    {
                        orderable: true,
                        targets: [0, 1, 2]
                    }
                ],
                language: {
                    emptyTable: "No devices have been added"
                },
                pagingType: "full_numbers",
                columns: [
                    {
                        data: 'device_name',
                    },
                    {
                        data: 'dev_eui',
                    },
                    {
                        data: 'join_mode'
                    },
                    {
                        data: null,
                        render: function ( data ) {
                            var html = "";
                            if (data.linkblue !== "<?php echo $userSession->getUser()->getLinkblue(); ?>") {
                                html = "<button class='btn btn-primary btn-xs' onclick='getDevice(\""+data.dev_eui+"\");'>" +
                                    "<span class='fas fa-edit' data-toggle='tooltip' data-placement='left' title='Edit Device'></span>" +
                                    "</button>&nbsp;"+
                                    "<button class='btn btn-danger btn-xs' onclick='deleteDevice(\""+data.dev_eui+"\");'>" +
                                    "<span class='fas fa-trash' data-toggle='tooltip' data-placement='left' title='Delete Device'></span>" +
                                    "</button>";
                            }
                            return html;
                        }
                    }
                ],
                
            });
            collectionDataTable.buttons().container().prependTo('#collection_filter');
            collectionDataTable.buttons().container().addClass('float-left');
            $('.dt-buttons').addClass('btn-group-sm');
            $('.dt-buttons div').addClass('btn-group-sm');
            collectionTable.on('xhr.dt', function (e, settings, data) {
            });
        });

        $("#add-new-device").on('hidden.bs.modal', function() {
            clear_device_modal();
        });

        function clear_device_modal() {
            $('#device-name').val('');
            $('#device-desc').val('');
            $('#device-EUI').val('');
            $('#OTAA-ABP').val('');
            $('#device-app-key').val('');
            $('#device-addr').val('');
            $('#device-net-s-key').val('');
            $('#device-app-s-key').val('');
        }
        function fill_device_form(data) {
            $('#device-name').val(data['dev_name']);
            $('#device-desc').val(data['dev_desc']);
            $('#device-EUI').val(data['dev_eui']);
            $('#OTAA-ABP').val(data['join_mode']);
            $('#device-app-key').val(data['app_key']);
            $('#device-addr').val(data['dev_addr']);
            $('#device-net-s-key').val(data['netskey']);
            $('#device-app-s-key').val(data['appskey']);
        }

        $('#add-dev-btn').click(function() {
            $('#add-new-device').data('update', 'false');
            $('#add-new-device-label').html("Add New Device");
        });

        function addNewDevice(){
            let name = $('#device-name').val().replace(/(<([^>]+)>)/ig,"");
            let desc = $('#device-desc').val().replace(/(<([^>]+)>)/ig,"");
            let eui = $('#device-EUI').val().replace(/(<([^>]+)>)/ig,"");
            let join = $('#OTAA-ABP').val().replace(/(<([^>]+)>)/ig,"");
            let app_key = $('#device-app-key').val().replace(/(<([^>]+)>)/ig,"");
            let dev_addr = $('#device-addr').val().replace(/(<([^>]+)>)/ig,"");
            let netskey = $('#device-net-s-key').val().replace(/(<([^>]+)>)/ig,"");
            let appskey = $('#device-app-s-key').val().replace(/(<([^>]+)>)/ig,"");
            if (name === "") {
                showError("Device name is required.");
                return false;
            }
            if (desc === ""){
                showError("Device description is required.");
                return false;
            }
            if (eui === ""){
                showError("Device EUI is required.");
                return false;
            }
            if (!(join === 'OTAA' || join === "ABP")){
                showError('An error has occurred, please refresh the page and try again.');
                return false;
            }
            if (app_key === "" && join === "OTAA"){
                showError("Application key is required for OTAA.");
                return false;
            }
            if (dev_addr === "" && join === "ABP"){
                showError("Device address is required for ABP.");
                return false;
            }
            if (netskey === "" && join === "ABP"){
                showError("Network session key is required for ABP.");
                return false;
            }
            if (appskey === "" && join === "ABP"){
                showError("Application session key is required for ABP.");
                return false;
            }
            if (join === 'OTAA'){
                var device_data = {
                    'device-name': name,
                    'device-desc': desc,
                    'device-EUI': eui,
                    'join-mode': join,
                    'app-key': app_key
                }
            } else {
                var device_data = {
                    'device-name': name,
                    'device-desc': desc,
                    'device-EUI': eui,
                    'join-mode': join,
                    'dev-addr': dev_addr,
                    'netskey': netskey,
                    'appskey': appskey
                }
            }
            
            if ($('#add-new-device').data('update') == 'true') {
                device_data['old-eui'] = old_eui;
                $.ajax({
                    url : '/devices/update-device',
                    type : 'POST',
                    data : device_data,

                    success : function(data) {
                        if (data['success']) {
                            showSuccess(data['message']);
                            $('#add-new-device').modal('hide');
                            collectionTable.DataTable().ajax.reload();
                        } else {
                            showError(data['message']);
                        }
                    },
                    error : function(request, error){
                        showError(error);
                    }
                });
            }
            else {
                $.ajax({
                    url : '/devices/add-device',
                    type : 'POST',
                    data : device_data,

                    success : function(data) {
                        if (data['success']) {
                            showSuccess(data['message']);
                            $('#add-new-device').modal('hide');
                            collectionTable.DataTable().ajax.reload();
                        } else {
                            showError(data['message']);
                        }
                    },
                    error : function(request, error){
                        showError(error);
                    }
                });
            }
        }

        $('#OTAA-ABP').on('change', function(){
            if (this.value === 'OTAA'){
                $('#device-addr').attr("disabled", 'disabled');
                $('#device-net-s-key').attr("disabled", 'disabled');
                $('#device-app-s-key').attr("disabled", 'disabled');
                $('#device-app-key').removeAttr('disabled');
            } else {
                $('#device-addr').removeAttr("disabled");
                $('#device-net-s-key').removeAttr("disabled");
                $('#device-app-s-key').removeAttr("disabled");
                $('#device-app-key').attr('disabled', 'disabled');

            }
        });

        function getDevice(dev_eui) {
            $('#add-new-device-label').html("Update Device");

            $.ajax({
                url : '/devices/get-device',
                type : 'GET',
                data : {
                    'dev_eui': dev_eui
                },

                success : function(data) {
                    if (data['success']) {
                        fill_device_form(data['data']);
                        $('#add-new-device').data('update', 'true');
                        $('#add-new-device').modal('show');
                        old_eui = data['data']['dev_eui'];
                    } else {
                        showError(data['message']);
                    }
                },
                error : function(request, error){
                    showError(error);
                }
            });
        }

        function deleteDevice(dev_eui) {
            if(confirm("Are you sure you want to delete this device?")){
                $.ajax({
                    url : '/devices/delete-device',
                    type : 'GET',
                    data : {
                        'dev_eui': dev_eui
                    },

                    success : function(data) {
                        if (data['success']) {
                            showSuccess(data['message']);
                            collectionTable.DataTable().ajax.reload();
                        } else {
                            showError(data['message']);
                        }
                    },
                    error : function(request, error){
                        showError(error);
                    }
                });
            }
        }
    </script>
<?php
include_once __DIR__ . '/../_footer.php';