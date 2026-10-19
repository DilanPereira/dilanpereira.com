<?php
/**
 * Línguas do site. Para adicionar uma, acrescenta uma linha:
 *   'fr' => ['name' => 'Français', 'project' => 'projet'],
 *   - 'name'    nome mostrado no seletor de idioma
 *   - 'project' palavra usada no endereço dos projetos (/fr/projet/nome-do-projeto)
 * e depois as traduções ("fr": "...") nos ficheiros JSON de assets_priv/lang/.
 * Textos ainda sem tradução aparecem em inglês.
 *
 * A primeira língua é a principal: fica na raiz do site (dilanpereira.com/);
 * as outras ficam com prefixo (dilanpereira.com/pt/).
 */
return [
    'en' => ['name' => 'English',   'project' => 'project'],
    'pt' => ['name' => 'Português', 'project' => 'projeto'],
];
