<?php

namespace App\Livewire;

use App\Models\CashCount;
use App\Models\CashCountItem;
use App\Models\CashierDutyLog;
use App\Models\Denomination;
use App\Models\Payment; // Import the new Payment model
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache; // Import Cache facade
use Livewire\Component;

class CashierModule extends Component
{
    public $showStartShiftModal = false;
    public $showEndShiftModal = false;
    public $showDailyReportModal = false;
    public $total_cash_out;
    public $shiftStarted = false;
    public $dailyReportData = [];

    public $denominations = [];
    public $cashCountQuantities = [];
    public $endShiftCashCountQuantities = [];

    public function mount()
    {
        $this->denominations = Cache::remember('denominations', 60*60, function () { // Cache for 1 hour
            return Denomination::orderBy('value', 'desc')->get();
        });
        foreach ($this->denominations as $denomination) {
            $this->cashCountQuantities[$denomination->id] = null;
            $this->endShiftCashCountQuantities[$denomination->id] = null;
        }
        $this->checkShiftStatus();
    }

    public function checkShiftStatus()
    {
        $this->shiftStarted = CashierDutyLog::where('user_id', Auth::id())
            ->where('cashier_duty_log_status_id', 1)
            ->exists();
    }

    public function openStartShiftModal()
    {
        $this->resetValidation();
        foreach ($this->denominations as $denomination) {
            $this->cashCountQuantities[$denomination->id] = null;
        }
        $this->showStartShiftModal = true;
    }

    public function gettotalCashInProperty()
    {
        $total = 0;
        foreach ($this->denominations as $denomination) {
            $quantity = (float)($this->cashCountQuantities[$denomination->id] ?? 0);
            $total += $quantity * $denomination->value;
        }
        return $total;
    }

    public function calculateTotalCashIn()
    {
        // This method is called by wire:change, but the actual calculation is done by the computed property
        // It forces a re-render to update the displayed total.
    }

    public function startShift()
    {
        $this->validate([
            'cashCountQuantities.*' => 'nullable|numeric|min:0',
        ]);


        // Create CashierDutyLog first
        $dutyLog = CashierDutyLog::create([
            'cashier_duty_log_status_id' => 1, // Assuming 1 is "Active"
            'user_id' => Auth::id(),
            'time_in' => Carbon::now(),
            'created_by' => Auth::id(),
        ]);

        // Then create CashCount, linking it to the CashierDutyLog
        $cashCount = CashCount::create([
            'cashier_duty_log_id' => $dutyLog->id, // Link to the new CashierDutyLog
            'count_type_id' => 1, // Assuming 1 is for 'Start Shift' cash count type
            'total_amount' => $this->totalCashIn,
        ]);

        // Finally, create CashCountItems
        foreach ($this->denominations as $denomination) {
            $quantity = (float)($this->cashCountQuantities[$denomination->id] ?? 0);
            if ($quantity > 0) {
                CashCountItem::create([
                    'cash_count_id' => $cashCount->id,
                    'denomination_id' => $denomination->id,
                    'quantity' => $quantity,
                    'sub_total' => $quantity * $denomination->value,
                ]);
            }
        }

        $this->showStartShiftModal = false;
        foreach ($this->denominations as $denomination) {
            $this->cashCountQuantities[$denomination->id] = 0;
        }
        $this->checkShiftStatus();

        $this->showStartShiftModal = false;
        foreach ($this->denominations as $denomination) {
            $this->cashCountQuantities[$denomination->id] = 0;
        }
        $this->checkShiftStatus();
    }

    public function gettotalCashOutComputedProperty()
    {
        $total = 0;
        foreach ($this->denominations as $denomination) {
            $quantity = (float)($this->endShiftCashCountQuantities[$denomination->id] ?? 0);
            $total += $quantity * $denomination->value;
        }
        return $total;
    }

    public function calculateTotalCashOut()
    {
        // This method is called by wire:change, but the actual calculation is done by the computed property
        // It forces a re-render to update the displayed total.
    }

    public function openEndShiftModal()
    {
        $this->resetValidation();
        foreach ($this->denominations as $denomination) {
            $this->endShiftCashCountQuantities[$denomination->id] = null;
        }
        $this->showEndShiftModal = true;
    }

