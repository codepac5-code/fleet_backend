<?php
namespace App\Http\Core\Classes;
use Exception;
class OperationTracker {

    private $totalIncome; 
    private $pendingAmount; 
    private $withdrawnAmount; 
    private $availableAmount; 
    private $history;

    public function __construct($totalIncome = 0, $pendingAmount = 0, $withdrawnAmount = 0, $availableAmount = 0)
    {
        $this->totalIncome      = $totalIncome;
        $this->pendingAmount    = $pendingAmount;
        $this->withdrawnAmount  = $withdrawnAmount;
        $this->availableAmount  = $availableAmount;
        $this->history = []; 
    }

    public function addIncome($amount)
    {
        $this->totalIncome += $amount;
        $this->availableAmount += $amount; 
        $this->logStats('addIncome', $amount);
    }

    public function addPendingAmount($amount)
    {
        $this->pendingAmount += $amount;
        $this->logStats('addPendingAmount', $amount);
    }

    public function withdrawAmount($amount)
    {
        if ($amount <= $this->availableAmount) {
            $this->availableAmount -= $amount;
            $this->withdrawnAmount += $amount;
            $this->logStats('withdrawAmount', $amount);
        }
        else {
            throw new Exception("The amount to withdraw exceeds the available amount.");
        }
    }
    
    public function calculateProfit($percentage)
    {
        $profit = $this->totalIncome * ($percentage / 100);
        $this->logStats('calculateProfit', $profit);
        return $profit;
    }

    private function logStats($action, $amount)
    {
        $date = date('Y-m-d H:i:s');
        $this->history[] = [
            'date' => $date,
            'action' => $action,
            'amount' => $amount
        ];
    }

    public function getStats()
    {
        return [
            'totalIncome' => $this->totalIncome,
            'pendingAmount' => $this->pendingAmount,
            'withdrawnAmount' => $this->withdrawnAmount,
            'availableAmount' => $this->availableAmount,
        ];
    }

    public function getHistory()
    {
        return $this->history;
    }
}


