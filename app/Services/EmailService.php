<?php
declare(strict_types=1);
namespace App\Services;

use Core\Logger;

class EmailService
{
    private string $host;
    private int    $port;
    private string $username;
    private string $password;
    private string $fromEmail;
    private string $fromName;
    private bool   $useSMTP;

    public function __construct()
    {
        $cfg = require dirname(__DIR__, 2) . '/config/config.php';
        $mail = $cfg['mail'] ?? [];
        $this->host      = $mail['host']     ?? '';
        $this->port      = (int)($mail['port']     ?? 587);
        $this->username  = $mail['username'] ?? '';
        $this->password  = $mail['password'] ?? '';
        $this->fromEmail = $mail['from']     ?? 'noreply@kaaydem.sn';
        $this->fromName  = $mail['name']     ?? 'Kaay Dem !';
        $this->useSMTP   = !empty($this->host) && !empty($this->username) && !empty($this->password);
    }

    public function send(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        if (empty($textBody)) {
            $textBody = strip_tags(str_replace(['<br>', '<br/>', '</p>', '</div>'], "\n", $htmlBody));
        }
        try {
            if ($this->useSMTP) {
                return $this->sendSMTP($toEmail, $toName, $subject, $htmlBody, $textBody);
            }
            return $this->sendNative($toEmail, $toName, $subject, $htmlBody, $textBody);
        } catch (\Throwable $e) {
            Logger::error('Email failed', ['to' => $toEmail, 'error' => $e->getMessage()]);
            return false;
        }
    }

