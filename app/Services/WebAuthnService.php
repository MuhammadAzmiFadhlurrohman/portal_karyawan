<?php

namespace App\Services;

use App\Models\User;
use App\Models\WebAuthnCredential;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class WebAuthnService
{
    /**
     * P-256 ECDSA SubjectPublicKeyInfo ASN.1 DER header (26 bytes).
     */
    private const P256_SPKI_HEADER_HEX = '3059301306072a8648ce3d020106082a8648ce3d03010703420004';

    /**
     * Ensure table exists even if artisan migrate hasn't been executed on target server yet.
     */
    public function ensureTableExists(): void
    {
        if (Schema::hasTable('webauthn_credentials')) {
            return;
        }

        Schema::create('webauthn_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('credential_id', 255)->unique();
            $table->string('device_name', 150);
            $table->text('public_key_pem');
            $table->integer('algorithm')->default(-7);
            $table->unsignedBigInteger('sign_count')->default(0);
            $table->json('transports')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function base64UrlDecode(string $data): string
    {
        $padded = strtr($data, '-_', '+/');
        $mod = strlen($padded) % 4;
        if ($mod > 0) {
            $padded .= str_repeat('=', 4 - $mod);
        }

        $decoded = base64_decode($padded, true);
        if ($decoded === false) {
            throw new RuntimeException('Invalid base64url payload.');
        }

        return $decoded;
    }

    /**
     * Generate WebAuthn credential creation options for the authenticated user.
     *
     * @return array<string, mixed>
     */
    public function generateRegistrationOptions(User $user, Request $request): array
    {
        $this->ensureTableExists();

        $challenge = random_bytes(32);
        $challengeB64 = $this->base64UrlEncode($challenge);
        $request->session()->put('webauthn_register_challenge', $challengeB64);

        $excludeCredentials = WebAuthnCredential::query()
            ->where('user_id', $user->id)
            ->get(['credential_id', 'transports'])
            ->map(function (WebAuthnCredential $cred) {
                $item = [
                    'type' => 'public-key',
                    'id' => $cred->credential_id,
                ];
                if (! empty($cred->transports)) {
                    $item['transports'] = $cred->transports;
                }

                return $item;
            })
            ->values()
            ->all();

        $userName = $user->username ?: ($user->email ?: $user->employee_code);

        return [
            'rp' => [
                'name' => config('app.name', 'GoMSN - PT Media Solusi Network'),
                'id' => $request->getHost(),
            ],
            'user' => [
                'id' => $this->base64UrlEncode((string) $user->id),
                'name' => $userName,
                'displayName' => $user->name,
            ],
            'challenge' => $challengeB64,
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],
                ['type' => 'public-key', 'alg' => -257],
            ],
            'timeout' => 60000,
            'excludeCredentials' => $excludeCredentials,
            'authenticatorSelection' => [
                'residentKey' => 'preferred',
                'requireResidentKey' => false,
                'userVerification' => 'preferred',
            ],
            'attestation' => 'none',
        ];
    }

    /**
     * Verify WebAuthn registration response and persist the public key credential.
     *
     * @param  array<string, mixed>  $payload
     */
    public function verifyAndStoreRegistration(User $user, Request $request, array $payload): WebAuthnCredential
    {
        $this->ensureTableExists();

        $expectedChallenge = (string) $request->session()->pull('webauthn_register_challenge', '');
        if ($expectedChallenge === '') {
            throw ValidationException::withMessages([
                'webauthn' => 'Sesi pendaftaran biometrik kedaluwarsa. Silakan coba lagi.',
            ]);
        }

        $credentialId = trim((string) ($payload['credential_id'] ?? ''));
        if ($credentialId === '' || strlen($credentialId) > 255) {
            throw ValidationException::withMessages([
                'webauthn' => 'ID kredensial biometrik tidak valid.',
            ]);
        }

        $clientDataBytes = $this->base64UrlDecode((string) ($payload['client_data_json'] ?? ''));
        $clientData = json_decode($clientDataBytes, true);
        if (! is_array($clientData)) {
            throw ValidationException::withMessages([
                'webauthn' => 'ClientDataJSON biometrik tidak valid.',
            ]);
        }

        if (($clientData['type'] ?? '') !== 'webauthn.create') {
            throw ValidationException::withMessages([
                'webauthn' => 'Tipe respons WebAuthn tidak sesuai untuk pendaftaran.',
            ]);
        }

        if (! hash_equals($expectedChallenge, (string) ($clientData['challenge'] ?? ''))) {
            throw ValidationException::withMessages([
                'webauthn' => 'Challenge pendaftaran biometrik tidak cocok.',
            ]);
        }

        if (! $this->verifyOrigin((string) ($clientData['origin'] ?? ''), $request)) {
            throw ValidationException::withMessages([
                'webauthn' => 'Origin situs tidak diizinkan untuk pendaftaran biometrik.',
            ]);
        }

        $signCount = 0;
        $algorithm = (int) ($payload['algorithm'] ?? -7);
        $publicKeyPem = null;

        // Parse attestationObject when present to verify RP ID hash and support fallback key extraction
        $coseKeyMap = null;
        if (! empty($payload['attestation_object'])) {
            $attestationBytes = $this->base64UrlDecode((string) $payload['attestation_object']);
            $attestation = $this->decodeCbor($attestationBytes);
            $authData = $attestation['authData'] ?? null;

            if (! is_string($authData) || strlen($authData) < 55) {
                throw ValidationException::withMessages([
                    'webauthn' => 'Authenticator data biometrik tidak lengkap.',
                ]);
            }

            $expectedRpHash = hash('sha256', $request->getHost(), true);
            if (! hash_equals($expectedRpHash, substr($authData, 0, 32))) {
                throw ValidationException::withMessages([
                    'webauthn' => 'Domain (RP ID) biometrik tidak cocok.',
                ]);
            }

            $flags = ord($authData[32]);
            if (($flags & 0x01) === 0 || ($flags & 0x40) === 0) {
                throw ValidationException::withMessages([
                    'webauthn' => 'Verifikasi kehadiran pengguna atau data kredensial tidak ditemukan.',
                ]);
            }

            $signCount = unpack('N', substr($authData, 33, 4))[1];
            $credIdLen = unpack('n', substr($authData, 53, 2))[1];
            $coseKeyBytes = substr($authData, 55 + $credIdLen);
            if ($coseKeyBytes !== '') {
                $coseKeyMap = $this->decodeCbor($coseKeyBytes);
                if (is_array($coseKeyMap) && isset($coseKeyMap[3])) {
                    $algorithm = (int) $coseKeyMap[3];
                }
            }
        }

        if (! empty($payload['public_key_spki'])) {
            $spkiBytes = $this->base64UrlDecode((string) $payload['public_key_spki']);
            $pem = $this->spkiDerToPem($spkiBytes);
            if (@openssl_pkey_get_public($pem) !== false) {
                $publicKeyPem = $pem;
            }
        }

        if ($publicKeyPem === null && is_array($coseKeyMap)) {
            $publicKeyPem = $this->coseKeyToPem($coseKeyMap);
        }

        if ($publicKeyPem === null || @openssl_pkey_get_public($publicKeyPem) === false) {
            throw ValidationException::withMessages([
                'webauthn' => 'Browser atau perangkat tidak memberikan kunci publik biometrik yang didukung (gunakan sidik jari / Face ID / Windows Hello standar).',
            ]);
        }

        $existing = WebAuthnCredential::query()->where('credential_id', $credentialId)->first();
        if ($existing && (int) $existing->user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'webauthn' => 'Kredensial biometrik ini sudah terdaftar pada akun lain.',
            ]);
        }

        $transports = array_values(array_filter(
            (array) ($payload['transports'] ?? []),
            static fn ($t) => is_string($t) && $t !== ''
        ));

        $deviceName = trim((string) ($payload['device_name'] ?? ''));
        if ($deviceName === '') {
            $deviceName = $this->detectDeviceLabel((string) $request->userAgent());
        }
        $deviceName = mb_substr($deviceName, 0, 150);

        $credential = WebAuthnCredential::query()->updateOrCreate(
            ['credential_id' => $credentialId],
            [
                'user_id' => $user->id,
                'device_name' => $deviceName,
                'public_key_pem' => $publicKeyPem,
                'algorithm' => $algorithm,
                'sign_count' => $signCount,
                'transports' => $transports,
                'last_used_at' => now(),
            ]
        );

        AuditLogger::record(
            action: 'auth.webauthn_enrolled',
            auditable: $credential,
            newValues: [
                'device_name' => $deviceName,
                'algorithm' => $algorithm,
                'transports' => $transports,
            ],
            actorId: $user->id
        );

        return $credential;
    }

    /**
     * Generate WebAuthn assertion (login) options.
     *
     * @param  array<int, string>  $localCredentialIds
     * @return array<string, mixed>
     */
    public function generateLoginOptions(Request $request, ?string $identifier = null, array $localCredentialIds = []): array
    {
        $this->ensureTableExists();

        $challenge = random_bytes(32);
        $challengeB64 = $this->base64UrlEncode($challenge);
        $request->session()->put('webauthn_login_challenge', $challengeB64);

        $allowCredentials = [];

        if ($identifier !== null && trim($identifier) !== '') {
            $user = User::findByIdentifier(trim($identifier));
            if ($user) {
                $allowCredentials = WebAuthnCredential::query()
                    ->where('user_id', $user->id)
                    ->get(['credential_id', 'transports'])
                    ->map(fn (WebAuthnCredential $cred) => $this->formatAllowCredential($cred))
                    ->values()
                    ->all();
            }
        } elseif (! empty($localCredentialIds)) {
            $filteredIds = array_values(array_filter(
                $localCredentialIds,
                static fn ($id) => is_string($id) && $id !== '' && strlen($id) <= 255
            ));

            if (! empty($filteredIds)) {
                $allowCredentials = WebAuthnCredential::query()
                    ->whereIn('credential_id', $filteredIds)
                    ->whereHas('user', static fn ($q) => $q->where('is_active', true))
                    ->get(['credential_id', 'transports'])
                    ->map(fn (WebAuthnCredential $cred) => $this->formatAllowCredential($cred))
                    ->values()
                    ->all();
            }
        }

        return [
            'challenge' => $challengeB64,
            'rpId' => $request->getHost(),
            'timeout' => 60000,
            'userVerification' => 'preferred',
            'allowCredentials' => $allowCredentials,
        ];
    }

    /**
     * Verify signed WebAuthn assertion and return the authenticated User.
     *
     * @param  array<string, mixed>  $payload
     */
    public function verifyLoginAssertion(Request $request, array $payload): User
    {
        $this->ensureTableExists();

        $expectedChallenge = (string) $request->session()->pull('webauthn_login_challenge', '');
        if ($expectedChallenge === '') {
            throw ValidationException::withMessages([
                'webauthn' => 'Sesi tantangan login biometrik kedaluwarsa. Silakan coba lagi.',
            ]);
        }

        $credentialId = trim((string) ($payload['credential_id'] ?? ''));
        $credential = WebAuthnCredential::query()
            ->where('credential_id', $credentialId)
            ->with('user')
            ->first();

        if (! $credential || ! $credential->user) {
            throw ValidationException::withMessages([
                'webauthn' => 'Perangkat atau sidik jari ini belum terdaftar di GoMSN. Silakan masuk dengan password terlebih dahulu lalu aktifkan Biometrik di menu Profil Saya.',
            ]);
        }

        if (! $credential->user->is_active) {
            throw ValidationException::withMessages([
                'webauthn' => 'Akun Anda berstatus non-aktif. Hubungi HRD.',
            ]);
        }

        $clientDataBytes = $this->base64UrlDecode((string) ($payload['client_data_json'] ?? ''));
        $clientData = json_decode($clientDataBytes, true);
        if (! is_array($clientData)) {
            throw ValidationException::withMessages([
                'webauthn' => 'Data verifikasi klien biometrik tidak valid.',
            ]);
        }

        if (($clientData['type'] ?? '') !== 'webauthn.get') {
            throw ValidationException::withMessages([
                'webauthn' => 'Tipe respons WebAuthn tidak valid untuk login.',
            ]);
        }

        if (! hash_equals($expectedChallenge, (string) ($clientData['challenge'] ?? ''))) {
            throw ValidationException::withMessages([
                'webauthn' => 'Challenge autentikasi tidak cocok.',
            ]);
        }

        if (! $this->verifyOrigin((string) ($clientData['origin'] ?? ''), $request)) {
            throw ValidationException::withMessages([
                'webauthn' => 'Origin situs tidak valid untuk autentikasi biometrik.',
            ]);
        }

        $authDataBytes = $this->base64UrlDecode((string) ($payload['authenticator_data'] ?? ''));
        if (strlen($authDataBytes) < 37) {
            throw ValidationException::withMessages([
                'webauthn' => 'Authenticator data tidak lengkap.',
            ]);
        }

        $expectedRpHash = hash('sha256', $request->getHost(), true);
        if (! hash_equals($expectedRpHash, substr($authDataBytes, 0, 32))) {
            throw ValidationException::withMessages([
                'webauthn' => 'Domain autentikasi biometrik tidak cocok.',
            ]);
        }

        $flags = ord($authDataBytes[32]);
        if (($flags & 0x01) === 0) {
            throw ValidationException::withMessages([
                'webauthn' => 'Kehadiran pengguna tidak terkonfirmasi oleh sensor biometrik.',
            ]);
        }

        $newSignCount = unpack('N', substr($authDataBytes, 33, 4))[1];
        if ($credential->sign_count > 0 && $newSignCount > 0 && $newSignCount <= $credential->sign_count) {
            throw ValidationException::withMessages([
                'webauthn' => 'Deteksi anomali sign counter biometrik (potensi kloning kredensial). Gunakan password atau daftarkan ulang.',
            ]);
        }

        $signatureBytes = $this->base64UrlDecode((string) ($payload['signature'] ?? ''));
        $signedData = $authDataBytes.hash('sha256', $clientDataBytes, true);

        $verified = @openssl_verify($signedData, $signatureBytes, $credential->public_key_pem, OPENSSL_ALGO_SHA256);
        if ($verified !== 1) {
            throw ValidationException::withMessages([
                'webauthn' => 'Tanda tangan kriptografi biometrik gagal diverifikasi.',
            ]);
        }

        $credential->update([
            'sign_count' => $newSignCount > $credential->sign_count ? $newSignCount : $credential->sign_count,
            'last_used_at' => now(),
        ]);

        return $credential->user;
    }

    /**
     * Format allowCredential descriptor for browser navigator.credentials.get().
     *
     * @return array<string, mixed>
     */
    private function formatAllowCredential(WebAuthnCredential $cred): array
    {
        $item = [
            'type' => 'public-key',
            'id' => $cred->credential_id,
        ];
        if (! empty($cred->transports)) {
            $item['transports'] = $cred->transports;
        }

        return $item;
    }

    private function verifyOrigin(string $origin, Request $request): bool
    {
        if ($origin === '') {
            return false;
        }

        $originHost = parse_url($origin, PHP_URL_HOST);
        $originScheme = parse_url($origin, PHP_URL_SCHEME);
        if (! is_string($originHost) || ! is_string($originScheme)) {
            return false;
        }

        $requestHost = $request->getHost();
        if (strcasecmp($originHost, $requestHost) !== 0) {
            return false;
        }

        $originPort = parse_url($origin, PHP_URL_PORT);
        $requestPort = $request->getPort();
        if ($originPort !== null && $requestPort !== null && (int) $originPort !== (int) $requestPort) {
            return false;
        }

        $isLocal = in_array(strtolower($requestHost), ['localhost', '127.0.0.1', '::1'], true);

        return strtolower($originScheme) === 'https'
            || ($isLocal && strtolower($originScheme) === 'http')
            || strcasecmp($origin, $request->getSchemeAndHttpHost()) === 0;
    }

    private function spkiDerToPem(string $derBytes): string
    {
        return "-----BEGIN PUBLIC KEY-----\n"
            .chunk_split(base64_encode($derBytes), 64, "\n")
            ."-----END PUBLIC KEY-----\n";
    }

    /**
     * Convert a parsed COSE public key map to SPKI PEM format (supports ES256 P-256 and RS256).
     *
     * @param  array<int|string, mixed>  $cose
     */
    private function coseKeyToPem(array $cose): ?string
    {
        $kty = $cose[1] ?? null;

        // EC2 (P-256 / ES256)
        if ($kty === 2 && ($cose[-1] ?? null) === 1) {
            $x = $cose[-2] ?? '';
            $y = $cose[-3] ?? '';
            if (is_string($x) && is_string($y) && strlen($x) === 32 && strlen($y) === 32) {
                $der = hex2bin(self::P256_SPKI_HEADER_HEX).$x.$y;

                return $this->spkiDerToPem($der);
            }
        }

        // RSA (RS256)
        if ($kty === 3) {
            $n = $cose[-1] ?? '';
            $e = $cose[-2] ?? '';
            if (is_string($n) && is_string($e) && $n !== '' && $e !== '') {
                $nInt = $this->asn1Integer($n);
                $eInt = $this->asn1Integer($e);
                $rsaPub = $this->asn1Sequence($nInt.$eInt);
                $algId = hex2bin('300d06092a864886f70d0101010500');
                $bitString = $this->asn1BitString($rsaPub);
                $spkiDer = $this->asn1Sequence($algId.$bitString);

                return $this->spkiDerToPem($spkiDer);
            }
        }

        return null;
    }

    private function asn1Length(int $length): string
    {
        if ($length < 0x80) {
            return chr($length);
        }
        $bytes = ltrim(pack('N', $length), "\x00");

        return chr(0x80 | strlen($bytes)).$bytes;
    }

    private function asn1Integer(string $unsignedBytes): string
    {
        $trimmed = ltrim($unsignedBytes, "\x00");
        if ($trimmed === '') {
            $trimmed = "\x00";
        }
        if ((ord($trimmed[0]) & 0x80) !== 0) {
            $trimmed = "\x00".$trimmed;
        }

        return "\x02".$this->asn1Length(strlen($trimmed)).$trimmed;
    }

    private function asn1Sequence(string $content): string
    {
        return "\x30".$this->asn1Length(strlen($content)).$content;
    }

    private function asn1BitString(string $content): string
    {
        $payload = "\x00".$content;

        return "\x03".$this->asn1Length(strlen($payload)).$payload;
    }

    /**
     * Minimal RFC 8949 CBOR decoder for WebAuthn attestationObject and COSE public keys.
     */
    private function decodeCbor(string $bytes): mixed
    {
        $offset = 0;

        return $this->readCborItem($bytes, $offset);
    }

    private function readCborItem(string $bytes, int &$offset): mixed
    {
        if ($offset >= strlen($bytes)) {
            throw new RuntimeException('Unexpected end of CBOR payload.');
        }

        $initial = ord($bytes[$offset++]);
        $majorType = ($initial >> 5) & 0x07;
        $additional = $initial & 0x1F;

        $readLength = function (int $add) use ($bytes, &$offset): int {
            if ($add < 24) {
                return $add;
            }
            if ($add === 24) {
                return ord($bytes[$offset++]);
            }
            if ($add === 25) {
                $val = unpack('n', substr($bytes, $offset, 2))[1];
                $offset += 2;

                return $val;
            }
            if ($add === 26) {
                $val = unpack('N', substr($bytes, $offset, 4))[1];
                $offset += 4;

                return $val;
            }
            if ($add === 27) {
                $val = unpack('J', substr($bytes, $offset, 8))[1];
                $offset += 8;

                return $val;
            }
            throw new RuntimeException("Unsupported CBOR additional info: {$add}");
        };

        switch ($majorType) {
            case 0: // Unsigned int
                return $readLength($additional);

            case 1: // Negative int
                return -1 - $readLength($additional);

            case 2: // Byte string
            case 3: // Text string
                $len = $readLength($additional);
                $str = substr($bytes, $offset, $len);
                $offset += $len;

                return $str;

            case 4: // Array
                $count = $readLength($additional);
                $arr = [];
                for ($i = 0; $i < $count; $i++) {
                    $arr[] = $this->readCborItem($bytes, $offset);
                }

                return $arr;

            case 5: // Map
                $count = $readLength($additional);
                $map = [];
                for ($i = 0; $i < $count; $i++) {
                    $key = $this->readCborItem($bytes, $offset);
                    $val = $this->readCborItem($bytes, $offset);
                    $map[$key] = $val;
                }

                return $map;

            case 6: // Semantic tag (unwrap)
                $readLength($additional);

                return $this->readCborItem($bytes, $offset);

            case 7: // Simple / float
                if ($additional === 20) {
                    return false;
                }
                if ($additional === 21) {
                    return true;
                }
                if ($additional === 22 || $additional === 23) {
                    return null;
                }
                $skip = match ($additional) {
                    24 => 1,
                    25 => 2,
                    26 => 4,
                    27 => 8,
                    default => 0,
                };
                $offset += $skip;

                return null;

            default:
                throw new RuntimeException("Unsupported CBOR major type: {$majorType}");
        }
    }

    private function detectDeviceLabel(string $ua): string
    {
        $os = 'Perangkat';
        if (str_contains($ua, 'iPhone')) {
            $os = 'iPhone (Face ID / Touch ID)';
        } elseif (str_contains($ua, 'iPad')) {
            $os = 'iPad (Touch ID / Face ID)';
        } elseif (str_contains($ua, 'Android')) {
            $os = 'Android Biometrik';
        } elseif (str_contains($ua, 'Windows')) {
            $os = 'Windows Hello / Laptop';
        } elseif (str_contains($ua, 'Mac OS') || str_contains($ua, 'Macintosh')) {
            $os = 'Mac (Touch ID / Passkey)';
        } elseif (str_contains($ua, 'Linux')) {
            $os = 'Linux Passkey';
        }

        $browser = '';
        if (str_contains($ua, 'Edg')) {
            $browser = 'Edge';
        } elseif (str_contains($ua, 'Chrome') && ! str_contains($ua, 'Edg')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'Safari') && ! str_contains($ua, 'Chrome')) {
            $browser = 'Safari';
        } elseif (str_contains($ua, 'Firefox')) {
            $browser = 'Firefox';
        }

        return $browser !== '' ? "{$os} · {$browser}" : $os;
    }
}
