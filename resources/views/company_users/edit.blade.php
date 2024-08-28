@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Edit Company User Details)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div id="content">
                            <form method="POST" action="/company_users/{{ $user->id }}" id="rolesForm">
                                @csrf
                                @method('PUT')
                                @if (!empty($message))
                                    <p>{{ $message }}</p>
                                @endif
                                <p>User Name: {!! $user->name !!}</p>
                                <p>Roles:</p>
                                <input type="hidden" id="id" name="id" value="{{ $user->id }}">
                                <div id="rolesGrid"></div>
                                <input type="hidden" name="rolesInput" id="rolesInput">
                                <br>
                                <button class="btn btn-primary" type="submit">Save</button>
                                @error('')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            var roles = @json($roles);
            var checkedRoles = @json($checkedRoles);

            // Create a dictionary for quick lookup
            var checkedRolesDict = checkedRoles.reduce((dict, role) => {
                dict[role.id] = true;
                return dict;
            }, {});

            // Prepare data for jqxGrid
            var data = roles.map(function(role) {
                return {
                    id: role.id,
                    name: role.name,
                    checked: checkedRolesDict[role.id] === true // Lookup in dictionary
                };
            });

            // Initialize jqxGrid
            $("#rolesGrid").jqxGrid({
                width: '100%',
                autoheight: true,
                source: new $.jqx.dataAdapter({
                    localdata: data,
                    datatype: "array"
                }),
                columns: [
                    { text: 'Select', datafield: 'checked', columntype: 'checkbox', width: 50, editable: true },
                    { text: 'Role', datafield: 'name', width: 250 }
                ],
                editable: true
            });

            // Update hidden input with selected roles on form submission
            $("#rolesForm").submit(function (e) {
                var selectedRoles = [];
                var rows = $("#rolesGrid").jqxGrid('getrows');
                for (var i = 0; i < rows.length; i++) {
                    if (rows[i].checked) {
                        selectedRoles.push(rows[i].id);
                    }
                }
                $("#rolesInput").val(selectedRoles.join(","));
            });
        });
    </script>
@endsection