    private function sendSMTP(string $to, string $toName, string $subject, string $html, string $text): bool
    {
        $boundary = md5(uniqid('kd', true));
        $message  = 'From: =?UTF-8?B?' . base64_encode($this->fromName) . '?= <' . $this->fromEmail . ">\r\n";
        $message .= 'To: =?UTF-8?B?' . base64_encode($toName) . '?= <' . $to . ">\r\n";
        $message .= 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= 'Content-Type: multipart/alternative; boundary="' . $boundary . "\"\r\n";
        $message .= "X-Mailer: KaayDem-PHP\r\n\r\n";
        $message .= '--' . $boundary . "\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
        $message .= chunk_split(base64_encode($text)) . "\r\n";
        $message .= '--' . $boundary . "\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
        $message .= chunk_split(base64_encode($html)) . "\r\n";
        $message .= '--' . $boundary . "--\r\n";

        $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]]);
        $prefix = ($this->port === 465) ? 'ssl://' : '';
        $sock = @stream_socket_client($prefix . $this->host . ':' . $this->port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
        if (!$sock) throw new \RuntimeException("SMTP connect failed: $errstr ($errno)");
        stream_set_timeout($sock, 15);

        $read = fgets($sock, 512);
        if (!str_starts_with($read, '220')) { fclose($sock); throw new \RuntimeException("SMTP greeting: $read"); }

        fwrite($sock, "EHLO kaaydem.sn\r\n");
        while ($r = fgets($sock, 512)) { if (substr($r, 3, 1) === ' ') break; }

        if ($this->port === 587) {
            fwrite($sock, "STARTTLS\r\n");
            $r = fgets($sock, 512);
            if (str_starts_with($r, '220')) {
                stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                fwrite($sock, "EHLO kaaydem.sn\r\n");
                while ($r = fgets($sock, 512)) { if (substr($r, 3, 1) === ' ') break; }
            }
        }

        fwrite($sock, "AUTH LOGIN\r\n");   fgets($sock, 512);
        fwrite($sock, base64_encode($this->username) . "\r\n"); fgets($sock, 512);
        fwrite($sock, base64_encode($this->password) . "\r\n");
        $auth = fgets($sock, 512);
        if (!str_starts_with($auth, '235')) { fclose($sock); throw new \RuntimeException("SMTP auth failed: $auth"); }

        fwrite($sock, "MAIL FROM:<{$this->fromEmail}>\r\n"); fgets($sock, 512);
        fwrite($sock, "RCPT TO:<$to>\r\n"); fgets($sock, 512);
        fwrite($sock, "DATA\r\n"); fgets($sock, 512);
        fwrite($sock, $message . "\r\n.\r\n");
        $resp = fgets($sock, 512);
        fwrite($sock, "QUIT\r\n"); fclose($sock);

        $ok = str_starts_with($resp, '250');
        Logger::info('Email SMTP', ['to' => $to, 'subject' => $subject, 'ok' => $ok]);
        return $ok;
    }

    private function sendNative(string $to, string $toName, string $subject, string $html, string $text): bool
    {
        $boundary = md5(uniqid());
        $headers  = 'From: ' . $this->fromName . ' <' . $this->fromEmail . ">\r\n";
        $headers .= "Reply-To: {$this->fromEmail}\r\nMIME-Version: 1.0\r\n";
        $headers .= 'Content-Type: multipart/alternative; boundary="' . $boundary . "\"";
        $body     = '--' . $boundary . "\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n$text\r\n";
        $body    .= '--' . $boundary . "\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n$html\r\n";
        $body    .= '--' . $boundary . '--';
        $ok = mail('"' . $toName . '" <' . $to . '>', '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
        Logger::info('Email native', ['to' => $to, 'ok' => $ok]);
        return $ok;
    }

    private function appUrl(): string
    {
        static $u = null;
        if ($u === null) { $c = require dirname(__DIR__, 2) . '/config/config.php'; $u = $c['app']['url'] ?? ''; }
        return $u;
    }

    private function tpl(string $heading, string $body): string
    {
        $base = $this->appUrl();
        return '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1.0"><style>'
            . '* { box-sizing:border-box; margin:0; padding:0; }'
            . 'body { background:#F0F4F8; font-family:"Segoe UI",Arial,sans-serif; color:#1A1A2E; }'
            . '.wrap { max-width:600px; margin:0 auto; padding:2rem 1rem; }'
            . '.header { background:linear-gradient(135deg,#080E1E 0%,#0D1B3E 100%); border-radius:16px 16px 0 0; padding:2rem; text-align:center; }'
            . '.logo { font-size:1.5rem; font-weight:900; color:#fff; letter-spacing:-1px; }'
            . '.logo em { color:#F0A500; font-style:normal; }'
            . '.body { background:#fff; padding:2rem 2.5rem; border-radius:0 0 16px 16px; box-shadow:0 4px 24px rgba(8,14,30,.10); }'
            . 'h1 { font-size:1.4rem; font-weight:800; color:#080E1E; margin-bottom:1.25rem; }'
            . 'p { line-height:1.7; margin-bottom:.9rem; color:#374151; font-size:.95rem; }'
            . 'ul { padding-left:1.2rem; margin-bottom:1rem; }'
            . 'ul li { line-height:1.8; color:#374151; font-size:.95rem; }'
            . '.btn { display:inline-block; margin-top:.75rem; padding:.75rem 1.75rem;'
            . ' background:linear-gradient(135deg,#1246AA,#1A5CC8); color:#fff !important;'
            . ' text-decoration:none; border-radius:10px; font-weight:700; font-size:.95rem; }'
            . '.footer { text-align:center; padding:1.5rem; color:#94A3B8; font-size:.78rem; }'
            . '</style></head><body>'
            . '<div class="wrap">'
            . '<div class="header">'
            . '<div class="logo">&#x1F697; Kaay <em>Dem</em> !</div>'
            . '<div style="color:rgba(255,255,255,.5);font-size:.8rem;margin-top:.3rem">Covoiturage &#233;tudiant s&#233;n&#233;galais</div>'
            . '</div>'
            . '<div class="body">'
            . '<h1>' . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . '</h1>'
            . $body
            . '</div>'
            . '<div class="footer">'
            . '&#169; Kaay Dem ! &middot; Covoiturage &#233;tudiant s&#233;n&#233;galais<br>'
            . 'Vous recevez cet email car vous &#234;tes inscrit sur Kaay Dem !'
            . '</div>'
            . '</div></body></html>';
    }

    public function sendWelcome(string $email, string $name): bool
    {
        $url = $this->appUrl();
        return $this->send($email, $name, 'Bienvenue sur Kaay Dem !',
            $this->tpl('Bienvenue ' . htmlspecialchars($name) . ' !',
                '<p>Votre compte <strong>Kaay Dem !</strong> a &#233;t&#233; cr&#233;&#233; avec succ&#232;s.</p>'
                . '<p>Vous pouvez maintenant :</p><ul>'
                . '<li>&#x1F50D; Rechercher des trajets entre villes s&#233;n&#233;galaises</li>'
                . '<li>&#x1F3AB; R&#233;server une place en quelques secondes</li>'
                . '<li>&#x1F697; Devenir conducteur et partager vos frais de route</li>'
                . '</ul><p><a href="' . $url . '/search" class="btn">Trouver un trajet &#8594;</a></p>'
            )
        );
    }

    public function sendBookingConfirmed(string $email, string $passengerName, string $departure, string $arrival, string $date, float $price): bool
    {
        $url = $this->appUrl();
        $priceFormatted = number_format($price, 0, ',', ' ');
        return $this->send($email, $passengerName,
            'R&#233;servation confirm&#233;e — ' . $departure . ' &#8594; ' . $arrival,
            $this->tpl('R&#233;servation confirm&#233;e !',
                '<p>Bonjour <strong>' . htmlspecialchars($passengerName) . '</strong>,</p>'
                . '<p>Votre r&#233;servation a &#233;t&#233; <strong style="color:#065F46">confirm&#233;e</strong>.</p>'
                . '<div style="background:#F0FDF4;border-left:4px solid #059669;padding:1rem;border-radius:8px;margin:1rem 0">'
                . '<div>&#x1F5FA;&#xFE0F; <strong>' . htmlspecialchars($departure) . ' &#8594; ' . htmlspecialchars($arrival) . '</strong></div>'
                . '<div>&#x1F4C5; <strong>' . htmlspecialchars($date) . '</strong></div>'
                . '<div>&#x1F4B0; <strong>' . $priceFormatted . ' FCFA</strong></div>'
                . '</div>'
                . '<p><a href="' . $url . '/bookings" class="btn">Voir ma r&#233;servation &#8594;</a></p>'
            )
        );
    }

    public function sendBookingCancelled(string $email, string $name, string $departure, string $arrival): bool
    {
        $url = $this->appUrl();
        return $this->send($email, $name,
            'R&#233;servation annul&#233;e — ' . $departure . ' &#8594; ' . $arrival,
            $this->tpl('R&#233;servation annul&#233;e',
                '<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>'
                . '<p>Votre r&#233;servation <strong>' . htmlspecialchars($departure) . ' &#8594; ' . htmlspecialchars($arrival) . '</strong> a &#233;t&#233; annul&#233;e.</p>'
                . '<p><a href="' . $url . '/search" class="btn">Rechercher un trajet &#8594;</a></p>'
            )
        );
    }

    public function sendDriverApproved(string $email, string $name): bool
    {
        $url = $this->appUrl();
        return $this->send($email, $name, 'Votre compte conducteur est activ&#233; !',
            $this->tpl('F&#233;licitations, vous &#234;tes conducteur !',
                '<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>'
                . '<p>Votre demande a &#233;t&#233; <strong style="color:#065F46">approuv&#233;e</strong>.</p>'
                . '<ul><li>Publiez vos trajets</li><li>Transportez des passagers</li><li>Partagez vos frais</li></ul>'
                . '<p><a href="' . $url . '/trips/create" class="btn">Publier mon premier trajet &#8594;</a></p>'
            )
        );
    }

    public function sendDriverRejected(string $email, string $name, string $reason): bool
    {
        $url = $this->appUrl();
        return $this->send($email, $name, 'Demande conducteur — compl&#233;ment requis',
            $this->tpl('Demande en attente',
                '<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>'
                . '<div style="background:#FEF2F2;border-left:4px solid #E8442F;padding:1rem;border-radius:8px;margin:1rem 0">'
                . '<strong>Motif :</strong> ' . htmlspecialchars($reason) . '</div>'
                . '<p><a href="' . $url . '/profile/driver/request" class="btn">Soumettre &#224; nouveau &#8594;</a></p>'
            )
        );
    }

    public function sendPasswordReset(string $email, string $name, string $resetLink): bool
    {
        return $this->send($email, $name, 'R&#233;initialisation de votre mot de passe',
            $this->tpl('R&#233;initialiser votre mot de passe',
                '<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>'
                . '<p>Ce lien est valable pendant <strong>1 heure</strong>.</p>'
                . '<p><a href="' . htmlspecialchars($resetLink) . '" class="btn">R&#233;initialiser &#8594;</a></p>'
                . '<p style="color:#64748B;font-size:.85rem">Si vous n&#8217;avez pas fait cette demande, ignorez cet email.</p>'
            )
        );
    }

    public function sendNewMessage(string $email, string $toName, string $fromName, string $preview): bool
    {
        $url = $this->appUrl();
        return $this->send($email, $toName,
            'Nouveau message de ' . $fromName,
            $this->tpl('Nouveau message',
                '<p>Bonjour <strong>' . htmlspecialchars($toName) . '</strong>,</p>'
                . '<p><strong>' . htmlspecialchars($fromName) . '</strong> vous a envoy&#233; un message :</p>'
                . '<div style="background:#F8FAFF;border-left:4px solid #1246AA;padding:1rem;border-radius:8px;margin:1rem 0;font-style:italic">'
                . '&#8220;' . htmlspecialchars($preview) . '&#8221;</div>'
                . '<p><a href="' . $url . '/messages" class="btn">R&#233;pondre &#8594;</a></p>'
            )
        );
    }

    public function sendNewBookingToDriver(string $email, string $driverName, string $passengerName, string $departure, string $arrival, string $date, int $seats): bool
    {
        $url = $this->appUrl();
        return $this->send($email, $driverName,
            'Nouvelle r&#233;servation — ' . $departure . ' &#8594; ' . $arrival,
            $this->tpl('Nouvelle r&#233;servation re&#231;ue',
                '<p>Bonjour <strong>' . htmlspecialchars($driverName) . '</strong>,</p>'
                . '<p><strong>' . htmlspecialchars($passengerName) . '</strong> souhaite r&#233;server <strong>' . $seats . ' place(s)</strong>.</p>'
                . '<div style="background:#EFF6FF;border-left:4px solid #1246AA;padding:1rem;border-radius:8px;margin:1rem 0">'
                . '<div><strong>' . htmlspecialchars($departure) . ' &#8594; ' . htmlspecialchars($arrival) . '</strong></div>'
                . '<div>' . htmlspecialchars($date) . '</div>'
                . '<div>' . $seats . ' place(s)</div></div>'
                . '<p><a href="' . $url . '/dashboard" class="btn">Voir la r&#233;servation &#8594;</a></p>'
            )
        );
    }
}
