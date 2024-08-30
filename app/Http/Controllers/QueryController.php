<?php

namespace App\Http\Controllers;

use App\Models\Query;
use Illuminate\Http\Request;
use App\Models\Ability;
use Illuminate\Support\Facades\Validator;
use App\EPMADD\DbAccess;

class QueryController extends Controller
{
    public function index()
    {
        $company = \Auth::user()->currentCompany->company;
        $queries = Query::where('company_id', $company->id)
            ->when(request('title'), function ($query) {
                return $query->where('title', 'like', '%' . request('title') . '%');
            })
            ->latest()->get();
        return view('queries.index', compact('queries'));
    }

    public function create()
    {
        $abilities = Ability::latest()->get();
        return view('queries.create', compact('abilities'));
    }

    public function show(Query $query)
    {
        return view('queries.show', compact('query'));
    }

    public function store(Request $request)
    {
        $validator = $this->validateQuery($request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $company = \Auth::user()->currentCompany->company;
        Query::create([
            'company_id' => $company->id,
            'title' => request('title'),
            'category' => request('category'),
            'query' => request('query'),
            'ability_id' => request('ability_id'),
        ]);
        return redirect(route('queries.index'));
    }

    public function edit(Query $query)
    {
        \Request::flash();
        $abilities = Ability::latest()->get();
        return view('queries.edit', compact('query', 'abilities'));
    }

    public function update(Request $request, $id)
    {
        $validator = $this->validateQuery($request);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $company = \Auth::user()->currentCompany->company;
        $query = Query::findOrFail($id);
        $query->update([
            'company_id' => $company->id,
            'title' => request('title'),
            'category' => request('category'),
            'query' => request('query'),
            'ability_id' => request('ability_id')
        ]);
        return redirect($query->path());
    }

    public function destroy(Query $query)
    {
        $query->delete();
        return redirect(route('queries.index'));
    }

    public function run(Query $query)
    {
        if (stripos($query->query, 'file ') === 0) {
            return redirect(route('queries.index'))->with('status', 'Cannot run file reports here.');
        }

        $db = new DbAccess();
        $stmt = $db->query($query->query);
        $headings = [];
        for ($i = 0; $i < $stmt->columnCount(); $i++) {
            $meta = $stmt->getColumnMeta($i);
            $headings[] = $meta['name'];
        }
        return view('queries.run', compact('query', 'stmt', 'headings'));
    }

    private function validateQuery($request)
    {
        $messages = [
            'ability_id.exists' => 'The selected ability is invalid. Please choose among the recommended items.',
        ];

        $validator = Validator::make($request->all(), [
            'title' => ['required'],
            'category' => ['required'],
            'query' => ['required'],
            'ability_id' => ['required', 'exists:App\Models\Ability,id'],
        ], $messages);

        $validator->after(function ($validator) {
            if (stripos(request('query'), 'select ') !== 0 && stripos(request('query'), 'file ') !== 0) {
                $validator->errors()->add('query', 'Only select or file queries are allowed.');
            }
        });

        return $validator;
    }
}
