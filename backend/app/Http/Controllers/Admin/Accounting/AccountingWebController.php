<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountingWebController extends Controller
{
    public function index(Request $request) 
    { 
        $section = $request->query('section', 'dashboard');
        $data = [];

        if ($section === 'account-plans') {
            $data['accountPlans'] = \App\Models\AccountPlan::with(['classComptability', 'categoryComptability'])->paginate(15);
        } elseif ($section === 'sub-account-plans') {
            $data['subAccounts'] = \App\Models\SubAccountPlan::with('accountPlan')->paginate(15);
        } elseif ($section === 'fees') {
            $data['fees'] = \App\Models\Fee::paginate(15);
        } elseif ($section === 'journal') {
            $data['journals'] = \App\Models\Journal::with(['accountPlan', 'account'])->latest()->paginate(20);
        } elseif ($section === 'payments') {
            $data['payments'] = \App\Models\Payment::with(['student', 'fee'])->latest()->paginate(15);
        }

        return view('backend.pages.accounting.index', array_merge(['section' => $section], $data)); 
    }
    public function create() { return view('admin.accounting.create'); }
    public function store(Request $request) { /* ... */ }
    public function show($id) { return view('admin.accounting.show'); }
    public function edit($id) { return view('admin.accounting.edit'); }
    public function update(Request $request, $id) { /* ... */ }
    public function destroy($id) { /* ... */ }
}
