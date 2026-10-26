/**
 * ============================================================================
 *  script.js — comportamento das páginas (início e projetos)
 * ============================================================================
 *  1. Animação de entrada dos blocos (.reveal) quando aparecem no ecrã
 *  2. Menu de telemóvel: fecha ao escolher um link ou ao fazer scroll
 *  3. Scroll suave para as secções (#sobre, #projetos...)
 *  4. Botão "voltar ao topo" e secção atual destacada no menu
 *  5. Botão "ver percurso completo"
 *
 *  Notas:
 *  - Carregado com "defer": corre depois de o HTML estar pronto, por isso não
 *    é preciso esperar pelo evento DOMContentLoaded.
 *  - Tudo fica dentro de uma função (IIFE) para não criar variáveis globais.
 *  - Não há "onclick" no HTML: a Content-Security-Policy do site (config.php)
 *    bloqueia JavaScript escrito dentro do HTML; os eventos são ligados aqui.
 * ============================================================================
 */
(() => {
    'use strict';

    const menuEl = document.getElementById('menu');
    const header = document.querySelector('header');
    const backToTopBtn = document.getElementById('backToTop');

    // Quem preferir menos animações (definição do sistema) não tem scroll suave
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const scrollBehavior = reduceMotion ? 'auto' : 'smooth';


    /* ------------------------------------------------------------------------
     * 1. Animação de entrada
     * ------------------------------------------------------------------------
     * O IntersectionObserver avisa quando um elemento entra no ecrã (15% visível).
     * Nesse momento recebe a classe "visible" (o CSS faz a animação) e deixa de
     * ser observado, porque só anima uma vez.
     * Browsers sem IntersectionObserver mostram tudo logo.
     */
    const revealEls = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        revealEls.forEach((el) => observer.observe(el));
    } else {
        revealEls.forEach((el) => el.classList.add('visible'));
    }


    /* ------------------------------------------------------------------------
     * 2. Menu de telemóvel (componente "collapse" do Bootstrap)
     * ------------------------------------------------------------------------
     * Abaixo de 992px (breakpoint "lg") o menu fica atrás do botão "hambúrguer".
     * Fecha-se sozinho ao carregar num link e ao fazer scroll com ele aberto.
     */
    const menu = (menuEl && window.bootstrap)
        ? bootstrap.Collapse.getOrCreateInstance(menuEl, { toggle: false })
        : null;
    const menuIsOpen = () => menuEl !== null && menuEl.classList.contains('show');

    document.querySelectorAll('.nav-link').forEach((link) => {
        link.addEventListener('click', () => {
            if (menu && menuIsOpen() && window.innerWidth < 992) {
                menu.hide();
            }
        });
    });


    /* ------------------------------------------------------------------------
     * 3. Scroll suave para as secções
     * ------------------------------------------------------------------------
     * Links do tipo "#sobre": em vez do salto seco, desliza até à secção,
     * descontando a altura do cabeçalho fixo (para o título não ficar tapado).
     * Depois tira o "#sobre" do endereço, para o endereço ficar limpo.
     */
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', (event) => {
            const id = anchor.getAttribute('href').slice(1);
            const target = id ? document.getElementById(id) : null;
            if (!target) {
                return; // link "#" ou secção inexistente: comportamento normal
            }

            event.preventDefault();
            // No telemóvel, se o clique veio do menu aberto, o cabeçalho ainda inclui
            // o menu (que está a fechar): desconta-o, senão a página parava muito acima.
            // Fechado, o menu mede 0; no computador fica na mesma linha do logótipo.
            const openMenuHeight = (menuEl && window.innerWidth < 992) ? menuEl.offsetHeight : 0;
            const headerHeight = header ? header.offsetHeight - openMenuHeight : 0;
            const top = target.getBoundingClientRect().top + window.scrollY - (headerHeight - 20);
            window.scrollTo({ top, behavior: scrollBehavior });

            // replaceState (e não pushState): o botão "voltar" do browser não fica cheio de entradas iguais
            history.replaceState(null, '', window.location.pathname + window.location.search);
        });
    });


    /* ------------------------------------------------------------------------
     * 4. Scroll da página: botão "voltar ao topo", fecho do menu e secção atual
     * ------------------------------------------------------------------------
     * Secção atual ("scrollspy"): o link do menu da secção que está no ecrã
     * fica destacado (classe "active" + aria-current, o CSS mostra-o a escuro
     * com um traço dourado por baixo). Só existe na página inicial, onde os
     * links do menu são "#secção"; nas páginas de projeto não há nada a destacar.
     *
     * Uma secção conta como "atual" quando o seu início já passou um pouco do
     * cabeçalho (35% da altura do ecrã). No fundo da página, a última secção
     * (contacto) fica sempre ativa, mesmo que seja curta demais para chegar lá.
     *
     * O evento "scroll" dispara dezenas de vezes por segundo. Para não pesar,
     * o trabalho é agrupado com requestAnimationFrame (no máximo uma vez por
     * frame) e o evento é "passive" (o browser não tem de esperar pelo script).
     */
    let ticking = false;
    let menuScrollStart = null;   // posição quando o menu foi visto aberto pela 1.ª vez

    // Pares [link do menu, secção], pela ordem em que aparecem na página
    const spyItems = [...document.querySelectorAll('#menu .nav-link[href^="#"]')]
        .map((link) => [link, document.getElementById(link.getAttribute('href').slice(1))])
        .filter(([, section]) => section !== null);
    let currentSpy = null;

    const updateActiveLink = () => {
        if (spyItems.length === 0) return;

        const line = (header ? header.offsetHeight : 0) + window.innerHeight * 0.35;
        const atBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2;

        let active = spyItems[0][0];
        if (atBottom) {
            active = spyItems[spyItems.length - 1][0];
        } else {
            for (const [link, section] of spyItems) {
                if (section.getBoundingClientRect().top <= line) active = link;
            }
        }

        if (active === currentSpy) return;   // nada mudou: não mexe no HTML
        currentSpy = active;
        spyItems.forEach(([link]) => {
            const on = link === active;
            link.classList.toggle('active', on);
            if (on) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
        });
    };

    const onScroll = () => {
        const y = window.scrollY;

        if (backToTopBtn) {
            backToTopBtn.classList.toggle('show', y > 400);
        }

        updateActiveLink();

        // Menu aberto e a pessoa já deslizou mais de 60px: fecha-o
        if (menu && menuIsOpen()) {
            if (menuScrollStart === null) {
                menuScrollStart = y;
            } else if (Math.abs(y - menuScrollStart) > 60) {
                menu.hide();
                menuScrollStart = null;
            }
        } else {
            menuScrollStart = null;
        }

        ticking = false;
    };

    window.addEventListener('scroll', () => {
        if (!ticking) {
            ticking = true;
            window.requestAnimationFrame(onScroll);
        }
    }, { passive: true });

    // Estado inicial (ex.: a página abriu já a meio, em /pt/#contacto)
    // e quando o tamanho da janela muda
    updateActiveLink();
    window.addEventListener('resize', updateActiveLink, { passive: true });

    if (backToTopBtn) {
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: scrollBehavior });
        });
    }


    /* ------------------------------------------------------------------------
     * 5. "Ver percurso completo"
     * ------------------------------------------------------------------------
     * Os itens menos relevantes do percurso (.extra-exp-item) começam escondidos.
     * O botão mostra/esconde-os com uma pequena animação:
     *   - mostrar: tira o "d-none" e, logo a seguir, acrescenta "show-item"
     *     (o CSS anima a opacidade/altura);
     *   - esconder: tira "show-item" e só põe "d-none" depois de a animação acabar.
     * Os textos do botão vêm dos atributos data-text-show / data-text-hide
     * (já traduzidos pelo PHP).
     */
    const toggleBtn = document.getElementById('toggle-percurso-btn');
    const extraItems = document.querySelectorAll('.extra-exp-item');
    const ANIMATION_MS = 400;   // igual à duração da transição no style.css

    if (toggleBtn && extraItems.length === 0) {
        toggleBtn.hidden = true;   // nada para mostrar: o botão não faz sentido
    } else if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const show = toggleBtn.getAttribute('aria-expanded') !== 'true';

            extraItems.forEach((item) => {
                const wrapper = item.querySelector('.timeline-collapse-wrapper');
                if (show) {
                    if (wrapper) wrapper.classList.remove('d-none');
                    // Espera um frame para o browser "ver" o elemento antes de animar
                    window.requestAnimationFrame(() => {
                        window.requestAnimationFrame(() => item.classList.add('show-item'));
                    });
                } else {
                    item.classList.remove('show-item');
                    window.setTimeout(() => {
                        if (wrapper && !item.classList.contains('show-item')) {
                            wrapper.classList.add('d-none');
                        }
                    }, ANIMATION_MS);
                }
            });

            toggleBtn.setAttribute('aria-expanded', String(show));
            toggleBtn.textContent = show ? toggleBtn.dataset.textHide : toggleBtn.dataset.textShow;
        });
    }
})();