    public function endShift()
    {
        $this->validate([
            'endShiftCashCountQuantities.*' => 'nullable|numeric|min:0',
        ]);

        if ($this->totalCashOutComputed <= 0) {
            $this->addError('endShiftCashCountQuantities', 'Total cash out must be greater than zero.');
            return;
        }

        $activeDutyLog = CashierDutyLog::where('user_id', Auth::id())
            ->where('cashier_duty_log_status_id', 1)
            ->first();

        if ($activeDutyLog) {
            // Create CashCount for end shift
            $endCashCount = CashCount::create([
                'cashier_duty_log_id' => $activeDutyLog->id,
                'count_type_id' => 2, // Assuming 2 is for 'End Shift' cash count type
                'total_amount' => $this->totalCashOutComputed,
            ]);

            // Create CashCountItems for end shift
            foreach ($this->denominations as $denomination) {
                $quantity = (float)($this->endShiftCashCountQuantities[$denomination->id] ?? 0);
                if ($quantity > 0) {
                    CashCountItem::create([
                        'cash_count_id' => $endCashCount->id,
                        'denomination_id' => $denomination->id,
                        'quantity' => $quantity,
                        'sub_total' => $quantity * $denomination->value,
                    ]);
                }
            }

            $transactions = Transaction::with('payments')->where('cashier_duty_log_id', $activeDutyLog->id)->get();
            $totalSalesInCash = $transactions->sum(function ($transaction) {
                return $transaction->payments->where('payment_method_id', 1)->sum('amount_received');
            });
            $startingCash = $activeDutyLog->cashCount ? $activeDutyLog->cashCount->total_amount : 0; // Use cashCount total
            $systemComputedCash = $startingCash + $totalSalesInCash;
            $discrepancy = $this->totalCashOutComputed - $systemComputedCash; // Use computed total for discrepancy

            $activeDutyLog->update([
                'cashier_duty_log_status_id' => 2, // Assuming 2 is "Inactive"
                'time_out' => Carbon::now(),
                'updated_by' => Auth::id(),
            ]);

            $this->prepareDailyReport($activeDutyLog, $transactions, $systemComputedCash, $discrepancy, $this->totalCashOutComputed);
            $this->showEndShiftModal = false;
            $this->showDailyReportModal = true;
            $this->checkShiftStatus();
        }
    }

    public function prepareDailyReport($dutyLog, $transactions, $systemComputedCash, $discrepancy, $actualEndingCash)
    {
        $salesByPaymentMethod = [];
        foreach ($transactions as $transaction) {
            foreach ($transaction->payments as $payment) {
                $methodId = $payment->payment_method_id;
                $amount = $payment->amount_received;

                if (!isset($salesByPaymentMethod[$methodId])) {
                    $salesByPaymentMethod[$methodId] = 0;
                }
                $salesByPaymentMethod[$methodId] += $amount;
            }
        }

        $salesByProduct = [];
        foreach ($transactions as $transaction) {
            foreach ($transaction->sales as $sale) {
                if (!isset($salesByProduct[$sale->product->product_name])) {
                    $salesByProduct[$sale->product->product_name] = ['quantity' => 0, 'total' => 0];
                }
                $salesByProduct[$sale->product->product_name]['quantity'] += $sale->quantity;
                $salesByProduct[$sale->product->product_name]['total'] += $sale->total;
            }
        }

        $this->dailyReportData = [
            'time_in' => $dutyLog->time_in,
            'time_out' => $dutyLog->time_out,
            'total_cash_in' => $dutyLog->cashCount->total_amount, // Use cashCount total
            'total_cash_out' => $actualEndingCash, // Use the actual ending cash from the end shift count
            'system_computed_cash' => $systemComputedCash,
            'discrepancy' => $discrepancy,
            'sales_by_payment_method' => $salesByPaymentMethod,
            'sales_by_product' => $salesByProduct,
            'payment_methods' => Cache::remember('paymentMethods', 60*60, function () { // Cache for 1 hour
                return PaymentMethod::all()->pluck('payment_method_name', 'id');
            }),
        ];
    }

    public function render()
    {
        return view('livewire.cashier-module');
    }
}
