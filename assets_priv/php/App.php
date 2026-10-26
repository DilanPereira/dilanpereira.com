<?php
declare(strict_types=1);

/**
 * ============================================================================
 *  App.php — estado global do pedido atual (língua e página)
 * ============================================================================
 *
 *  Várias partes do site precisam de saber "em que língua estamos" e "em que
 *  página estamos" (para construir links, o seletor de idioma, as tags do
 *  Google...). Em vez de espalhar variáveis globais ($GLOBALS['lang'], etc.),
 *  esse estado fica guardado aqui, num único sítio, com tipos definidos.
 *
 *  Como se usa:
 *    App::lang()          -> 'pt'
 *    App::defaultLang()   -> 'en'  (a primeira língua de languages.php)
 *    App::languages()     -> ['en' => ['name' => 'English', 'project' => 'project'], ...]
 *    App::route()         -> ['page' => 'project', 'project' => [...]]
 *
 *  Quem preenche o estado:
 *    - router.php    : App::requestLang('pt') (língua vinda do endereço) e App::setRoute(...)
 *    - lang_init.php : App::boot(...) com a lista de línguas e a língua final
 *
 *  "final" impede que a classe seja estendida; todos os métodos são estáticos
 *  porque só existe um pedido de cada vez (padrão "registry" simples).
 * ============================================================================
 */
final class App
{
    /** Língua pedida pelo endereço (null = o pedido não passou pelo router). */
    private static ?string $requestedLang = null;

    /** Língua efetiva da página atual. */
    private static string $lang = 'en';

    /** Língua principal (sem prefixo no endereço). */
    private static string $defaultLang = 'en';

    /** @var array<string, array{name: string, project: string}> Lista de línguas */
    private static array $languages = [];

    /** @var array{page: string, project?: array} Página atual */
    private static array $route = ['page' => 'home'];

    /** O router chama isto ANTES de carregar a configuração, com a língua do endereço. */
    public static function requestLang(string $lang): void
    {
        self::$requestedLang = $lang;
    }

    public static function requestedLang(): ?string
    {
        return self::$requestedLang;
    }

    /**
     * Fixa a lista de línguas e a língua atual. Chamado uma vez, no lang_init.php.
     * Se a língua não existir na lista, usa a principal (nunca confiar em dados de fora).
     */
    public static function boot(array $languages, string $lang): void
    {
        self::$languages   = $languages;
        self::$defaultLang = (string)array_key_first($languages);
        self::$lang        = isset($languages[$lang]) ? $lang : self::$defaultLang;
    }

    public static function lang(): string
    {
        return self::$lang;
    }

    public static function defaultLang(): string
    {
        return self::$defaultLang;
    }

    /** @return array<string, array{name: string, project: string}> */
    public static function languages(): array
    {
        return self::$languages;
    }

    /** Indica se um código ('pt', 'en'...) é uma língua do site. */
    public static function isLanguage(string $code): bool
    {
        return isset(self::$languages[$code]);
    }

    /** O router diz em que página estamos (início, projeto ou 404). */
    public static function setRoute(array $route): void
    {
        self::$route = $route;
    }

    public static function route(): array
    {
        return self::$route;
    }
}
