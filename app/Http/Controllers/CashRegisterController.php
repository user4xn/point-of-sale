<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashRegisterController extends Controller
{
    public function check()
    {
        $register = CashRegister::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        return response()->json([
            'open' => (bool) $register,
            'register' => $register,
        ]);
    }

    public function open(Request $request)
    {
        DB::beginTransaction();

        try {
            $hasOpen = CashRegister::where('user_id', auth()->id())
                ->where('status', 'open')
                ->exists();

            if ($hasOpen) {
                return back()->with([
                    'flash' => ['error' => 'Masih ada kas terbuka, tutup dulu sebelum buka kas baru.'],
                ]);
            }

            CashRegister::create([
                'user_id' => auth()->id(),
                'opening_amount' => $request->opening_amount ?? 0,
                'status' => 'open',
                'opened_at' => now(),
            ]);

            DB::commit();

            return back()->with([
                'flash' => ['success' => 'Kas berhasil dibuka.'],
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with([
                'flash' => ['error' => 'Gagal membuka kas: ' . $th->getMessage()],
            ]);
        }
    }

    public function close(Request $request)
    {
        DB::beginTransaction();

        try {
            $register = CashRegister::where('user_id', auth()->id())
                ->where('status', 'open')
                ->latest()
                ->first();

            if (!$register) {
                return back()->with([
                    'flash' => ['error' => 'Tidak ada kas terbuka untuk ditutup.'],
                ]);
            }

            $register->update([
                'closing_amount' => $request->closing_amount ?? 0,
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            DB::commit();

            return back()->with([
                'flash' => ['success' => 'Kas berhasil ditutup.'],
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with([
                'flash' => ['error' => 'Gagal menutup kas: ' . $th->getMessage()],
            ]);
        }
    }
}
