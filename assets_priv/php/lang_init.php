<?php
declare(strict_types=1);

/**
 * ============================================================================
 *  lang_init.php — língua atual e textos traduzidos
 * ============================================================================
 *  No fim deste ficheiro ficam disponíveis, para os templates:
 *
 *    $lang          língua atual ('en', 'pt'...)
 *    $languages     lista de línguas (de languages.php)
 *    $languageNames ['en' => 'English', 'pt' => 'Português']  (seletor de idioma)
 *    $projects      projetos de projects.json, já na língua atual
 *    $educations    formação/experiência de education.json, já na língua atual
 *    __('chave')    texto de texts.json na língua atual
 *
 *  Os ficheiros JSON guardam cada texto em todas as línguas:
 *      "titulo": { "pt": "Olá", "en": "Hello" }
 *  e loadLanguageData() devolve só a língua atual:
 *      "titulo": "Olá"
 * ============================================================================
 */

/** @var array<string, array{name: string, project: string}> $languages */
$languages = require __DIR__ . '/languages.php';

/*
 * Língua atual, por esta ordem de prioridade:
 *   1. A do endereço (/pt/... -> pt), que o router.php indicou com App::requestLang().
 *   2. Pedidos que não passam pelo router (ex.: contacto.php, quando o formulário
 *      é enviado) usam a língua da última página vista, guardada na sessão.
 *   3. Se nada disso existir, a língua principal (a primeira da lista).
 * O App::boot() confirma que é uma língua válida.
 */
App::boot($languages, App::requestedLang() ?? ($_SESSION['lang'] ?? (string)array_key_first($languages)));

$lang          = App::lang();
$languageNames = array_map(static fn(array $l): string => $l['name'], $languages);


/**
 * Lê um ficheiro de assets_priv/lang/ e devolve o conteúdo na língua pedida.
 *
 *   loadLanguageData('projects', 'pt')
 *
 * Percorre o JSON todo (recursivamente): sempre que encontra um objeto com
 * chaves de língua ({"pt": ..., "en": ...}), troca-o pelo valor da língua pedida.
 * Se essa tradução faltar, usa o inglês; se também faltar, o primeiro valor que houver.
 *
 * O resultado fica em cache (variável static) durante o pedido, para não ler
 * e processar o mesmo ficheiro duas vezes.
 */
function loadLanguageData(string $prefix, string $lang): array
{
    static $cache = [];
    $cacheKey = "{$prefix}_{$lang}";

    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $filePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . "{$prefix}.json";
    if (!is_file($filePath)) {
        logCustomError("Ficheiro de idioma não encontrado: {$prefix}.json");
        return $cache[$cacheKey] = [];
    }

    $languageKeys = array_flip(array_keys(App::languages()));

    $translate = static function (mixed $data) use (&$translate, $lang, $languageKeys): mixed {
        if (!is_array($data)) {
            return $data; // texto, número, etc.: fica como está
        }

        // Objeto de traduções, ex.: {"pt": "...", "en": "..."}
        if (array_intersect_key($data, $languageKeys)) {
            return $data[$lang] ?? $data['en'] ?? reset($data);
        }

        // Lista ou objeto normal: traduz cada elemento
        return array_map($translate, $data);
    };

    return $cache[$cacheKey] = $translate(parseJsonFile($filePath));
}


// Dados usados pelas páginas
$projects   = loadLanguageData('projects', $lang);
$educations = loadLanguageData('education', $lang);


/**
 * Texto traduzido de assets_priv/lang/texts.json.
 *
 *   __('headerHome', 'Home')  ->  'Início' (em pt)
 *
 * O segundo parâmetro é o texto a usar se a chave não existir no JSON.
 * Nota: devolve o texto SEM escapar, porque alguns textos têm HTML de propósito
 * (ex.: <strong> no "aboutText1"). Ao usá-lo dentro de atributos, usar e(__(...)).
 */
function __(string $key, string $default = ''): string
{
    $texts = loadLanguageData('texts', App::lang());
    $value = $texts[$key] ?? $default;
    return is_string($value) ? $value : $default;
}
