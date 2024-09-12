<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::all();
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|unique:employees',
        'phone_number' => 'required|string|max:20',
        'job_title' => 'required|string|max:255',
        'hire_date' => 'required|date',
        'salary' => 'required|numeric',
        ]);

        Employee::create($request->all());
        return redirect()->route('employees.index');
    }
}
