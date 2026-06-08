<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use App\Models\User;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminSmsController extends Controller
{
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    /**
     * Affiche la page d'envoi de SMS
     */
    public function index()
    {
        $users = User::where('is_admin', false)
            ->whereNotNull('phone')
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return view('admin.sms.index', compact('users'));
    }

    /**
     * Journal des SMS envoyés
     */
    public function logs(Request $request)
    {
        $query = SmsLog::with('sender')->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($context = $request->input('context')) {
            $query->where('context', $context);
        }

        $logs = $query->paginate(50)->withQueryString();

        $stats = [
            'total'  => SmsLog::count(),
            'sent'   => SmsLog::where('status', 'sent')->count(),
            'failed' => SmsLog::where('status', 'failed')->count(),
        ];

        return view('admin.sms.logs', compact('logs', 'stats'));
    }

    /**
     * Envoie un SMS à un ou plusieurs utilisateurs
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1600',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'string',
        ]);

        $message = $request->input('message');
        $recipientType = $request->input('recipient_type', 'selected');
        $recipients = $request->input('recipients', []);

        $phoneNumbers = [];

        if ($recipientType === 'all') {
            // Envoyer à tous les utilisateurs
            $phoneNumbers = User::where('is_admin', false)
                ->whereNotNull('phone')
                ->pluck('phone')
                ->toArray();
        } elseif ($recipientType === 'manual') {
            // Numéros manuels
            $manualNumbers = $request->input('manual_numbers', '');
            $phoneNumbers = array_filter(
                array_map('trim', preg_split('/[\n,;]+/', $manualNumbers))
            );
        } else {
            // Utilisateurs sélectionnés
            $phoneNumbers = User::whereIn('id', $recipients)
                ->whereNotNull('phone')
                ->pluck('phone')
                ->toArray();
        }

        if (empty($phoneNumbers)) {
            return back()->with('error', 'Aucun destinataire valide trouvé.');
        }

        Log::info('Admin SMS: Début envoi', [
            'total_recipients' => count($phoneNumbers),
            'message_length' => strlen($message)
        ]);

        $results = $this->twilioService->sendBulkSms($phoneNumbers, $message, 'admin_bulk', session('user_id'));

        Log::info('Admin SMS: Résultats', $results);

        return back()->with('success', sprintf(
            'SMS envoyés : %d réussis, %d échoués sur %d total.',
            $results['success'],
            $results['failed'],
            count($phoneNumbers)
        ));
    }

    /**
     * Envoie un SMS de test
     */
    public function sendTest(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:1600',
        ]);

        $result = $this->twilioService->sendSms(
            $request->input('phone'),
            $request->input('message'),
            'test',
            session('user_id')
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'SMS de test envoyé avec succès !'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erreur: ' . ($result['error'] ?? 'Erreur inconnue')
        ], 400);
    }
}
