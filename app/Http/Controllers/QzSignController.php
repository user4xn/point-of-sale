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

        $signature = null;
        openssl_sign($dataToSign, $signature, $pkeyId, OPENSSL_ALGO_SHA1);

        openssl_free_key($pkeyId);

        return response()->json([
            'signature' => base64_encode($signature)
        ]);
    }
}
