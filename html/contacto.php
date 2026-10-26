<?php
declare(strict_types=1);

/**
 * ============================================================================
 *  contacto.php — recebe o formulário de contacto da página inicial
 * ============================================================================
 *  Não é uma página: só recebe o envio (POST), verifica-o, guarda a mensagem,
 *  envia a notificação para o telemóvel e redireciona de volta para a secção
 *  de contacto, na língua em que a pessoa estava (ex.: /pt/#contacto).
 *  O resultado (sucesso/erro) é passado à página inicial através da sessão
 *  (padrão "Post/Redirect/Get").
 *
 *  Como é um ficheiro real na pasta html/, não passa pelo router.php.
 *  A língua vem da sessão (guardada pelo router na última página vista).
 *
 *  As verificações em detalhe estão em assets_priv/php/contact_helper.php.
 * ============================================================================
 */
require_once __DIR__ . '/../assets_priv/php/config.php';          // sessão, $isWIP, língua
require_once __DIR__ . '/../assets_priv/php/urls.php';            // urlHome()
require_once __DIR__ . '/../assets_priv/php/contact_helper.php';  // validação, gravação, ntfy

/** @var bool $isWIP */

/**
 * Guarda o resultado na sessão e volta à secção de contacto.
 *   $status : 'ok' | 'invalid' | 'rate' | 'expired' | 'error' (escolhe a mensagem mostrada)
 *   $old    : o que a pessoa escreveu (para não ter de escrever tudo outra vez se houver erro)
 *   $errors : campos com erro (ficam a vermelho)
 * O código 303 diz ao browser para abrir a página com GET (assim, carregar F5
 * depois não reenvia o formulário).
 */
function contactFinish(string $status, array $old = [], array $errors = []): never
{
    $_SESSION['contact_flash'] = ['status' => $status, 'old' => $old, 'errors' => $errors];
    header('Location: ' . urlHome() . '#contacto', true, 303);
    exit;
}

// Em manutenção, ou se alguém abrir este endereço diretamente (sem enviar o formulário),
// volta simplesmente à página inicial
if ($isWIP || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . urlHome(), true, 303);
    exit;
}

// 1. Token CSRF (impede que outros sites submetam o formulário em nome do visitante).
//    hash_equals compara em tempo constante (não deixa adivinhar o token por tempos de resposta).
$token = $_POST['csrf'] ?? '';
$saved = $_SESSION['contact_csrf'] ?? '';
if (!is_string($token) || !is_string($saved) || $saved === '' || !hash_equals($saved, $token)) {
    contactFinish('expired');
}

// 2. Armadilhas para bots: campo escondido preenchido ou envio rápido demais.
//    Respondemos "sucesso" para o bot não perceber que foi apanhado.
$shownAt = (int)($_SESSION['contact_form_shown_at'] ?? 0);
if (!empty($_POST['website']) || $shownAt === 0 || time() - $shownAt < CONTACT_MIN_SECONDS) {
    contactFinish('ok');
}

// 3. Validação
[$data, $errors] = contactValidate($_POST);
if ($errors) {
    contactFinish('invalid', $data, $errors);
}

// 4. Limite de envios por IP
$ipHash = contactIpHash(contactClientIp());
if (!contactRateLimitAllows($ipHash)) {
    contactFinish('rate', $data);
}

// 5. Guardar e notificar
$message = [
    'data'     => date('c'),
    'nome'     => $data['nome'],
    'email'    => $data['email'],
    'assunto'  => $data['assunto'],
    'mensagem' => $data['mensagem'],
    'idioma'   => App::lang(),
    'ip_hash'  => $ipHash,
];

if (!contactSaveMessage($message)) {
    logCustomError('Não foi possível guardar a mensagem de contacto (verificar permissões de escrita em assets_priv/data)');
    contactFinish('error', $data);
}

contactNotify($message);

// Novo token depois de um envio com sucesso (um token só serve para uma mensagem)
unset($_SESSION['contact_csrf']);
contactFinish('ok');
