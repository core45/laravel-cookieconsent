<?php

namespace Core45\CookieConsent\Http;

use Core45\CookieConsent\Models\ConsentLog;
use Core45\CookieConsent\Support\ConfigBuilder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LogConsentController
{
    public function __invoke(Request $request, ConfigBuilder $builder): JsonResponse
    {
        abort_unless((bool) config('cookieconsent.logging.enabled', true), 404);
        abort_if(strlen($request->getContent()) > 16384, 413, 'Consent payload too large.');

        $knownCategories = array_keys((array) config('cookieconsent.categories', []));

        $validated = $request->validate([
            'action' => ['required', Rule::in(['first_consent', 'change'])],
            'consentId' => ['required', 'string', 'max:64'],
            'revision' => ['required', 'integer', 'min:0'],
            'acceptType' => ['required', Rule::in(['all', 'custom', 'necessary'])],
            'acceptedCategories' => ['present', 'array'],
            'acceptedCategories.*' => ['string', Rule::in($knownCategories)],
            'rejectedCategories' => ['present', 'array'],
            'rejectedCategories.*' => ['string', Rule::in($knownCategories)],
            'acceptedServices' => ['sometimes', 'array'],
            'languageCode' => ['nullable', 'string', 'max:12'],
            'payload' => ['required', 'array'],
            'idempotencyKey' => ['nullable', 'string', 'max:64'],
        ]);

        $idempotencyKey = $validated['idempotencyKey'] ?? null;

        if ($idempotencyKey !== null && ConsentLog::where('idempotency_key', $idempotencyKey)->exists()) {
            return response()->json(['ok' => true, 'duplicate' => true], 200);
        }

        $log = new ConsentLog([
            'consent_id' => $validated['consentId'],
            'action' => $validated['action'],
            'accept_type' => $validated['acceptType'],
            'accepted_categories' => $validated['acceptedCategories'],
            'rejected_categories' => $validated['rejectedCategories'],
            'accepted_services' => $validated['acceptedServices'] ?? null,
            'revision' => $validated['revision'],
            'language_code' => $validated['languageCode'] ?? null,
            'payload' => $validated['payload'],
            'idempotency_key' => $idempotencyKey,
        ]);

        $log->ip_address = match (config('cookieconsent.logging.capture_ip', 'raw')) {
            'raw' => $request->ip(),
            'hashed' => hash('sha256', $request->ip().(string) config('cookieconsent.logging.ip_hash_salt')),
            default => null,
        };

        $log->user_agent = config('cookieconsent.logging.capture_user_agent', true)
            ? mb_substr((string) $request->userAgent(), 0, 1000)
            : null;

        if (config('cookieconsent.logging.link_user', true) && $request->user() !== null) {
            $log->user_type = $request->user()::class;
            $log->user_id = $request->user()->getAuthIdentifier();
        }

        $log->policy_version = config('cookieconsent.logging.policy_version');
        $log->policy_hash = $builder->policyHash();

        try {
            $log->save();
        } catch (UniqueConstraintViolationException) {
            return response()->json(['ok' => true, 'duplicate' => true], 200);
        }

        return response()->json(['ok' => true], 201);
    }
}
