<?php
namespace App\Support;

class Mailer
{
    private string $fromName;
    private string $fromEmail;
    public function __construct(Env $env)
    {
        $this->fromName = $env->get('MAIL_FROM_NAME', 'App');
        $this->fromEmail = $env->get('MAIL_FROM_ADDRESS', 'no-reply@app.test');
    }

    // Dev mailer: writes emails to storage/mail/*.log
    public function send(string $to, string $subject, string $html): void
    {
        $dir = __DIR__ . '/../../storage/mail';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $ts = date('Ymd_His');
        $fn = $dir . '/' . $ts . '_' . preg_replace('/[^a-z0-9]+/i','_', $to) . '.log';
        file_put_contents($fn, "From: {$this->fromName} <{$this->fromEmail}>
To: {$to}
Subject: {$subject}

{$html}");
    }
}
