<?php
declare(strict_types=1);

/**
 * ============================================================================
 *  contact_helper.php — funções do formulário de contacto
 * ============================================================================
 *  - As mensagens são guardadas em assets_priv/data/mensagens.jsonl (formato
 *    "JSON Lines": uma mensagem por linha), fora da pasta pública, com bloqueio
 *    de ficheiro para dois envios ao mesmo tempo não se estragarem um ao outro.
 *  - Limite de envios por IP (ratelimit.json, na mesma pasta).
 *  - Notificação opcional para o telemóvel via ntfy (configurada no local_config.php).
 *
 *  Usado por: html/contacto.php (recebe o envio) e pages/home.php (token CSRF).
 * ============================================================================
 */

/** Tamanho máximo de cada campo (em caracteres). Tem de bater certo com os "maxlength" do HTML. */
const CONTACT_LIMITS = [
    'nome'     => 100,
    'email'    => 254,
    'assunto'  => 150,
    'mensagem' => 5000,
];
const CONTACT_MIN_MESSAGE    = 10;   // caracteres mínimos da mensagem
const CONTACT_MIN_SECONDS    = 3;    // envios mais rápidos que isto são de bots
const CONTACT_MAX_PER_WINDOW = 3;    // mensagens por IP...
const CONTACT_WINDOW_SECONDS = 3600; // ...por hora

/** Pasta dos dados privados (criada na primeira utilização). */
function contactDataDir(): string
{
    $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data';
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    return $dir;
}

/**
 * Configuração local opcional (local_config.php, não vai para o Git):
 * endereço e token do ntfy, "sal" do hash dos IPs.
 * Lida uma só vez por pedido (cache em variável static).
 */
function contactLocalConfig(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $file = __DIR__ . DIRECTORY_SEPARATOR . 'local_config.php';
    if (!is_file($file)) {
        return $config = [];
    }
    // Sem "<?php" no início, o PHP imprimia o ficheiro (com o token) em vez de o executar
    if (strncmp(ltrim((string)file_get_contents($file, false, null, 0, 64)), '<?php', 5) !== 0) {
        logCustomError('local_config.php ignorado: o ficheiro tem de começar por <?php');
        return $config = [];
    }
    $loaded = require $file;
    return $config = is_array($loaded) ? $loaded : [];
}

