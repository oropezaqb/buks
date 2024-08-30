<?php

namespace App\Http\Controllers;

use App\Models\Query;
use Illuminate\Http\Request;
use PDO;
use App\Models\Ability;
use Illuminate\Support\Facades\Validator;
use App\EPMADD\DbAccess;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.ShortVariableName)
     */

class QueryController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth');
//        $this->middleware('company');
//        $this->middleware('web');
    }
    public function index()
    {
        $company = \Auth::user()->currentCompany->company;
        if (empty(request('title'))) {
            $queries = Query::where('company_id', $company->id)->latest()->get();
        } else {
            $queries = Query::where('company_id', $company->id)
                ->where('title', 'like', '%' . request('title') . '%')->get();
        }
        //if (\Route::currentRouteName() === 'report_line_items.index')
        //{
        //    \Request::flash();
        //}
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

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $company = \Auth::user()->currentCompany->company;
        $query = new Query([
            'company_id' => $company->id,
            'title' => request('title'),
            'category' => request('category'),
            'query' => request('query'),
            'ability_id' => request('ability_id'),
        ]);
        $query->save();
        return redirect(route('queries.index'));
    }
    public function edit(Query $query)
    {
        if (\Route::currentRouteName() === 'queries.edit') {
            \Request::flash();
        }
        $abilities = Ability::latest()->get();
        return view('queries.edit', compact('query', 'abilities'));
    }
    public function update(Request $request, $id)
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
        } else {
            $db = new DbAccess();
            $stmt = $db->query($query->query);
            $ncols = $stmt->columnCount();
            $headings = array();
            for ($i = 0; $i < $ncols; $i++) {
                $meta = $stmt->getColumnMeta($i);
                $headings[] = $meta['name'];
            }
            return view('queries.run', compact('query', 'stmt', 'headings'));
        }
    }
}
