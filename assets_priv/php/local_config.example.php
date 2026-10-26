<?php
/**
 * Copia este ficheiro para local_config.php (que NÃO vai para o Git)
 * e preenche o que quiseres usar. Tudo é opcional.
 */
return [
    // Notificação no telemóvel quando chega uma mensagem (app ntfy, Android/iOS).
    // Ex.: 'https://ntfy.sh/um-nome-dificil-de-adivinhar-123' ou o teu ntfy self-hosted.
    'ntfy_url'   => '',

    // Só se o teu servidor ntfy tiver autenticação (token de acesso).
    'ntfy_token' => '',

    // Texto aleatório usado para "baralhar" os IPs guardados (muda para algo só teu).
    'ip_salt'    => 'muda-isto-para-algo-aleatorio',
];
