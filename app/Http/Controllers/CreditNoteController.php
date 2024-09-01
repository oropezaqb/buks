<?php

namespace App\Http\Controllers;

use App\Models\CreditNote;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use JavaScript;
use App\Models\CreditNoteLine;
use App\Http\Requests\StoreCreditNote;
use App\Jobs\CreateCreditNote;
use App\Models\Invoice;
use App\Jobs\CreateInvoice;
use App\Jobs\UpdateSales;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     * @SuppressWarnings(PHPMD.ShortVariableName)
     */

class CreditNoteController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth');
//        $this->middleware('company');
//        $this->middleware('web');
//        $this->middleware('outputVat');
    }
    public function index()
    {
        $company = \Auth::user()->currentCompany->company;
        if (empty(request('customer_name'))) {
            $creditNotes = CreditNote::where('company_id', $company->id)->latest()->get();
        } else {
            $customer = Customer::where('name', request('customer_name'))->firstOrFail();
            $creditNotes = CreditNote::where('company_id', $company->id)
                ->where('customer_id', $customer->id)->latest()->get();
        }
        if (\Route::currentRouteName() === 'credit_notes.index') {
            \Request::flash();
        }
        return view('credit_notes.index', compact('creditNotes'));
    }
    public function show(CreditNote $creditNote)
    {
        $company = \Auth::user()->currentCompany->company;
        $customers = Customer::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'credit_notes.show',
            compact('creditNote', 'customers', 'products')
        );
    }
    public function create()
    {
        $company = \Auth::user()->currentCompany->company;
        $customers = Customer::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'credit_notes.create',
            compact('customers', 'products')
        );
    }
    public function store(StoreCreditNote $request)
    {
        try {
            \DB::transaction(function () use ($request) {
                $company = \Auth::user()->currentCompany->company;
                $creditNote = new CreditNote([
                    'company_id' => $company->id,
                    'invoice_id' => request('invoice_id'),
                    'date' => request('date'),
                    'number' => request('number'),
                ]);
                $creditNote->save();
                $createCreditNote = new CreateCreditNote();
                $createCreditNote->updateLines($creditNote);
                $createCreditNote->recordTransaction($creditNote);
//                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)
//                    ->where('date', '>=', request('date'))->orderBy('date', 'asc')->get();
                $updateSales = new UpdateSales();
                $updateSales->updateSales(request('date'));
            });
            return redirect(route('credit_notes.index'));
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
    }
    public function translateError($e)
    {
        switch ($e->getCode()) {
            case '23000':
                if (preg_match(
                    "/for key '(.*)'/",
                    $e->getMessage(),
                    $m
                )) {
                    $indexes = array(
                      'my_unique_ref' =>
                    array ('Credit note is already recorded.', 'number'));
                    if (isset($indexes[$m[1]])) {
                        $this->err_flds = array($indexes[$m[1]][1] => 1);
                        return $indexes[$m[1]][0];
                    }
                }
                break;
        }
        return $e->getMessage();
    }

    public function edit(CreditNote $creditNote)
    {
        $company = \Auth::user()->currentCompany->company;
        $customers = Customer::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'credit_notes.edit',
            compact('creditNote', 'customers', 'products')
        );
    }
    public function update(StoreCreditNote $request, CreditNote $creditNote)
    {
//        try {
            \DB::transaction(function () use ($request, $creditNote) {
                $company = \Auth::user()->currentCompany->company;
                $oldDate = $creditNote->date;
                $newDate = request('date');
                $creditNote->update([
                    'company_id' => $company->id,
                    'invoice_id' => request('invoice_id'),
                    'date' => request('date'),
                    'number' => request('number'),
                ]);
                $creditNote->save();
                $changeDate = $newDate;
                if ($oldDate < $newDate) {
                    $changeDate = $oldDate;
                }
                $createCreditNote = new CreateCreditNote();
                $createCreditNote->deleteCreditNote($creditNote);
                $createCreditNote->recordTransaction($creditNote);
                $createCreditNote->updateLines($creditNote);
//                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)
//                    ->where('date', '>=', $changeDate)->orderBy('date', 'asc')->get();
                $updateSales = new UpdateSales();
                $updateSales->updateSales($changeDate);
            });
            return redirect(route('credit_notes.show', [$creditNote]))
                ->with('status', 'Credit note updated!');
//        } catch (\Exception $e) {
//            return back()->with('status', $this->translateError($e))->withInput();
//        }
    }
    public function destroy(CreditNote $creditNote)
    {
        try {
            \DB::transaction(function () use ($creditNote) {
                $creditNoteDate = $creditNote->date;
                $creditNote->delete();
//                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)
//                    ->where('date', '>=', $creditNoteDate)->orderBy('date', 'asc')->get();
                $updateSales = new UpdateSales();
                $updateSales->updateSales($creditNoteDate);
            });
            return redirect(route('credit_notes.index'));
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
    }
}
