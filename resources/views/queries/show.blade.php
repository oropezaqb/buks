@extends('layouts.app2')

@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">
                Company: {{ \Auth::user()->currentCompany->company->name }} (Add Queries)
            </div>
            <div class="card-body">
                <div id="wrapper">
                    <div id="page" class="container">
                        <div class="content">
                                <p>Query Title: {{ $query->title }}</p>
                                <p>Category: {{ $query->category }}</p>
                                <div class="form-group">
                                    <label for="explanation">Query: </label>
                                    <textarea id="explanation" name="explanation" class="form-control" rows="4" cols="50" required disabled>{{ $query->query }}</textarea>
                                </div>
                                <p>Ability: {{ $query->ability->name }}</p>
                            <div style="display:inline-block;"><button class="btn btn-primary" onclick="location.href = '/queries/{{ $query->id }}/edit';">Edit</button></div>
                            <div style="display:inline-block;"><form method="POST" action="/queries/{{ $query->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" type="submit">Delete</button>
                            </form></div>
                            <script>
                                function setValue(input) {
                                    var list = input.getAttribute('list');
                                    var options = document.querySelectorAll('#' + list + ' option');
                                    var hiddenInput = document.getElementById(input.getAttribute('id') + '-hidden');
                                    var hiddenInputName = document.getElementById('name-' + input.getAttribute('id') + '-hidden');
                                    var label = input.value;

                                    hiddenInputName.value = label;
                                    hiddenInput.value = label;

                                    options.forEach(function(option) {
                                        if (option.innerText === label) {
                                            hiddenInput.value = option.getAttribute('data-value');
                                        }
                                    });
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
