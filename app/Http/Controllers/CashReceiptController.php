<?php

namespace App\Http\Controllers;

use App\Models\CashReceipt;
use App\Models\Account;
use App\Models\SubsidiaryLedger;
use App\Models\CashReceiptLine;
use Illuminate\Http\Request;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ShortVariableName)
     */

class CashReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = \Auth::user()->currentCompany->company;

        $cashReceipts = CashReceipt::where('company_id', $company->id)->latest()->get();

        return view('cash_receipts.index', compact('cashReceipts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $company = \Auth::user()->currentCompany->company;

        $accounts = Account::where('company_id', $company->id)->latest()->get();

        $subsidiaryLedgers = SubsidiaryLedger::where('company_id', $company->id)->latest()->get();

        return view('cash_receipts.create', compact('accounts', 'subsidiaryLedgers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'date' => 'required|date',
            'doc_number' => 'required|numeric',
            'memo' => 'nullable|string',
            'item_lines' => 'required|array',
            "item_lines.'subsidiary_ledger_id'.*" => 'required|exists:subsidiary_ledgers,id',
            "item_lines.'account_id'.*" => 'required|exists:accounts,id',
            "item_lines.'description'.*" => 'nullable|string',
            "item_lines.'amount'.*" => 'required|numeric',
            "item_lines.'output_tax'.*" => 'nullable|numeric'
        ]);
//        try {
            \DB::transaction(function () use ($request, $validated) {
                $company = \Auth::user()->currentCompany->company;
                $cashReceipt = CashReceipt::create([
                    'company_id' => $company->id,
                    'account_id' => $validated['account_id'],
                    'date' => $validated['date'],
                    'number' => $validated['doc_number'],
                    'memo' => $validated['memo'] ?? null,
                ]);
    
                // Create cash receipt lines

                if (!is_null(request("item_lines.'subsidiary_ledger_id'"))) {
                    $count = count(request("item_lines.'subsidiary_ledger_id'"));
                    for ($row = 0; $row < $count; $row++) {
                        $outputTax = 0;
                        if (!is_null(request("item_lines.'output_tax'.".$row))) {
                            $outputTax = request("item_lines.'output_tax'.".$row);
                        }
                        $itemLine = new CashReceiptLine([
                            'cash_receipt_id' => $cashReceipt->id,
                            'subsidiary_ledger_id' => request("item_lines.'subsidiary_ledger_id'.".$row),
                            'account_id' => request("item_lines.'account_id'.".$row),
                            'description' => request("item_lines.'description'.".$row),
                            'amount' => request("item_lines.'amount'.".$row),
                            'output_tax' => $outputTax
                        ]);
                        $itemLine->save();
                    }
                }
/*
                foreach ($request["item_lines"] as $line) {
                    //dd($request["item_lines"]);
                    CashReceiptLine::create([
                        'cash_receipt_id' => $cashReceipt->id,
                        'subsidiary_ledger_id' => $line["'subsidiary_ledger_id'"],
                        'account_id' => $line['account_id'],
                        'description' => $line['description'] ?? null,
                        'amount' => $line['amount'],
                        'output_tax' => $line['output_tax'] ?? 0,
                    ]);
                }
*/
            });
            return redirect(route('cash_receipts.index'));
//        } catch (\Exception $e) {
//            return back()->with('status', $this->translateError($e))->withInput();
//       }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CashReceipt  $cashReceipt
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CashReceipt  $cashReceipt
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CashReceipt  $cashReceipt
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CashReceipt  $cashReceipt
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        //
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
                    array ('Bill is already recorded.', 'bill_number'));
                    if (isset($indexes[$m[1]])) {
                        $this->err_flds = array($indexes[$m[1]][1] => 1);
                        return $indexes[$m[1]][0];
                    }
                }
                break;
        }
        return $e->getMessage();
    }
}
