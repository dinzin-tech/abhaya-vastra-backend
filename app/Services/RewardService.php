<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\RewardSetting;
use Illuminate\Support\Facades\DB;

class RewardService
{
    protected $userId;
    protected $user;
    protected $wallet;

    public function __construct($userId)
    {
        $this->userId = $userId;
        $this->user = User::findOrFail($userId);
        $this->wallet = $this->getOrCreateWallet();
    }

    /**
     * Get or create wallet for user
     */
    protected function getOrCreateWallet()
    {
        return Wallet::firstOrCreate(
            ['user_id' => $this->userId],
            ['balance' => 0]
        );
    }

    /**
     * Calculate reward points based on order amount
     */
    public function calculateRewardPoints($orderAmount)
    {
        $settings = RewardSetting::where('status', 1)->first();

        if (!$settings) {
            return 0;
        }

        // Check if order meets minimum requirement
        if ($orderAmount < $settings->min_order_value) {
            return 0;
        }

        // Calculate points: (order_amount / base_amount) * points_per_base
        $points = floor(($orderAmount / $settings->reward_base_amount) * $settings->reward_points);

        return $points;
    }

    /**
     * Create a pending credit transaction for order rewards
     */
    public function createPendingCredit($orderAmount, $reference, $description)
    {
        $points = $this->calculateRewardPoints($orderAmount);

        if ($points <= 0) {
            return null;
        }

        $transaction = WalletTransaction::create([
            'wallet_id' => $this->wallet->id,
            'type' => 'credit',
            'points' => $points,
            'status' => 'pending',
            'description' => $description,
            'reference' => $reference,
        ]);

        return $transaction;
    }

    /**
     * Approve a pending transaction and credit wallet
     */
    public function approveTransaction(WalletTransaction $transaction)
    {
        if ($transaction->status !== 'pending' || $transaction->type !== 'credit') {
            throw new \Exception('Only pending credit transactions can be approved.');
        }

        DB::beginTransaction();
        try {
            // Update wallet balance
            $this->wallet->increment('balance', $transaction->points);

            // Update transaction status
            $transaction->update(['status' => 'completed']);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reverse a pending transaction
     */
    public function reverseTransaction(WalletTransaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            throw new \Exception('Only pending transactions can be reversed.');
        }

        $transaction->update(['status' => 'reversed']);
        return true;
    }

    /**
     * Deduct points from wallet (for checkout usage)
     */
    public function deductPoints($points, $reference, $description)
    {
        if ($this->wallet->balance < $points) {
            throw new \Exception('Insufficient loyalty points balance.');
        }

        DB::beginTransaction();
        try {
            // Deduct from wallet
            $this->wallet->decrement('balance', $points);

            // Create debit transaction
            $transaction = WalletTransaction::create([
                'wallet_id' => $this->wallet->id,
                'type' => 'debit',
                'points' => $points,
                'status' => 'completed',
                'description' => $description,
                'reference' => $reference,
            ]);

            DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Add points to wallet (for purchasing loyalty points)
     */
    public function addPoints($points, $reference, $description)
    {
        DB::beginTransaction();
        try {
            // Add to wallet
            $this->wallet->increment('balance', $points);

            // Create credit transaction
            $transaction = WalletTransaction::create([
                'wallet_id' => $this->wallet->id,
                'type' => 'credit',
                'points' => $points,
                'status' => 'completed',
                'description' => $description,
                'reference' => $reference,
            ]);

            DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get wallet balance
     */
    public function getBalance()
    {
        return $this->wallet->balance;
    }

    /**
     * Convert points to rupees based on settings
     */
    public static function pointsToRupees($points)
    {
        $settings = RewardSetting::where('status', 1)->first();
        
        if (!$settings || $settings->points_value <= 0) {
            return 0;
        }

        return $points * $settings->points_value;
    }

    /**
     * Convert rupees to points based on settings
     */
    public static function rupeesToPoints($amount)
    {
        $settings = RewardSetting::where('status', 1)->first();
        
        if (!$settings || $settings->points_value <= 0) {
            return 0;
        }

        return floor($amount / $settings->points_value);
    }
}