/** Token CSRF guardado na sessão (criado uma vez, reutilizado até haver um envio com sucesso). */
function contactCsrfToken(): string
{
    if (empty($_SESSION['contact_csrf']) || !is_string($_SESSION['contact_csrf'])) {
        $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['contact_csrf'];
}

/** Marca quando o formulário foi mostrado (para apanhar bots que submetem logo). */
function contactMarkFormShown(): void
{
    $_SESSION['contact_form_shown_at'] = time();
}

/**
 * IP real do visitante. O pedido passa por Cloudflare Tunnel -> Nginx Proxy Manager -> contentor,
 * por isso o REMOTE_ADDR é sempre um IP interno do Docker. Nesse caso usamos, por esta ordem:
 * CF-Connecting-IP (Cloudflare), o primeiro IP do X-Forwarded-For e o X-Real-IP.
 * Se o pedido vier de um IP público (sem proxy), esses cabeçalhos são ignorados,
 * porque aí qualquer pessoa os podia inventar para fugir ao limite de envios.
 */
function contactClientIp(): string
{
    $remote    = (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    $publicIp  = static fn(string $ip): bool =>
        filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;

    if (!$publicIp($remote)) {
        $candidates = [
            (string)($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''),
            trim(explode(',', (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''))[0]),
            (string)($_SERVER['HTTP_X_REAL_IP'] ?? ''),
        ];
        foreach ($candidates as $ip) {
            if ($publicIp($ip)) {
                return $ip;
            }
        }
    }
    return $remote;
}

/** O IP é guardado só como hash (RGPD: não precisamos do IP em claro). */
function contactIpHash(string $ip): string
{
    $salt = (string)(contactLocalConfig()['ip_salt'] ?? 'portfolio-dilan');
    return substr(hash('sha256', $salt . '|' . $ip), 0, 16);
}

/**
 * Regista uma tentativa e devolve false se o IP já passou o limite.
 * Usa um ficheiro JSON pequeno com bloqueio exclusivo (flock): se chegarem dois
 * pedidos ao mesmo tempo, o segundo espera que o primeiro acabe.
 * Tentativas mais antigas que a janela de tempo são apagadas a cada pedido.
 */
function contactRateLimitAllows(string $ipHash): bool
{
    $file = contactDataDir() . DIRECTORY_SEPARATOR . 'ratelimit.json';
    $fp = @fopen($file, 'c+');
    if ($fp === false) {
        return true; // se não der para abrir o ficheiro, não bloqueamos visitantes legítimos
    }

    try {
        flock($fp, LOCK_EX);
        $data = json_decode((string)stream_get_contents($fp) ?: '{}', true);
        $data = is_array($data) ? $data : [];

        $limit = time() - CONTACT_WINDOW_SECONDS;
        foreach ($data as $hash => $times) {
            $recent = array_values(array_filter((array)$times, static fn($t): bool => (int)$t > $limit));
            if ($recent) {
                $data[$hash] = $recent;
            } else {
                unset($data[$hash]);
            }
        }

        $allowed = count($data[$ipHash] ?? []) < CONTACT_MAX_PER_WINDOW;
        if ($allowed) {
            $data[$ipHash][] = time();
        }

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, (string)json_encode($data));
        fflush($fp);

        return $allowed;
    } finally {
        // Corre sempre, mesmo que algo acima falhe: o ficheiro nunca fica bloqueado
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

/** Limpa o texto: tira espaços nas pontas e caracteres de controlo (mantém quebras de linha e tabs). */
function contactClean(string $value): string
{
    return preg_replace('/[^\P{C}\n\t]/u', '', trim($value)) ?? '';
}

/**
 * Valida os campos. Devolve [dados limpos, lista de campos com erro].
 * Campos que não sejam texto (ex.: alguém envia "nome[]=x") contam como vazios.
 */
function contactValidate(array $input): array
{
    $data = [];
    foreach (array_keys(CONTACT_LIMITS) as $field) {
        $value = $input[$field] ?? '';
        $data[$field] = contactClean(is_string($value) ? $value : '');
    }
    // Nome, email e assunto ficam numa só linha
    foreach (['nome', 'email', 'assunto'] as $field) {
        $data[$field] = (string)preg_replace('/\s+/u', ' ', $data[$field]);
    }

    $errors = [];
    foreach (CONTACT_LIMITS as $field => $max) {
        if ($data[$field] === '' || mb_strlen($data[$field]) > $max) {
            $errors[] = $field;
        }
    }
    if (!in_array('email', $errors, true) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'email';
    }
    if (!in_array('mensagem', $errors, true) && mb_strlen($data['mensagem']) < CONTACT_MIN_MESSAGE) {
        $errors[] = 'mensagem';
    }

    return [$data, array_values(array_unique($errors))];
}

/** Acrescenta a mensagem ao ficheiro mensagens.jsonl. */
function contactSaveMessage(array $message): bool
{
    $file = contactDataDir() . DIRECTORY_SEPARATOR . 'mensagens.jsonl';
    $json = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) {
        return false;
    }
    return @file_put_contents($file, $json . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Notificação opcional via ntfy (https://ntfy.sh ou self-hosted).
 * Só corre se 'ntfy_url' estiver definido no local_config.php.
 * Uma falha não impede o envio (a mensagem já está guardada), mas fica registada no log.
 */
function contactNotify(array $message): void
{
    $config = contactLocalConfig();
    if (empty($config['ntfy_url'])) {
        return;
    }

    $headers = [
        // Codificado (RFC 2047) para nomes com acentos chegarem bem ao telemóvel.
        // Em base64 também não há forma de o nome "partir" o cabeçalho com quebras de linha.
        'Title: =?UTF-8?B?' . base64_encode('Nova mensagem de ' . mb_substr($message['nome'], 0, 60)) . '?=',
        'Tags: envelope',
        'Content-Type: text/plain; charset=utf-8',
    ];
    if (!empty($config['ntfy_token'])) {
        $headers[] = 'Authorization: Bearer ' . $config['ntfy_token'];
    }

    $body = $message['assunto'] . "\n" . $message['email'] . "\n\n" . mb_substr($message['mensagem'], 0, 500);
    $context = stream_context_create(['http' => [
        'method'          => 'POST',
        'header'          => implode("\r\n", $headers),
        'content'         => $body,
        'timeout'         => 4,
        'ignore_errors'   => true,
        'follow_location' => 0,   // não seguir redirecionamentos (o token não deve ir parar a outro sítio)
    ]]);

    $response = @file_get_contents((string)$config['ntfy_url'], false, $context);

    // Com ignore_errors, um 401/403 não devolve false: verificamos o código HTTP.
    // PHP 8.4+ tem http_get_last_response_headers(); versões antigas usam $http_response_header.
    $responseHeaders = function_exists('http_get_last_response_headers')
        ? (http_get_last_response_headers() ?? [])
        : ($http_response_header ?? []);
    $statusLine = (string)($responseHeaders[0] ?? '');
    $ok = $response !== false && preg_match('#^HTTP/\S+\s+2\d\d#', $statusLine) === 1;
    if (!$ok) {
        $detail = $statusLine !== ''
            ? $statusLine . ' ' . mb_substr((string)$response, 0, 200)
            : 'sem resposta (falha de ligação)';
        logCustomError('Falha na notificação ntfy: ' . $detail);
    }
}
