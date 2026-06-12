<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Get 10 users for testing, or create them if none exist
        $users = User::factory()->count(10)->create();

        foreach ($users as $user) {
            // Create wallet with random balance
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => rand(50, 500), // initial balance
            ]);

            // Add some transactions
            $transactionCount = rand(3, 7);
            for ($i = 0; $i < $transactionCount; $i++) {
                $type = rand(0, 1) ? 'credit' : 'debit';
                $points = rand(10, 100);
                $status = ['pending', 'completed', 'reversed'][rand(0, 2)];

                // Ensure debit doesn't exceed balance
                if ($type === 'debit' && $points > $wallet->balance) {
                    $type = 'credit';
                }

                if ($type === 'credit') {
                    $wallet->increment('balance', $points);
                } else {
                    $wallet->decrement('balance', $points);
                }

                WalletTransaction::create([
                    'wallet_id'   => $wallet->id,
                    'type'        => $type,
                    'points'      => $points,
                    'status'      => $status,
                    'description' => $type === 'credit' ? 'Reward earned' : 'Points redeemed',
                    'reference'   => 'TXN-' . Str::upper(Str::random(8)),
                ]);
            }
        }

        $this->command->info('Wallets and transactions seeded successfully!');
    }
}
