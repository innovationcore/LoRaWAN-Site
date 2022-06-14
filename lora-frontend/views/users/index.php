<?php
/** @var UserSession $userSession */
$page = "users";
include_once __DIR__ . '/../_header.php';
?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h4">Users</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#userModal">
                <i class="fas fa-user-plus"></i> Add New User
            </button>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <table id="users" class="table table-striped table-bordered dt-responsive responsive-text" style="width:100%">
                <thead>
                <tr>
                    <th>Linkblue</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                <tr>
                    <th>Linkblue</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">User Management</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" id="userModalUserId" value="" />
                        <div class="col-sm-12 mb-3 form-floating">
                            <input class="form-control" type="text" style="pointer-events: auto;" id="userModalLinkblue" placeholder="Linkblue" />
                            <label for="userModalLinkblue">Linkblue</label>
                        </div>
                        <div class="col-lg-5">
                            <label>Account Role:</label>
                        </div>
                        <div class="col-lg-7 mb-2">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-secondary" id="userModalAccountType">
                                <select name="user-roles" id="user-roles">
                                    <option value="-1">-- Select Role --</option>
                                </select>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submit_user();">Submit User</button>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var users_table = $('#users');
        var users_datatable = null;
        var users = {}

        var userModal = $('#userModal');
        var userModalUserId = $('#userModalUserId');
        var userModalLinkblue = $('#userModalLinkblue');
        var userModalAccountTypeOptions = $('#userModalAccountTypeOptions');

        var userModalAccountType = $('#userModalAccountType');

        $.ajax(
            {
                url: "/users/getRoles", 
                success: 
                function(result){
                    $.each(result.roles, function(index){
                        $('#user-roles').append('<option value="'+result.roles[index][0]+'">'+result.roles[index][1]+'</option>');
                    });
                }
            }
        );

        function fill_user_form(user) {
            if (user !== null) {
                userModalUserId.val(user.id);
                userModalLinkblue.val(user.linkblue);
                $('#user-roles').val(user.role);
                $('#user-roles').each(function(){
                    $(this).removeAttr('hidden');
                });
            }
        }

        function edit_user(user_id) {
            fill_user_form(users[user_id]);
            userModalLinkblue.attr('disabled', 'disabled');
            userModal.modal('show');
        }

        function delete_user(user_id) {
            let isExecuted = confirm("Are you sure to delete this user? This action is not reversible.");
            if (isExecuted) {
                $.post({
                    url: '/users/deleteUser',
                    data: {'id': user_id},
                    dataType: 'json'
                }).done(function(data) {
                    if (data.success) {
                        showSuccess('Successfully removed user');
                        users_datatable.ajax.reload( null, false );
                        userModal.modal('hide');
                    } else {
                        showError(data.error_message);
                    }
                });
            }
        }

        function clear_user_form() {
            userModalUserId.val('');
            userModalLinkblue.val('');
            userModalLinkblue.attr('disabled', false);
            $('#user-roles').val("-1");
            $('#user-roles').each(function(){
                $(this).attr('hidden');
            });
        }

        userModal.on('hidden.bs.modal', function() {
            clear_user_form();
        });

        function submit_user() {
            if (userModalLinkblue.val() === null || userModalLinkblue.val() === '') {
                showError('You must supply an user linkblue');
                return;
            }

            let userRole = $('#user-roles').val()
            if (userRole === "-1"){
                showError('You must choose a user role');
                return;
            }

            var formData = {
                'id': userModalUserId.val(),
                'linkblue': userModalLinkblue.val(),
                'role': userRole
            };
            $.post({
                url: '/users/submit',
                data: formData,
                dataType: 'json'
            }).done(function(data) {
                if (data.success) {
                    showSuccess('Successfully ' + data.action + ' user');
                    users_datatable.ajax.reload( null, false );
                    userModal.modal('hide');
                } else {
                    showError(data.error_message);
                }
            });
        }

        $(function() {
            clear_user_form();
            users_datatable = users_table.DataTable({
                serverSide: false,
                ajax: {
                    url: "/users/list"
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
                        targets: [0, 1, 2]
                    },
                    {
                        orderable: false,
                        targets: [1, 2]
                    }
                ],
                language: {
                    emptyTable: "No users have been added"
                },
                pagingType: "full_numbers",
                columns: [
                    {
                        data: 'linkblue'
                    },
                    {
                        data: 'role',
                        render: function ( data ) {
                            if (data == 0) {
                                return "Admin";
                            }
                            //else if () {}
                            else {
                                return "User";
                            }
                            
                        }
                    },
                    {
                        data: null,
                        render: function ( data ) {
                            var html = "";
                            if (data.linkblue !== "<?php echo $userSession->getUser()->getLinkblue(); ?>") {
                                html = "<button class='btn btn-primary btn-xs' onclick='edit_user(\"" + data.id + "\");'>" +
                                    "<span class='fas fa-user-edit' data-toggle='tooltip' data-placement='left' title='Edit User'></span>" +
                                    "</button>&nbsp;" +
                                    "<button class='btn btn-danger btn-xs' onclick='delete_user(\"" + data.id + "\");'>" +
                                    "<span class='fas fa-user-slash' data-toggle='tooltip' data-placement='left' title='Delete User'></span>" +
                                    "</button>";
                            }
                            return html;
                        }
                    }
                ]
            });
            users_datatable.buttons().container().prependTo('#users_filter');
            users_datatable.buttons().container().addClass('float-left');
            $('.dt-buttons').addClass('btn-group-sm');
            $('.dt-buttons div').addClass('btn-group-sm');
            users_table.on('xhr.dt', function (e, settings, data) {
                users = {};
                $.each(data.data, function(i, v) {
                    users[v.id] = v;
                });
            });
        });
    </script>
<?php
include_once __DIR__ . '/../_footer.php';