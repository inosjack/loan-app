<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\LoanInstalment;
use App\Models\LoanTerm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanManageController extends Controller
{
    //
    /**
     * get all loan terms
     * @return LoanTerm[]|\Illuminate\Database\Eloquent\Collection
     */
    public function terms()
    {
        return LoanTerm::all();
    }

    public function create(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'loan_term' => 'required'
        ]);

        $loanTerm = LoanTerm::where('name', '=', $request->get('loan_term'))->first();

        if(!$loanTerm) {
            return response()->json([
                'message' => 'The selected loan term is invalid.'
            ],422);
        }

        $loanDetails = DB::transaction(function () use($request, $loanTerm) {
           $frequency = ['week' => 52, 'month' => 12, 'year' => 1];
           $loan = new Loan();
           $loan->user_id = $request->user()->id;
           $loan->loan_term_id = $loanTerm->id;
           $loan->amount = $request->get('amount');
           $loan->save();
           $count = 0;

           $installments = [];
           while($count < $loanTerm->tenure) {
            $principal_amount = floatval($loan->amount) - (floatval($loan->amount)/$loanTerm->tenure * $count);
            $count++;
            $balance_amount = $principal_amount - floatval($loan->amount)/$loanTerm->tenure;
            $emi = floatval($loan->amount)/$loanTerm->tenure
            + ($principal_amount * floatval($loanTerm->interest_rate)*$count)/(100*$frequency[$loanTerm->frequency]);
            $installments[] = LoanInstallment::create([
                'loan_id' => $loan->id,
                'user_id' => $loan->user_id,
                'loan_term_id' => $loanTerm->id,
                'principal_amount' => round($principal_amount,2),
                'emi' =>  round($emi,2),
                'balance_amount' =>  round($balance_amount,2),
            ]);
           }
           return ['loan' => $loan,'installments' => $installments];
        }, 4);
        return response()->json([
            'message' => 'Loan approved',
            'loanDetails' => $loanDetails
        ]);
    }

    public function repayment(Request $request)
    {
        $data = $request->validate([
            'loan_id' => 'required',
            'amount' => 'required|numeric|gt:0'
        ]);

        $loanInstallments = LoanInstallment::where('loan_id',$data['loan_id'])
                ->where('user_id', $request->user()->id)
                ->orderBy('id')
                ->get();

        if($loanInstallments->count() === 0) {
            return response()->json([
                'error' => 'The loan details is invalid.'
            ],422);
        }

        if($loanInstallments->whereNull('payment_at')->count() == 0) {
            return response()->json([
                'error' => 'Your loan already cleared.'
            ]);
        }

        if($loanInstallments->whereNull('payment_at')->first()->emi != round($data['amount'],2)) {
            return response()->json([
                'error' => 'Your loan amount is invalid.'
            ],422);
        }

        $loanInstallments->whereNull('payment_at')->first()->update(['payment_at' => Carbon::now()]);

        return response()->json(
            ['message' => 'Your repayment is successful']
        );
    }
}
