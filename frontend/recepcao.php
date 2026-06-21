<?php
session_start();
if (!isset($_SESSION['id_usuario'])) { header("Location: login.html"); exit; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recepção — Mini Hotel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen" style="background-color:#fdf6ec;">

    <!-- NAVBAR -->
    <header class="sticky top-0 z-10 shadow-lg" style="background-color:#3d1a00;">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color:#b45309;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5M3.75 3h16.5M4.5 3v18M19.5 3v18M9 7.5h1.5m4.5 0H16.5M9 12h1.5m4.5 0H16.5M9 16.5h1.5m4.5 0H16.5" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-white font-bold text-base leading-tight">Mini Hotel</h1>
                    <p class="text-amber-400 text-xs">Recepção</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-amber-200 text-sm hidden sm:inline">Olá, <strong><?= htmlspecialchars($_SESSION['nome']) ?></strong></span>
                <?php if ($_SESSION['nivel'] == 'admin'): ?>
                    <a href="painel.php"
                        class="text-sm font-medium px-3 py-1.5 rounded-lg transition text-amber-900"
                        style="background-color:#f59e0b;" onmouseover="this.style.backgroundColor='#d97706'" onmouseout="this.style.backgroundColor='#f59e0b'">
                        Painel Gerencial
                    </a>
                <?php endif; ?>
                <a href="login.html" class="text-red-400 hover:text-red-300 text-sm font-medium transition">Sair</a>
            </div>
        </div>
    </header>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="max-w-7xl mx-auto px-4 py-8">

        <!-- Legenda de status -->
        <div class="flex flex-wrap items-center gap-5 mb-6">
            <h2 class="text-stone-700 font-semibold text-lg mr-2">Quartos</h2>
            <div class="flex items-center gap-1.5 text-sm text-stone-500">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span> Livre
            </div>
            <div class="flex items-center gap-1.5 text-sm text-stone-500">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span> Ocupado
            </div>
            <div class="flex items-center gap-1.5 text-sm text-stone-500">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span> Em Limpeza
            </div>
        </div>

        <!-- Grid de quartos (preenchido via JS) -->
        <div id="listaQuartos" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"></div>
    </main>

    <!-- ================================================ -->
    <!-- MODAL: CHECK-IN                                  -->
    <!-- ================================================ -->
    <div id="modalCheckin" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color:rgba(0,0,0,0.65);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-stone-100">
                <h2 id="tituloModal" class="text-lg font-bold text-stone-800">Entrada — Quarto</h2>
                <button onclick="fecharModal()" class="text-stone-400 hover:text-stone-600 text-2xl leading-none transition">&times;</button>
            </div>

            <form id="formCheckin" class="px-6 py-5 space-y-3">
                <input type="hidden" id="quarto_selecionado">

                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1">Nome Completo *</label>
                    <input type="text" id="nome" required
                        class="w-full px-3 py-2.5 border border-stone-200 rounded-lg text-stone-800 bg-stone-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white transition text-sm">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1">CPF *</label>
                        <input type="text" id="cpf" placeholder="000.000.000-00" maxlength="14" required
                            class="w-full px-3 py-2.5 border border-stone-200 rounded-lg text-stone-800 bg-stone-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white transition text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1">Telefone *</label>
                        <input type="text" id="telefone" placeholder="(00) 00000-0000" maxlength="15" required
                            class="w-full px-3 py-2.5 border border-stone-200 rounded-lg text-stone-800 bg-stone-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white transition text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1">Nascimento *</label>
                        <input type="text" id="data_nascimento" placeholder="DD/MM/AAAA" maxlength="10" required
                            class="w-full px-3 py-2.5 border border-stone-200 rounded-lg text-stone-800 bg-stone-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white transition text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1">Tempo est. (h) *</label>
                        <input type="number" id="tempo_estimado" min="1" required
                            class="w-full px-3 py-2.5 border border-stone-200 rounded-lg text-stone-800 bg-stone-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white transition text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1">Placa do Veículo (opcional)</label>
                    <input type="text" id="placa_veiculo" placeholder="ABC-1234"
                        class="w-full px-3 py-2.5 border border-stone-200 rounded-lg text-stone-800 bg-stone-50 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white transition text-sm">
                </div>

                <button type="submit"
                    class="w-full text-white font-semibold py-3 rounded-xl transition mt-2 active:scale-95"
                    style="background-color:#16a34a;" onmouseover="this.style.backgroundColor='#15803d'" onmouseout="this.style.backgroundColor='#16a34a'">
                    Confirmar Check-in
                </button>
                <div id="mensagemModal" class="text-center text-sm font-medium mt-1 hidden"></div>
            </form>
        </div>
    </div>

    <!-- ================================================ -->
    <!-- MODAL: CHECK-OUT                                 -->
    <!-- ================================================ -->
    <div id="modalCheckout" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color:rgba(0,0,0,0.65);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-stone-100">
                <h2 class="text-lg font-bold text-stone-800">Check-out — Quarto <span id="numQuartoCheckout"></span></h2>
                <button onclick="fecharModalCheckout()" class="text-stone-400 hover:text-stone-600 text-2xl leading-none transition">&times;</button>
            </div>

            <div class="px-6 py-5">
                <div id="detalhesCheckout" class="bg-amber-50 rounded-xl p-4 space-y-2.5 border border-amber-100">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-stone-500">Hóspede</span>
                        <span class="font-semibold text-stone-800" id="checkoutNome">—</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-stone-500">Entrada</span>
                        <span class="font-medium text-stone-700" id="checkoutEntrada">—</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-stone-500">Tempo estimado</span>
                        <span class="font-medium text-stone-700"><span id="checkoutTempo">0</span>h</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-stone-500">Tempo cobrado</span>
                        <span class="font-medium text-amber-700" id="checkoutReal">0</span>
                    </div>
                    <div class="border-t border-amber-200 pt-3 mt-1 flex justify-between items-center">
                        <span class="font-bold text-stone-700">Total a Pagar</span>
                        <span class="text-2xl font-bold text-red-600">R$ <span id="checkoutTotal">0,00</span></span>
                    </div>
                </div>

                <input type="hidden" id="id_locacao_checkout">
                <input type="hidden" id="quarto_checkout">

                <button onclick="confirmarCheckout()"
                    class="w-full mt-4 text-white font-semibold py-3 rounded-xl transition active:scale-95"
                    style="background-color:#dc2626;" onmouseover="this.style.backgroundColor='#b91c1c'" onmouseout="this.style.backgroundColor='#dc2626'">
                    Finalizar e Enviar para Limpeza
                </button>
                <div id="mensagemCheckout" class="text-center text-sm font-medium mt-2 hidden"></div>
            </div>
        </div>
    </div>

    <!-- ================================================ -->
    <!-- MODAL: LIMPEZA                                   -->
    <!-- ================================================ -->
    <div id="modalLimpeza" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color:rgba(0,0,0,0.65);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm text-center">
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-stone-100">
                <h2 class="text-lg font-bold text-amber-700">Quarto <span id="numQuartoLimpeza"></span></h2>
                <button onclick="fecharModalLimpeza()" class="text-stone-400 hover:text-stone-600 text-2xl leading-none transition">&times;</button>
            </div>
            <div class="px-6 py-6">
                <div class="w-14 h-14 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-stone-700 font-medium mb-1">A limpeza deste quarto foi concluída?</p>
                <p class="text-stone-400 text-sm mb-5">O quarto voltará a ficar disponível para locação.</p>

                <input type="hidden" id="quarto_limpeza_id">
                <button onclick="confirmarLimpeza()"
                    class="w-full text-amber-900 font-semibold py-3 rounded-xl transition active:scale-95"
                    style="background-color:#f59e0b;" onmouseover="this.style.backgroundColor='#d97706'" onmouseout="this.style.backgroundColor='#f59e0b'">
                    Sim, Liberar Quarto
                </button>
            </div>
        </div>
    </div>

    <script src="app.js"></script>
</body>
</html>
