@extends('layouts.app2')

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header font-weight-bold">Company: {{ \Auth::user()->currentCompany->company->name }} (Add a New Account Title)</div>
        <div class="card-body">
            <div id="wrapper">
                <div id="page" class="container">
                    <div class="content">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <form method="POST" action="/accounts">
                            @csrf
                            <div class="form-group">
                                <label for="number">Account Number:</label>
                                <input 
                                    class="form-control @error('number') is-danger @enderror" 
                                    type="text" 
                                    name="number" 
                                    id="number" 
                                    required
                                    value="{{ old('number') }}">
                                @error('number')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="title">Account Title:</label>
                                <input 
                                    class="form-control @error('title') is-danger @enderror" 
                                    type="text" 
                                    name="title" 
                                    id="title" 
                                    required
                                    value="{{ old('title') }}">
                                @error('title')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="type">Account Type:</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="110 - Cash and Cash Equivalents" {{ old('type') == '110 - Cash and Cash Equivalents' ? 'selected' : '' }}>110 - Cash and Cash Equivalents</option>
                                    <option value="120 - Non-Cash Current Asset" {{ old('type') == '120 - Non-Cash Current Asset' ? 'selected' : '' }}>120 - Non-Cash Current Asset</option>
                                    <option value="150 - Non-Current Asset" {{ old('type') == '150 - Non-Current Asset' ? 'selected' : '' }}>150 - Non-Current Asset</option>
                                    <option value="210 - Current Liabilities" {{ old('type') == '210 - Current Liabilities' ? 'selected' : '' }}>210 - Current Liabilities</option>
                                    <option value="250 - Non-Current Liabilities" {{ old('type') == '250 - Non-Current Liabilities' ? 'selected' : '' }}>250 - Non-Current Liabilities</option>
                                    <option value="310 - Capital" {{ old('type') == '310 - Capital' ? 'selected' : '' }}>310 - Capital</option>
                                    <option value="320 - Share Premium" {{ old('type') == '320 - Share Premium' ? 'selected' : '' }}>320 - Share Premium</option>
                                    <option value="330 - Retained Earnings" {{ old('type') == '330 - Retained Earnings' ? 'selected' : '' }}>330 - Retained Earnings</option>
                                    <option value="340 - Other Comprehensive Income" {{ old('type') == '340 - Other Comprehensive Income' ? 'selected' : '' }}>340 - Other Comprehensive Income</option>
                                    <option value="350 - Drawing" {{ old('type') == '350 - Drawing' ? 'selected' : '' }}>350 - Drawing</option>
                                    <option value="390 - Income Summary" {{ old('type') == '390 - Income Summary' ? 'selected' : '' }}>390 - Income Summary</option>
                                    <option value="410 - Revenue" {{ old('type') == '410 - Revenue' ? 'selected' : '' }}>410 - Revenue</option>
                                    <option value="420 - Other Income" {{ old('type') == '420 - Other Income' ? 'selected' : '' }}>420 - Other Income</option>
                                    <option value="510 - Cost of Goods Sold" {{ old('type') == '510 - Cost of Goods Sold' ? 'selected' : '' }}>510 - Cost of Goods Sold</option>
                                    <option value="520 - Operating Expense" {{ old('type') == '520 - Operating Expense' ? 'selected' : '' }}>520 - Operating Expense</option>
                                    <option value="590 - Income Tax Expense" {{ old('type') == '590 - Income Tax Expense' ? 'selected' : '' }}>590 - Income Tax Expense</option>
                                    <option value="600 - Other Accounts" {{ old('type') == '600 - Other Accounts' ? 'selected' : '' }}>600 - Other Accounts</option>
                                </select>
                                @error('type')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="line_item_id">Line Item:</label>
                                <input list="line_item_ids" id="line_item_id0" onchange="setValue(this)" class="custom-select @error('line_item_id') is-danger @enderror" required value="{{ old('line_item_name') }}">
                                <datalist id="line_item_ids">
                                    @foreach ($lineItems as $lineItem)
                                        <option data-value="{{ $lineItem->id }}">{{ $lineItem->name }}</option>
                                    @endforeach
                                </datalist>
                                <input type="hidden" name="line_item_id" id="line_item_id0-hidden" value="{{ old('line_item_id') }}">
                                <input type="hidden" name="line_item_name" id="name-line_item_id0-hidden" value="{{ old('line_item_name') }}">
                            </div>
                            <div class="form-group">
                                <input type="checkbox" name="subsidiary_ledger" id="subsidiary_ledger" {{ old('subsidiary_ledger') ? 'checked' : '' }}>
                                <label for="subsidiary_ledger">Subsidiary Ledger</label>
                            </div>
                            <button class="btn btn-primary" type="submit">Add</button>
                        </form>

                        <script>
                            function setValue(id) {
                                var input = id,
                                    list = input.getAttribute('list'),
                                    options = document.querySelectorAll('#' + list + ' option'),
                                    hiddenInput = document.getElementById(input.getAttribute('id') + '-hidden'),
                                    hiddenInputName = document.getElementById('name-' + input.getAttribute('id') + '-hidden'),
                                    label = input.value;

                                hiddenInputName.value = label;
                                hiddenInput.value = label;

                                for (var i = 0; i < options.length; i++) {
                                    var option = options[i];
                                    if (option.innerText === label) {
                                        hiddenInput.value = option.getAttribute('data-value');
                                        break;
                                    }
                                }
                            }
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
