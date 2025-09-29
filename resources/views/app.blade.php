<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/qz-tray/qz-tray.js"></script>
        <script>
          // Cert publik (public-cert.pem)
          qz.security.setCertificatePromise((resolve, reject) => {
        resolve(`-----BEGIN CERTIFICATE-----
MIIEAzCCAuugAwIBAgIUWKnNVP4vaY/YwLiIk3272wudT54wDQYJKoZIhvcNAQEL
BQAwgZAxCzAJBgNVBAYTAklEMRQwEgYDVQQIDAtKYXdhIFRlbmdhaDEUMBIGA1UE
BwwLUHVyYmFsaW5nZ2ExDTALBgNVBAoMBERhbnoxDDAKBgNVBAsMA1BPUzENMAsG
A1UEAwwEVGFkbzEpMCcGCSqGSIb3DQEJARYad2lsZGFuLmRlc3BlcmFkb0BnbWFp
bC5jb20wHhcNMjUwOTI5MTQwNjM2WhcNMzUwOTI3MTQwNjM2WjCBkDELMAkGA1UE
BhMCSUQxFDASBgNVBAgMC0phd2EgVGVuZ2FoMRQwEgYDVQQHDAtQdXJiYWxpbmdn
YTENMAsGA1UECgwERGFuejEMMAoGA1UECwwDUE9TMQ0wCwYDVQQDDARUYWRvMSkw
JwYJKoZIhvcNAQkBFhp3aWxkYW4uZGVzcGVyYWRvQGdtYWlsLmNvbTCCASIwDQYJ
KoZIhvcNAQEBBQADggEPADCCAQoCggEBALtAEqV2O8a56w3vk5ZiaGGfUR8E5/np
BVPehhKLqwxm+7L5qjJOczluemQMYwEYRHQh/Yzw72jWVwa8G8QG1Gj2S64A1gGH
DJIs4dsIAwgXLJG28mplpe/oQ34GMYUCDmdac4EzwlwSL7t1ez/dhgoIE47iDv+d
J66QEpqicoWA+KGOq7gt8QhFKCYBkuJkVv8TltrnIPERCOPCJY+7ZnDULGHDao8Y
eREwfAsLSj86LQoJhQiPLPmL6qD6UoC8NIXYP0y/Y2t5ayLUfnJY/SP+LBqSYxLf
S+bjrIv7G8vchRB5edUl4eVl2wqjXPi65R0FIVQSJuOVgHuyAejJVa8CAwEAAaNT
MFEwHQYDVR0OBBYEFNBB1Uea0dbNkGTNZ/0peDqXjo5uMB8GA1UdIwQYMBaAFNBB
1Uea0dbNkGTNZ/0peDqXjo5uMA8GA1UdEwEB/wQFMAMBAf8wDQYJKoZIhvcNAQEL
BQADggEBAAM2A44xLkUlLTNLud3M12l6aBKIWjPPoK9qmK0b429Rkii70bT1yNUw
OZqkObTZn6c5RX1ZVQLYPW3aXT2sAfWNjGyLVQCHGCHriqNPbJGmpwMDlMfrnaYa
K7j3E9YVbxsuMQ/vaQ6I0pmiScQCOFQbdNFFpysTAb6HaBYc14Linc9CjJAuKosQ
YAvUPbDsUWvADfOXpMEp/N+0EGPMsUt1Issj+i41tGRwCJMQci32WO+pIgByUhtm
7aLzVRpK1bFLwsckk2wFWrRywcvAH5FHMUBZVz43nAMh76lUv6Vkn4eqo84S3rQ8
yhLGhMWzRoMyNPLFM6vlKfSoR6MnJhk=
-----END CERTIFICATE-----`);
    });

          qz.security.setSignatureAlgorithm("SHA512");

          // Minta tanda tangan ke server Laravel
          qz.security.setSignaturePromise((toSign) => {
            return function(resolve, reject) {
                fetch("/qz-sign", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ data: toSign })
                })
                .then(res => res.json())
                .then(json => resolve(json.signature))
                .catch(err => reject(err));
            };
          });
        </script>
        @routes
        @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
