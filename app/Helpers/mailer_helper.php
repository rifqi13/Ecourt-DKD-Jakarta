<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (!function_exists('kirim_email')) {
    /**
     * Kirim email via PHPMailer dengan SMTP
     *
     * @param string      $to        Email tujuan
     * @param string      $subject   Subjek
     * @param string      $bodyHtml  Body HTML
     * @param int|null    $userId    ID user (untuk log)
     * @param string|null $toName    Nama penerima
     * @return bool
     */
    function kirim_email(
        string $to,
        string $subject,
        string $bodyHtml,
        ?int $userId = null,
        ?string $toName = null
    ): bool {
        $mail = new PHPMailer(true);
        $status = 'sent';
        $error  = null;

        try {
            // SMTP config dari .env
            $mail->isSMTP();
            $mail->Host       = env('email.SMTPHost', 'localhost');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('email.SMTPUser', '');
            $mail->Password   = env('email.SMTPPass', '');
            $mail->SMTPSecure = env('email.SMTPCrypto', 'tls') === 'ssl'
                                ? PHPMailer::ENCRYPTION_SMTPS
                                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) env('email.SMTPPort', 587);

            $mail->setFrom(
                env('email.fromEmail', 'noreply@ecourt.local'),
                env('email.fromName', 'E-Court')
            );

            // Debug (matikan di production)
            if (ENVIRONMENT === 'development') {
                $mail->SMTPDebug = SMTP::DEBUG_OFF;
            }

            $mail->addAddress($to, $toName ?? '');
            $mail->isHTML(true);
            $mail->CharSet  = 'UTF-8';
            $mail->Subject  = $subject;
            $mail->Body     = $bodyHtml;
            $mail->AltBody  = strip_tags($bodyHtml);

            $mail->send();

        } catch (Exception $e) {
            $status = 'failed';
            $error  = $mail->ErrorInfo ?: $e->getMessage();
            log_message('error', 'Kirim email gagal: ' . $error);
        }

        // Log ke DB
        try {
            $db = \Config\Database::connect();
            $db->table('email_log')->insert([
                'user_id'    => $userId,
                'to_email'   => $to,
                'subject'    => $subject,
                'body'       => $bodyHtml,
                'status'     => $status,
                'error'      => $error,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal log email: ' . $e->getMessage());
        }

        return $status === 'sent';
    }
}