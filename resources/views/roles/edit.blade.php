@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Edit Role Details)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div id="content">
                            <form method="POST" action="/roles/{{ $role->id }}" id="rolesForm">
                                @csrf
                                @method('PUT')
                                @if (!empty($message))
                                    <p>{{ $message }}</p>
                                @endif
                                <div class="form-group">
                                    <label for="name">Role Name: </label>
                                    <input
                                        class="form-control @error('name') is-danger @enderror"
                                        type="text"
                                        name="name"
                                        id="name" required
                                        value="{{ $role->name }}">
                                    @error('name')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <p>Abilities:</p>
                                <div id="jqxgrid"></div>
                                <input type="hidden" name="abilitiesInput" id="abilitiesInput">
                                <br>
                                <button class="btn btn-primary" type="submit">Save</button>
                                @error('name')
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
            var abilities = @json($abilities);
            var checkedAbilities = @json($checkedAbilities);

            // Create a dictionary for quick lookup
            var checkedAbilitiesDict = checkedAbilities.reduce((dict, ability) => {
                dict[ability.id] = true;
                return dict;
            }, {});

            // Prepare data for jqxGrid
            var data = abilities.map(function(ability) {
                return {
                    id: ability.id,
                    name: ability.name,
                    checked: checkedAbilitiesDict[ability.id] === true // Lookup in dictionary
                };
            });

            // Initialize jqxGrid
            $("#jqxgrid").jqxGrid({
                width: '100%',
                autoheight: true,
                source: new $.jqx.dataAdapter({
                    localdata: data,
                    datatype: "array"
                }),
                columns: [
                    { text: 'Select', datafield: 'checked', columntype: 'checkbox', width: 50, editable: true },
                    { text: 'Ability', datafield: 'name', width: 250 }
                ],
                editable: true
            });

            // Update hidden input with selected roles on form submission
            $("#rolesForm").submit(function (e) {
                var selectedRows = [];
                var rows = $("#jqxgrid").jqxGrid('getrows');
                for (var i = 0; i < rows.length; i++) {
                    if (rows[i].checked) {
                        selectedRows.push(rows[i].id);
                    }
                }
                $("#abilitiesInput").val(selectedRows.join(","));
            });
        });
    </script>
@endsection
