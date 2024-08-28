@extends('layouts.app2')
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Add Company Roles)</div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div id="content">
                            <form method="POST" action="/roles" id="rolesForm">
                                @csrf
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
                                        value="{{ old('name') }}">
                                    @error('name')
                                        <p class="help is-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <p>Abilities:</p>
                                <div id="jqxgrid"></div>
                                <input type="hidden" name="abilitiesInput" id="abilitiesInput">
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
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Convert PHP data to JSON format for JavaScript
                const abilities = @json($abilities);

                // Prepare data source
                const dataSource = new $.jqx.dataAdapter({
                    localdata: abilities.map(ability => ({
                        id: ability.id,
                        name: ability.name,
                        checked: false // Default unchecked, adjust as needed
                    })),
                    datatype: "array",
                    datafields: [
                        { name: 'id', type: 'number' },
                        { name: 'name', type: 'string' },
                        { name: 'checked', type: 'bool' }
                    ]
                });

                // Initialize jqxGrid
                $("#jqxgrid").jqxGrid({
                    width: '100%',
                    source: dataSource,
                    columns: [
                        { text: 'Ability Name', datafield: 'name', width: 250 },
                        { text: 'Included', datafield: 'checked', columntype: 'checkbox', width: 100 }
                    ],
                    editable: true,
                    selectionmode: 'singlerow'
                });

        $('#jqxgrid').on('cellvaluechanged', function (event) {
            var selectedRows = [];
            var rows = $('#jqxgrid').jqxGrid('getrows');

            rows.forEach(function (row) {
                if (row.checked) {
                    selectedRows.push(row.id);
                }
            });

            $('#abilitiesInput').val(selectedRows.join(','));
        });

            });
        </script>
@endsection
