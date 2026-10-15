{{--
    Paginador reutilizable (client-side, Alpine.js + paginationItems).

    Parámetros (expresiones Alpine como CADENAS COMPLETAS, nunca fragmentos):
      current  (req) expresión que evalúa la página actual, p. ej. "currentPage.sales"
      total    (req) expresión que evalúa el total de páginas, p. ej. "getTotalPages('sales')"
      go       (req) expresión que evalúa una FLECHA de un argumento que navega
                     a la página indicada, p. ej. "(p) => goToPage('sales', p)"
      count    (opc) expresión con el total de registros filtrados; si se pasa,
                     se muestra el rango "Mostrando X–Y de Z" (solo >= 1200px)
      perPage  (opc, por defecto "perPage") expresión con los registros por página
      show     (opc) expresión de visibilidad; por defecto "{{ total }} > 1"
      label    (opc, por defecto "Paginación") etiqueta aria-label del <nav>
      class    (opc) clases extra para el contenedor (p. ej. "mt-3")

    Accesibilidad:
      - Los botones Anterior/Siguiente llevan atributo `disabled` REAL
        (imposible activarlos con ratón o teclado) + aria-label descriptivo.
      - La página activa expone aria-current="page".
      - Los puntos suspensivos son aria-hidden (la posición ya la comunica
        el indicador de página, que es una región role="status" (polite):
        se anuncian cambios de página pero NO el rango "Mostrando…", que
        queda fuera de la región live para evitar ruido al escribir filtros).
      - El foco de teclado es visible vía :focus-visible (app.css).
--}}
@php
    $perPageExpr = $perPage ?? 'perPage';
    $showExpr = $show ?? ($total . ' > 1');
    $navLabel = $label ?? 'Paginación';
@endphp
<div class="app-pagination {{ $class ?? '' }}" x-cloak x-show="{{ $showExpr }}">

    <div class="app-pagination__info">

        <span class="app-pagination__indicator" role="status">
            Página <span x-text="{{ $current }}"></span> de <span x-text="{{ $total }}"></span>
        </span>

        @isset($count)
        <span class="app-pagination__range">
            Mostrando <span x-text="((({{ $current }}) - 1) * ({{ $perPageExpr }})) + 1"></span>–<span x-text="Math.min(({{ $current }}) * ({{ $perPageExpr }}), ({{ $count }}))"></span> de <span x-text="{{ $count }}"></span>
        </span>
        @endisset

    </div>

    <nav class="app-pagination__nav" aria-label="{{ $navLabel }}">

        <ul class="pagination app-pagination__list mb-0">

            <li class="page-item app-pagination__step" :class="({{ $current }}) <= 1 ? 'disabled' : ''">
                <button type="button"
                    class="page-link"
                    :disabled="({{ $current }}) <= 1"
                    @click="({{ $go }})({{ $current }} - 1)"
                    aria-label="Página anterior"
                    title="Página anterior">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </button>
            </li>

            <template x-for="item in paginationItems({{ $current }}, {{ $total }})" :key="item.key">
                <li class="page-item"
                    :class="[item.type === 'gap' ? 'app-pagination__gap' : '',
                             item.type === 'page' &amp;&amp; item.n === ({{ $current }}) ? 'active' : '',
                             item.distant ? 'app-pagination__item--dim' : '']"
                    :aria-hidden="item.type === 'gap' ? 'true' : null">

                    <span class="page-link" x-show="item.type === 'gap'" aria-hidden="true">&hellip;</span>

                    <button type="button"
                        class="page-link"
                        x-show="item.type === 'page'"
                        @click="({{ $go }})(item.n)"
                        :aria-label="'Ir a la página ' + item.n"
                        :aria-current="item.n === ({{ $current }}) ? 'page' : null"
                        x-text="item.n"></button>

                </li>
            </template>

            <li class="page-item app-pagination__step" :class="({{ $current }}) >= ({{ $total }}) ? 'disabled' : ''">
                <button type="button"
                    class="page-link"
                    :disabled="({{ $current }}) >= ({{ $total }})"
                    @click="({{ $go }})({{ $current }} + 1)"
                    aria-label="Página siguiente"
                    title="Página siguiente">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
            </li>

        </ul>

    </nav>

</div>
