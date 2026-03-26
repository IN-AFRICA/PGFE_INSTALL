<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountPlan;
use App\Models\Currency;
use App\Models\Fee;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\SubAccountPlan;
use App\Services\Accounts\AccountingDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class AccountingWebController extends Controller
{
    private const SECTIONS = [
        'dashboard',
        'account-plans',
        'sub-account-plans',
        'fees',
        'currencies',
        'payments',
        'journal',
        'reports',
    ];

    public function index(Request $request, AccountingDashboardService $dashboardService): View
    {
        $section = (string) $request->query('section', 'dashboard');
        if (! in_array($section, self::SECTIONS, true)) {
            $section = 'dashboard';
        }

        $data = ['section' => $section];
        $filters = array_filter([
            'school_id' => session('selected_school_id'),
        ]);

        if ($section === 'dashboard') {
            $data['stats'] = $dashboardService->getAccountingStats($filters);
            $data['journalCount'] = Journal::query()->count();
        } elseif ($section === 'account-plans') {
            $data['accountPlans'] = AccountPlan::query()
                ->with(['classComptability', 'categoryComptability'])
                ->latest('id')
                ->paginate(15);
        } elseif ($section === 'sub-account-plans') {
            $data['subAccounts'] = SubAccountPlan::query()
                ->with('accountPlan')
                ->latest('id')
                ->paginate(15);
        } elseif ($section === 'fees') {
            $data['fees'] = Fee::query()
                ->with(['currency', 'feeType', 'school'])
                ->latest('id')
                ->paginate(15);
        } elseif ($section === 'currencies') {
            $data['currencies'] = Currency::query()
                ->with('activeExchangeRate')
                ->latest('id')
                ->paginate(15);
        } elseif ($section === 'journal') {
            $data['journals'] = Journal::query()
                ->with(['accountPlan', 'account'])
                ->latest('date')
                ->latest('id')
                ->paginate(20);
        } elseif ($section === 'payments') {
            $data['payments'] = Payment::query()
                ->with(['student', 'fee.feeType', 'currency'])
                ->latest('id')
                ->paginate(15);
        }

        return view('backend.pages.accounting.index', $data);
    }

    public function create()
    {
        return view('admin.accounting.create');
    }

    public function store(Request $request)
    { /* ... */
    }

    public function show($id)
    {
        return view('admin.accounting.show');
    }

    public function edit($id)
    {
        return view('admin.accounting.edit');
    }

    public function update(Request $request, $id)
    { /* ... */
    }

    public function destroy($id)
    { /* ... */
    }
}
