<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QzSignController extends Controller
{
    public function sign(Request $request)
    {
        $dataToSign = $request->input('data');

        $privateKey = file_get_contents(storage_path('app/keys/private-key.pem'));
        $pkeyId = openssl_pkey_get_private($privateKey);

        if (!$pkeyId) {
            return response()->json(['error' => 'Invalid private key'], 500);
        }

        $signature = null;
        $success = openssl_sign($dataToSign, $signature, $pkeyId, OPENSSL_ALGO_SHA512);

        if (!$success) {
            return response()->json(['error' => 'Failed to sign data'], 500);
        }

        openssl_free_key($pkeyId);

        return response()->json([
            'signature' => base64_encode($signature)
        ]);
    }
}
