<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: recepcao.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Gerencial — Hostel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen" style="background-color:#f5f3ff;">

    <!-- NAVBAR -->
    <header class="sticky top-0 z-10 shadow-lg" style="background-color:#1e1b4b;">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color:#7c3aed;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-violet-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-white font-bold text-base leading-tight">Hostel</h1>
                    <p class="text-violet-300 text-xs">Painel Gerencial</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <button onclick="abrirModalHistorico()"
                    class="text-sm font-medium px-3 py-1.5 rounded-lg transition text-white border border-amber-700 hover:bg-amber-900">
                    Relatório de Locações
                </button>
                <a href="recepcao.php" class="text-violet-200 hover:text-violet-100 text-sm font-medium transition">Recepção</a>
                <a href="logout.php" class="text-red-400 hover:text-red-300 text-sm font-medium transition">Sair</a>
            </div>
        </div>
    </header>

    <!-- CONTEÚDO -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- COLUNA ESQUERDA: Faturamento + Categorias -->
            <div class="flex flex-col gap-6">

                <!-- Card Faturamento -->
                <div class="bg-white rounded-2xl shadow-sm border border-violet-100 p-6">
                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-widest mb-1">Faturamento do Turno</p>
                    <p id="valorFaturamento" class="text-4xl font-black mb-5" style="color:#7c3aed;">R$ 0,00</p>
                    <div class="flex flex-col gap-2">
                        <button onclick="abrirModalCaixa()"
                            class="w-full text-white font-semibold py-2.5 rounded-xl transition text-sm"
                            style="background-color:#dc2626;" onmouseover="this.style.backgroundColor='#b91c1c'" onmouseout="this.style.backgroundColor='#dc2626'">
                            Fechar Faturamento do Dia
                        </button>
                        <button onclick="abrirModalHistoricoCaixa()"
                            class="w-full font-semibold py-2.5 rounded-xl transition text-sm text-stone-700 bg-stone-100 hover:bg-stone-200">
                            Ver Fechamentos Diários
                        </button>
                        <button onclick="abrirModalHistoricoMes()"
                            class="w-full text-white font-semibold py-2.5 rounded-xl transition text-sm"
                            style="background-color:#1d4ed8;" onmouseover="this.style.backgroundColor='#1e40af'" onmouseout="this.style.backgroundColor='#1d4ed8'">
                            Fechamento Mensal
                        </button>
                    </div>
                </div>

                <!-- Card Categorias -->
                <div class="bg-white rounded-2xl shadow-sm border border-violet-100 p-6">
                    <h3 class="text-sm font-bold text-stone-700 uppercase tracking-wide mb-4">Gestão de Preços</h3>
                    <div id="mensagemPrecos" class="text-center text-sm font-medium mb-2 hidden"></div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-stone-100">
                                <th class="text-left py-2 text-xs text-stone-400 font-semibold uppercase">Categoria</th>
                                <th class="text-right py-2 text-xs text-stone-400 font-semibold uppercase">Novo R$/h</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody id="tabelaCategorias"></tbody>
                    </table>
                </div>
            </div>

            <!-- COLUNA DIREITA: Quartos -->
            <div class="lg:col-span-2 flex flex-col gap-6">

                <!-- Card Quartos Ativos -->
                <div class="bg-white rounded-2xl shadow-sm border border-violet-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-stone-700 uppercase tracking-wide">Gestão de Quartos</h3>
                        <button onclick="abrirModalNovoQuarto()"
                            class="text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition"
                            style="background-color:#16a34a;" onmouseover="this.style.backgroundColor='#15803d'" onmouseout="this.style.backgroundColor='#16a34a'">
                            + Novo Quarto
                        </button>
                    </div>
                    <div class="overflow-x-auto max-h-80 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="sticky top-0 bg-white">
                                <tr class="border-b border-stone-100">
                                    <th class="text-left py-2 px-3 text-xs text-stone-400 font-semibold uppercase">Nº</th>
                                    <th class="text-left py-2 px-3 text-xs text-stone-400 font-semibold uppercase">Categoria</th>
                                    <th class="text-left py-2 px-3 text-xs text-stone-400 font-semibold uppercase">Status</th>
                                    <th class="text-center py-2 px-3 text-xs text-stone-400 font-semibold uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaQuartos"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Card Quartos Inativos -->
                <div id="cardInativos" class="hidden bg-white rounded-2xl shadow-sm border border-red-100 p-6">
                    <h3 class="text-sm font-bold text-red-600 uppercase tracking-wide mb-4">Quartos Desativados</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-stone-100">
                                    <th class="text-left py-2 px-3 text-xs text-stone-400 font-semibold uppercase">Nº</th>
                                    <th class="text-left py-2 px-3 text-xs text-stone-400 font-semibold uppercase">Categoria</th>
                                    <th class="text-center py-2 px-3 text-xs text-stone-400 font-semibold uppercase">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaInativos"></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- ============================================ -->
    <!-- MODAL: FECHAR CAIXA                          -->
    <!-- ============================================ -->
    <div id="modalFecharCaixa" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color:rgba(0,0,0,0.65);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm text-center p-8">
            <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
            </div>
            <h2 class="text-lg font-bold text-stone-800 mb-2">Fechar Faturamento</h2>
            <p class="text-stone-500 text-sm mb-6">Tem certeza que deseja encerrar o turno atual?</p>
            <button onclick="confirmarFechamentoCaixa()"
                class="w-full text-white font-semibold py-3 rounded-xl transition mb-2"
                style="background-color:#dc2626;" onmouseover="this.style.backgroundColor='#b91c1c'" onmouseout="this.style.backgroundColor='#dc2626'">
                Sim, Encerrar Turno
            </button>
            <button onclick="fecharModalCaixa()" class="w-full text-stone-500 hover:text-stone-700 text-sm py-2 transition">Cancelar</button>
            <div id="mensagemCaixa" class="mt-3 text-sm font-medium hidden"></div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- MODAL: HISTÓRICO DIÁRIO                      -->
    <!-- ============================================ -->
    <div id="modalHistoricoCaixa" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color:rgba(0,0,0,0.65);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-stone-100">
                <h2 class="text-lg font-bold text-stone-800">Fechamentos Diários</h2>
                <button onclick="fecharModalHistoricoCaixa()" class="text-stone-400 hover:text-stone-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="p-6 max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-stone-100">
                            <th class="text-left py-2 text-xs text-stone-400 font-semibold uppercase">Data</th>
                            <th class="text-center py-2 text-xs text-stone-400 font-semibold uppercase">Locações</th>
                            <th class="text-right py-2 text-xs text-stone-400 font-semibold uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaHistoricoCaixaBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- MODAL: HISTÓRICO MENSAL                      -->
    <!-- ============================================ -->
    <div id="modalHistoricoMes" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color:rgba(0,0,0,0.65);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-stone-100">
                <h2 class="text-lg font-bold" style="color:#1d4ed8;">Fechamento Mensal</h2>
                <button onclick="fecharModalHistoricoMes()" class="text-stone-400 hover:text-stone-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="p-6 max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-stone-100">
                            <th class="text-left py-2 text-xs text-stone-400 font-semibold uppercase">Mês/Ano</th>
                            <th class="text-center py-2 text-xs text-stone-400 font-semibold uppercase">Locações</th>
                            <th class="text-right py-2 text-xs text-stone-400 font-semibold uppercase">Faturamento</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaHistoricoMesBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- MODAL: HISTÓRICO DE LOCAÇÕES                 -->
    <!-- ============================================ -->
    <div id="modalHistorico" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color:rgba(0,0,0,0.65);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl">
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-stone-100">
                <h2 class="text-lg font-bold text-stone-800">Histórico de Locações</h2>
                <button onclick="fecharModalHistorico()" class="text-stone-400 hover:text-stone-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="p-6 max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-stone-100">
                            <th class="text-left py-2 text-xs text-stone-400 font-semibold uppercase">Cliente</th>
                            <th class="text-center py-2 text-xs text-stone-400 font-semibold uppercase">Quarto</th>
                            <th class="text-left py-2 text-xs text-stone-400 font-semibold uppercase">Entrada</th>
                            <th class="text-left py-2 text-xs text-stone-400 font-semibold uppercase">Saída</th>
                            <th class="text-right py-2 text-xs text-stone-400 font-semibold uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaHistoricoBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- MODAL: NOVO QUARTO                           -->
    <!-- ============================================ -->
    <div id="modalNovoQuarto" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color:rgba(0,0,0,0.65);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-stone-100">
                <h2 class="text-lg font-bold text-stone-800">Adicionar Quarto</h2>
                <button onclick="fecharModalNovoQuarto()" class="text-stone-400 hover:text-stone-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1">Número do Quarto *</label>
                    <input type="number" id="novo_numero_quarto"
                        class="w-full px-3 py-2.5 border border-stone-200 rounded-lg text-stone-800 bg-stone-50 focus:outline-none focus:ring-2 focus:ring-amber-400 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1">Categoria *</label>
                    <select id="nova_categoria_quarto"
                        class="w-full px-3 py-2.5 border border-stone-200 rounded-lg text-stone-800 bg-stone-50 focus:outline-none focus:ring-2 focus:ring-amber-400 text-sm"></select>
                </div>
                <button onclick="salvarNovoQuarto()"
                    class="w-full text-white font-semibold py-3 rounded-xl transition"
                    style="background-color:#16a34a;" onmouseover="this.style.backgroundColor='#15803d'" onmouseout="this.style.backgroundColor='#16a34a'">
                    Cadastrar Quarto
                </button>
                <div id="mensagemNovoQuarto" class="text-center text-sm font-medium hidden"></div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- MODAL: EDITAR QUARTO                         -->
    <!-- ============================================ -->
    <div id="modalEditarQuarto" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color:rgba(0,0,0,0.65);">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
            <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-stone-100">
                <h2 class="text-lg font-bold text-stone-800">Alterar Categoria — Quarto <span id="tituloEdicaoQuarto"></span></h2>
                <button onclick="fecharModalEditarQuarto()" class="text-stone-400 hover:text-stone-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <input type="hidden" id="edit_numero_quarto">
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wide mb-1">Nova Categoria *</label>
                    <select id="edit_categoria_quarto"
                        class="w-full px-3 py-2.5 border border-stone-200 rounded-lg text-stone-800 bg-stone-50 focus:outline-none focus:ring-2 focus:ring-amber-400 text-sm"></select>
                </div>
                <button onclick="salvarEdicaoQuarto()"
                    class="w-full font-semibold py-3 rounded-xl transition text-violet-900"
                    style="background-color:#f59e0b;" onmouseover="this.style.backgroundColor='#d97706'" onmouseout="this.style.backgroundColor='#f59e0b'">
                    Atualizar Categoria
                </button>
                <div id="mensagemEditarQuarto" class="text-center text-sm font-medium hidden"></div>
            </div>
        </div>
    </div>

    <script>
        // ---- FUNÇÕES DO PAINEL ----
        function mostrarMsg(id, texto, ok) {
            const el = document.getElementById(id);
            el.classList.remove('hidden');
            el.style.color = ok ? '#16a34a' : '#dc2626';
            el.innerText = texto;
        }

        function carregarPainel() {
            fetch('../backend/api.php?acao=dados_painel').then(r => r.json()).then(dados => {
                if (!dados.sucesso) return;
                document.getElementById('valorFaturamento').innerText =
                    parseFloat(dados.faturamento).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

                const tbodyCat    = document.getElementById('tabelaCategorias');
                const selNovo     = document.getElementById('nova_categoria_quarto');
                const selEdit     = document.getElementById('edit_categoria_quarto');
                tbodyCat.innerHTML = '';
                selNovo.innerHTML  = '<option value="">Selecione...</option>';
                selEdit.innerHTML  = '<option value="">Selecione...</option>';

                dados.categorias.forEach(cat => {
                    tbodyCat.innerHTML += `
                        <tr class="border-b border-stone-50">
                            <td class="py-2.5 text-stone-700 font-medium">${cat.nome_categoria}</td>
                            <td class="py-2.5 text-right">
                                <input type="number" step="0.01" class="w-20 px-2 py-1 border border-stone-200 rounded-lg text-sm text-right focus:outline-none focus:ring-1 focus:ring-amber-400"
                                    id="preco_${cat.codigo_categoria}" value="${cat.valor_hora}">
                            </td>
                            <td class="py-2.5 pl-2">
                                <button onclick="atualizarPreco(${cat.codigo_categoria})"
                                    class="text-xs text-white px-2.5 py-1 rounded-lg font-semibold"
                                    style="background-color:#7c3aed;">Salvar</button>
                            </td>
                        </tr>`;
                    const opt = `<option value="${cat.codigo_categoria}">${cat.nome_categoria}</option>`;
                    selNovo.innerHTML += opt;
                    selEdit.innerHTML += opt;
                });
            });
        }

        function atualizarPreco(codigo) {
            const preco = document.getElementById(`preco_${codigo}`).value;
            fetch('../backend/api.php?acao=atualizar_preco', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ codigo, novo_preco: preco })
            }).then(r => r.json()).then(dados => {
                mostrarMsg('mensagemPrecos', dados.mensagem, dados.sucesso);
                if (dados.sucesso) { carregarPainel(); setTimeout(() => document.getElementById('mensagemPrecos').classList.add('hidden'), 3000); }
            });
        }

        function carregarListaQuartos() {
            fetch('../backend/api.php?acao=buscar_quartos').then(r => r.json()).then(dados => {
                const tbody = document.getElementById('tabelaQuartos');
                tbody.innerHTML = '';
                if (!dados.sucesso) return;
                dados.quartos.forEach(q => {
                    const sc = q.status_quarto === 'Livre' ? 'color:#16a34a' :
                               q.status_quarto === 'Ocupado' ? 'color:#dc2626' : 'color:#d97706';
                    tbody.innerHTML += `
                        <tr class="border-b border-stone-50 hover:bg-stone-50">
                            <td class="py-2.5 px-3 font-bold text-stone-800">${q.numero_quarto}</td>
                            <td class="py-2.5 px-3 text-stone-500 text-xs">${q.nome_categoria}</td>
                            <td class="py-2.5 px-3 text-xs font-bold" style="${sc}">${q.status_quarto}</td>
                            <td class="py-2.5 px-3 text-center">
                                <button onclick="abrirModalEditarQuarto(${q.numero_quarto})"
                                    class="text-xs font-semibold px-2 py-1 rounded-lg mr-1 text-violet-900"
                                    style="background-color:#ede9fe;">Categoria</button>
                                ${q.status_quarto === 'Livre' ? `<button onclick="confirmarDesativar(${q.numero_quarto})"
                                    class="text-xs text-white font-semibold px-2 py-1 rounded-lg"
                                    style="background-color:#dc2626;">Desativar</button>` : ''}
                            </td>
                        </tr>`;
                });
            });
        }

        function abrirModalEditarQuarto(num) {
            document.getElementById('modalEditarQuarto').classList.remove('hidden');
            document.getElementById('tituloEdicaoQuarto').innerText = num;
            document.getElementById('edit_numero_quarto').value = num;
            document.getElementById('mensagemEditarQuarto').classList.add('hidden');
        }
        function fecharModalEditarQuarto() { document.getElementById('modalEditarQuarto').classList.add('hidden'); }
        function salvarEdicaoQuarto() {
            const num = document.getElementById('edit_numero_quarto').value;
            const cat = document.getElementById('edit_categoria_quarto').value;
            fetch('../backend/api.php?acao=editar_quarto', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ numero: num, nova_categoria: cat })
            }).then(r => r.json()).then(dados => {
                mostrarMsg('mensagemEditarQuarto', dados.mensagem, dados.sucesso);
                if (dados.sucesso) setTimeout(() => { fecharModalEditarQuarto(); carregarListaQuartos(); }, 1400);
            });
        }

        function abrirModalNovoQuarto() {
            document.getElementById('modalNovoQuarto').classList.remove('hidden');
            document.getElementById('mensagemNovoQuarto').classList.add('hidden');
        }
        function fecharModalNovoQuarto() {
            document.getElementById('modalNovoQuarto').classList.add('hidden');
            document.getElementById('novo_numero_quarto').value = '';
        }
        function salvarNovoQuarto() {
            const num = document.getElementById('novo_numero_quarto').value;
            const cat = document.getElementById('nova_categoria_quarto').value;
            fetch('../backend/api.php?acao=adicionar_quarto', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ numero: num, categoria: cat })
            }).then(r => r.json()).then(dados => {
                mostrarMsg('mensagemNovoQuarto', dados.mensagem, dados.sucesso);
                if (dados.sucesso) setTimeout(() => { fecharModalNovoQuarto(); carregarListaQuartos(); }, 1400);
            });
        }

        function abrirModalCaixa() {
            document.getElementById('modalFecharCaixa').classList.remove('hidden');
            document.getElementById('mensagemCaixa').classList.add('hidden');
        }
        function fecharModalCaixa() { document.getElementById('modalFecharCaixa').classList.add('hidden'); }
        function confirmarFechamentoCaixa() {
            fetch('../backend/api.php?acao=fechar_caixa', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({})
            }).then(r => r.json()).then(dados => {
                mostrarMsg('mensagemCaixa', dados.mensagem, dados.sucesso);
                if (dados.sucesso) setTimeout(() => { fecharModalCaixa(); carregarPainel(); }, 1400);
            });
        }

        function abrirModalHistoricoCaixa() {
            document.getElementById('modalHistoricoCaixa').classList.remove('hidden');
            fetch('../backend/api.php?acao=buscar_historico_caixa').then(r => r.json()).then(dados => {
                const tbody = document.getElementById('tabelaHistoricoCaixaBody');
                tbody.innerHTML = '';
                if (dados.sucesso && dados.historico.length > 0) {
                    dados.historico.forEach(dia => {
                        const v = parseFloat(dia.total_dia).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                        tbody.innerHTML += `<tr class="border-b border-stone-50">
                            <td class="py-2.5 font-medium text-stone-800">${dia.data_formatada}</td>
                            <td class="py-2.5 text-center text-stone-500">${dia.qtd_locacoes}</td>
                            <td class="py-2.5 text-right font-bold" style="color:#16a34a;">${v}</td>
                        </tr>`;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="3" class="py-6 text-center text-stone-400">Nenhum fechamento encontrado.</td></tr>';
                }
            });
        }
        function fecharModalHistoricoCaixa() { document.getElementById('modalHistoricoCaixa').classList.add('hidden'); }

        function abrirModalHistoricoMes() {
            document.getElementById('modalHistoricoMes').classList.remove('hidden');
            fetch('../backend/api.php?acao=buscar_historico_mes').then(r => r.json()).then(dados => {
                const tbody = document.getElementById('tabelaHistoricoMesBody');
                tbody.innerHTML = '';
                if (dados.sucesso && dados.historico.length > 0) {
                    dados.historico.forEach(mes => {
                        const v = parseFloat(mes.total_mes).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                        tbody.innerHTML += `<tr class="border-b border-stone-50">
                            <td class="py-2.5 font-medium text-stone-800">${mes.mes_ano}</td>
                            <td class="py-2.5 text-center text-stone-500">${mes.qtd_locacoes}</td>
                            <td class="py-2.5 text-right font-bold" style="color:#1d4ed8;">${v}</td>
                        </tr>`;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="3" class="py-6 text-center text-stone-400">Nenhum fechamento registrado.</td></tr>';
                }
            });
        }
        function fecharModalHistoricoMes() { document.getElementById('modalHistoricoMes').classList.add('hidden'); }

        function abrirModalHistorico() {
            document.getElementById('modalHistorico').classList.remove('hidden');
            fetch('../backend/api.php?acao=buscar_historico').then(r => r.json()).then(dados => {
                const tbody = document.getElementById('tabelaHistoricoBody');
                tbody.innerHTML = '';
                if (dados.sucesso && dados.historico.length > 0) {
                    dados.historico.forEach(loc => {
                        const v     = loc.valor_total
                            ? parseFloat(loc.valor_total).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
                            : null;
                        const saida = loc.saida
                            ? `<span class="text-stone-500">${loc.saida}</span>`
                            : `<span class="text-amber-600 font-semibold">Em andamento</span>`;
                        const valor = v
                            ? `<span style="color:#16a34a;">${v}</span>`
                            : `<span class="text-stone-300">—</span>`;
                        tbody.innerHTML += `<tr class="border-b border-stone-50 hover:bg-stone-50">
                            <td class="py-2.5 text-stone-700">${loc.nome}</td>
                            <td class="py-2.5 text-center font-bold text-stone-800">${loc.numero_quarto}</td>
                            <td class="py-2.5 text-xs">${loc.entrada}</td>
                            <td class="py-2.5 text-xs">${saida}</td>
                            <td class="py-2.5 text-right font-bold text-sm">${valor}</td>
                        </tr>`;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="py-6 text-center text-stone-400">Nenhuma locação encontrada.</td></tr>';
                }
            });
        }
        function fecharModalHistorico() { document.getElementById('modalHistorico').classList.add('hidden'); }

        function carregarQuartosInativos() {
            fetch('../backend/api.php?acao=buscar_quartos_inativos').then(r => r.json()).then(dados => {
                const card  = document.getElementById('cardInativos');
                const tbody = document.getElementById('tabelaInativos');
                tbody.innerHTML = '';
                if (dados.sucesso && dados.quartos.length > 0) {
                    card.classList.remove('hidden');
                    dados.quartos.forEach(q => {
                        tbody.innerHTML += `<tr class="border-b border-stone-50">
                            <td class="py-2.5 px-3 font-bold text-stone-800">${q.numero_quarto}</td>
                            <td class="py-2.5 px-3 text-stone-500 text-xs">${q.nome_categoria}</td>
                            <td class="py-2.5 px-3 text-center">
                                <button onclick="reativarQuarto(${q.numero_quarto})"
                                    class="text-xs text-white font-semibold px-3 py-1 rounded-lg"
                                    style="background-color:#16a34a;">Reativar</button>
                            </td>
                        </tr>`;
                    });
                } else {
                    card.classList.add('hidden');
                }
            });
        }

        function reativarQuarto(num) {
            fetch('../backend/api.php?acao=reativar_quarto', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ numero: num })
            }).then(r => r.json()).then(dados => {
                alert(dados.mensagem);
                if (dados.sucesso) { carregarListaQuartos(); carregarQuartosInativos(); }
            });
        }

        function confirmarDesativar(num) {
            if (confirm(`Desativar o quarto ${num}? O histórico será preservado.`)) {
                fetch('../backend/api.php?acao=desativar_quarto', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ numero: num })
                }).then(r => r.json()).then(dados => {
                    alert(dados.mensagem);
                    if (dados.sucesso) { carregarListaQuartos(); carregarQuartosInativos(); }
                });
            }
        }

        carregarPainel();
        carregarListaQuartos();
        carregarQuartosInativos();
    </script>
</body>
</html>
