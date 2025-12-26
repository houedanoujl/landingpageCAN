<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

class TwilioService
{
    protected $client;
    protected $verifySid;

    public function __construct()
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $this->verifySid = config('services.twilio.verify_sid');

        if ($accountSid && $authToken) {
            $this->client = new Client($accountSid, $authToken);
        }
    }

    /**
     * Envoie un code de vérification par SMS via Twilio Verify
     */
    public function sendVerificationCode(string $phoneNumber): array
    {
        if (!$this->client) {
            Log::error('Twilio: Client non configuré');
            return ['success' => false, 'error' => 'Configuration Twilio manquante'];
        }

        try {
            // Formater le numéro au format E.164
            $formattedPhone = $this->formatE164($phoneNumber);
            
            Log::info('Twilio: Envoi code de vérification', [
                'phone' => $formattedPhone,
                'verify_sid' => $this->verifySid
            ]);

            $verification = $this->client->verify->v2
                ->services($this->verifySid)
                ->verifications
                ->create($formattedPhone, 'sms');

            Log::info('Twilio: Code envoyé avec succès', [
                'status' => $verification->status,
                'sid' => $verification->sid
            ]);

            return [
                'success' => true,
                'status' => $verification->status,
                'sid' => $verification->sid
            ];

        } catch (TwilioException $e) {
            Log::error('Twilio: Erreur envoi code', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        } catch (\Exception $e) {
            Log::error('Twilio: Exception inattendue', [
                'message' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifie le code OTP envoyé par l'utilisateur
     */
    public function verifyCode(string $phoneNumber, string $code): array
    {
        if (!$this->client) {
            Log::error('Twilio: Client non configuré');
            return ['success' => false, 'error' => 'Configuration Twilio manquante'];
        }

        try {
            $formattedPhone = $this->formatE164($phoneNumber);
            
            Log::info('Twilio: Vérification code', [
                'phone' => $formattedPhone
            ]);

            $verificationCheck = $this->client->verify->v2
                ->services($this->verifySid)
                ->verificationChecks
                ->create([
                    'to' => $formattedPhone,
                    'code' => $code
                ]);

            $isApproved = $verificationCheck->status === 'approved';

            Log::info('Twilio: Résultat vérification', [
                'status' => $verificationCheck->status,
                'valid' => $isApproved
            ]);

            return [
                'success' => $isApproved,
                'status' => $verificationCheck->status,
                'valid' => $isApproved
            ];

        } catch (TwilioException $e) {
            Log::error('Twilio: Erreur vérification code', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        } catch (\Exception $e) {
            Log::error('Twilio: Exception inattendue', [
                'message' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Formate le numéro de téléphone au format E.164
     */
    private function formatE164(string $phone): string
    {
        // Supprimer tout sauf les chiffres et le +
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Si déjà en format E.164, retourner tel quel
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        // Si commence par 00, remplacer par +
        if (str_starts_with($phone, '00')) {
            return '+' . substr($phone, 2);
        }

        // Côte d'Ivoire: 10 chiffres commençant par 0
        if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            return '+225' . $phone;
        }

        // Sénégal: 9 chiffres commençant par 7
        if (strlen($phone) === 9 && str_starts_with($phone, '7')) {
            return '+221' . $phone;
        }

        // France: 10 chiffres commençant par 06 ou 07
        if (strlen($phone) === 10 && (str_starts_with($phone, '06') || str_starts_with($phone, '07'))) {
            return '+33' . substr($phone, 1);
        }

        // Par défaut: assumer Côte d'Ivoire
        return '+225' . $phone;
    }
}
