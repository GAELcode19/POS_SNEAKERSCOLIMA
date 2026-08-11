<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotifier
{
    /**
     * Envía un mensaje de WhatsApp al número configurado.
     * Es "a prueba de fallos": si algo sale mal (sin internet, mala clave,
     * servicio caído) NUNCA lanza excepción ni detiene la venta; solo lo registra.
     */
    public static function send(string $message): void
    {
        $config = config('services.whatsapp');

        if (empty($config['enabled']) || empty($config['to'])) {
            return; // Notificaciones desactivadas o sin número configurado
        }

        try {
            match ($config['driver']) {
                'callmebot' => static::sendViaCallMeBot($config, $message),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::warning('WhatsAppNotifier: no se pudo enviar el aviso', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private static function sendViaCallMeBot(array $config, string $message): void
    {
        if (empty($config['callmebot_key'])) {
            return;
        }

        Http::timeout(8)
            ->connectTimeout(4)
            ->get('https://api.callmebot.com/whatsapp.php', [
                'phone' => $config['to'],
                'text' => $message,
                'apikey' => $config['callmebot_key'],
            ]);
    }
}
