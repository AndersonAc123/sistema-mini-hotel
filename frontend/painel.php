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
    <title>Painel Gerencial - Mini Hotel</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .cabecalho { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: white; padding: 15px; border-radius: 8px; }
        .cabecalho a { color: #ffc107; text-decoration: none; font-weight: bold; }
        
        .dashboard { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-top: 20px; }
        
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card-faturamento { text-align: center; border-top: 5px solid #28a745; }
        .card-faturamento h3 { margin: 0; color: #555; }
        .card-faturamento h1 { margin: 10px 0 20px 0; color: #28a745; font-size: 48px; }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        
        .input-preco { width: 80px; padding: 5px; }
        .btn-salvar { background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        
        .btn-fechar-caixa { background: #dc3545; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; }
        .btn-fechar-caixa:hover { background: #c82333; }
        
        .btn-ver-caixas { background: #6c757d; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px; }
        .btn-ver-caixas:hover { background: #5a6268; }

        .btn-novo-quarto { background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; float: right; font-weight: bold; }
        
        .btn-historico { background: #17a2b8; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; margin-right: 15px; }
        .btn-historico:hover { background: #138496; }

        .btn-acao { border: none; padding: 6px 15px; border-radius: 4px; cursor: pointer; font-size: 13px; color: white; margin-right: 5px;}
        .btn-editar { background: #ffc107; color: black; font-weight: bold; }
        .btn-desativar { background: #dc3545; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); }
        .modal-conteudo { background-color: #fff; margin: 5% auto; padding: 25px; border-radius: 8px; width: 90%; max-width: 800px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .fechar-modal { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; margin-top: -15px; margin-right: -10px; }
        .fechar-modal:hover { color: black; }
        .tabela-scroll { max-height: 400px; overflow-y: auto; margin-top: 15px; }
    </style>
</head>
<body>

    <div class="cabecalho">
        <h2>Painel Gerencial</h2>
        <div>
            <button class="btn-historico" onclick="abrirModalHistorico()">Relatório de Locações</button>
            <a href="recepcao.php">Voltar para Recepção</a>
        </div>
    </div>

    <div class="dashboard">
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card card-faturamento">
                <h3>Faturamento do Turno</h3>
                <h1 id="valorFaturamento">R$ 0,00</h1>
                <button class="btn-fechar-caixa" onclick="abrirModalCaixa()">Fechar Faturamento do Dia</button>
                <button class="btn-ver-caixas" onclick="abrirModalHistoricoCaixa()">Ver Fechamentos Diários</button>
                <button class="btn-ver-caixas" style="background: #007bff;" onclick="abrirModalHistoricoMes()">Ver Fechamento Mensal</button>
            </div>
            
            <div class="card">
                <h3>Gestão de Preços (Categorias)</h3>
                <table>
                    <thead><tr><th>Categoria</th><th>Preço Atual</th><th>Novo Preço</th><th>Ação</th></tr></thead>
                    <tbody id="tabelaCategorias"></tbody>
                </table>
                <div id="mensagemPrecos" style="margin-top: 15px; font-weight: bold; text-align: center;"></div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <button class="btn-novo-quarto" onclick="abrirModalNovoQuarto()">+ Novo Quarto</button>
                <h3>Gestão de Quartos</h3>
                <div class="tabela-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Nº</th>
                                <th>Categoria</th>
                                <th>Status Atual</th>
                                <th style="text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaQuartos"></tbody>
                    </table>
                </div>
            </div>

            <div class="card" id="cardInativos" style="display: none;">
                <h3 style="color: #dc3545;">Quartos Desativados</h3>
                <div class="tabela-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Nº</th>
                                <th>Categoria</th>
                                <th style="text-align: center;">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaInativos"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="modalFecharCaixa" class="modal">
        <div class="modal-conteudo" style="max-width: 400px; text-align: center;">
            <span class="fechar-modal" onclick="fecharModalCaixa()">&times;</span>
            <h2 style="color: #dc3545; margin-top: 0;">Fechar Faturamento</h2>
            <p>Tem certeza que deseja fechar o caixa?</p>
            <button onclick="confirmarFechamentoCaixa()" class="btn-fechar-caixa">Sim, Encerrar Turno</button>
            <div id="mensagemCaixa" style="margin-top: 10px; font-weight: bold;"></div>
        </div>
    </div>

    <div id="modalHistoricoCaixa" class="modal">
        <div class="modal-conteudo" style="max-width: 600px;">
            <span class="fechar-modal" onclick="fecharModalHistoricoCaixa()">&times;</span>
            <h2 style="margin-top: 0;">Fechamentos Diários</h2>
            <div class="tabela-scroll">
                <table>
                    <thead><tr><th>Data do Movimento</th><th>Locações Atendidas</th><th>Total Faturado</th></tr></thead>
                    <tbody id="tabelaHistoricoCaixaBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalHistoricoMes" class="modal">
        <div class="modal-conteudo" style="max-width: 600px;">
            <span class="fechar-modal" onclick="fecharModalHistoricoMes()">&times;</span>
            <h2 style="margin-top: 0; color: #007bff;">Fechamento Mensal</h2>
            <div class="tabela-scroll">
                <table>
                    <thead><tr><th>Mês/Ano</th><th>Total de Locações</th><th>Faturamento do Mês</th></tr></thead>
                    <tbody id="tabelaHistoricoMesBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalHistorico" class="modal">
        <div class="modal-conteudo">
            <span class="fechar-modal" onclick="fecharModalHistorico()">&times;</span>
            <h2 style="margin-top: 0;">Histórico Detalhado de Locações</h2>
            <div class="tabela-scroll">
                <table>
                    <thead><tr><th>Cliente</th><th>Quarto</th><th>Entrada</th><th>Saída</th><th>Total Pago</th></tr></thead>
                    <tbody id="tabelaHistoricoBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalNovoQuarto" class="modal">
        <div class="modal-conteudo" style="max-width: 400px;">
            <span class="fechar-modal" onclick="fecharModalNovoQuarto()">&times;</span>
            <h2 style="margin-top: 0;">Adicionar Novo Quarto</h2>
            <label style="display: block; margin-top: 15px;">Número do Quarto *</label>
            <input type="number" id="novo_numero_quarto" style="width: 90%; padding: 8px; margin-bottom: 10px;" required>
            <label style="display: block;">Categoria *</label>
            <select id="nova_categoria_quarto" style="width: 95%; padding: 8px; margin-bottom: 20px;"></select>
            <button onclick="salvarNovoQuarto()" class="btn-salvar" style="width: 95%; padding: 10px; background: #28a745;">Cadastrar Quarto</button>
            <div id="mensagemNovoQuarto" style="margin-top: 10px; font-weight: bold;"></div>
        </div>
    </div>

    <div id="modalEditarQuarto" class="modal">
        <div class="modal-conteudo" style="max-width: 400px;">
            <span class="fechar-modal" onclick="fecharModalEditarQuarto()">&times;</span>
            <h2 style="margin-top: 0;">Alterar Categoria do Quarto <span id="tituloEdicaoQuarto"></span></h2>
            
            <input type="hidden" id="edit_numero_quarto"> 

            <label style="display: block; margin-top: 15px;">Nova Categoria *</label>
            <select id="edit_categoria_quarto" style="width: 95%; padding: 8px; margin-bottom: 20px;"></select>
            
            <button onclick="salvarEdicaoQuarto()" class="btn-salvar" style="width: 95%; padding: 10px; background: #ffc107; color: black; font-weight:bold;">Atualizar Categoria</button>
            <div id="mensagemEditarQuarto" style="margin-top: 15px; font-weight: bold; font-size: 14px;"></div>
        </div>
    </div>

    <script>
        function carregarPainel() {
            fetch('../backend/api.php?acao=dados_painel').then(res => res.json()).then(dados => {
                if(dados.sucesso) {
                    const fatFormatado = parseFloat(dados.faturamento).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                    document.getElementById('valorFaturamento').innerText = fatFormatado;

                    const tbody = document.getElementById('tabelaCategorias');
                    const selectCatNovo = document.getElementById('nova_categoria_quarto');
                    const selectCatEdit = document.getElementById('edit_categoria_quarto');
                    
                    tbody.innerHTML = '';
                    selectCatNovo.innerHTML = '<option value="">Selecione uma categoria...</option>';
                    selectCatEdit.innerHTML = '<option value="">Selecione uma categoria...</option>';
                    
                    dados.categorias.forEach(cat => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td><strong>${cat.nome_categoria}</strong></td>
                                        <td>R$ ${parseFloat(cat.valor_hora).toFixed(2)}</td>
                                        <td>R$ <input type="number" step="0.01" class="input-preco" id="preco_${cat.codigo_categoria}" value="${cat.valor_hora}"></td>
                                        <td><button class="btn-salvar" onclick="atualizarPreco(${cat.codigo_categoria})">Salvar</button></td>`;
                        tbody.appendChild(tr);

                        const opcao = `<option value="${cat.codigo_categoria}">${cat.nome_categoria}</option>`;
                        selectCatNovo.innerHTML += opcao;
                        selectCatEdit.innerHTML += opcao;
                    });
                }
            });
        }

        function atualizarPreco(codigo) {
            const novoPreco = document.getElementById(`preco_${codigo}`).value;
            fetch('../backend/api.php?acao=atualizar_preco', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ codigo: codigo, novo_preco: novoPreco })
            }).then(res => res.json()).then(dados => {
                const msg = document.getElementById('mensagemPrecos');
                msg.style.color = dados.sucesso ? 'green' : 'red';
                msg.innerText = dados.mensagem;
                if(dados.sucesso) { carregarPainel(); setTimeout(() => msg.innerText = '', 3000); }
            });
        }

        function carregarListaQuartos() {
            fetch('../backend/api.php?acao=buscar_quartos').then(res => res.json()).then(dados => {
                const tbody = document.getElementById('tabelaQuartos');
                tbody.innerHTML = '';
                if(dados.sucesso) {
                    dados.quartos.forEach(q => {
                        let cor = q.status_quarto === 'Livre' ? 'green' : (q.status_quarto === 'Ocupado' ? 'red' : '#ffc107');
                        tbody.innerHTML += `
                            <tr>
                                <td><strong>${q.numero_quarto}</strong></td>
                                <td>${q.nome_categoria}</td>
                                <td style="color: ${cor}; font-weight: bold;">${q.status_quarto}</td>
                                <td style="text-align: center;">
                                    <button class="btn-acao btn-editar" onclick="abrirModalEditarQuarto(${q.numero_quarto})">Alterar Categoria</button>
                                    ${q.status_quarto === 'Livre' ? `<button class="btn-acao btn-desativar" onclick="confirmarDesativar(${q.numero_quarto})">Desativar</button>` : ''}
                                </td>
                            </tr>
                        `;
                    });
                }
            });
        }

        function abrirModalEditarQuarto(numero) {
            document.getElementById('modalEditarQuarto').style.display = 'block';
            document.getElementById('tituloEdicaoQuarto').innerText = numero; 
            document.getElementById('edit_numero_quarto').value = numero; 
            document.getElementById('mensagemEditarQuarto').innerText = '';
        }
        
        function fecharModalEditarQuarto() { document.getElementById('modalEditarQuarto').style.display = 'none'; }
        
        function salvarEdicaoQuarto() {
            const num = document.getElementById('edit_numero_quarto').value;
            const cat = document.getElementById('edit_categoria_quarto').value;
            
            fetch('../backend/api.php?acao=editar_quarto', {
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' }, 
                body: JSON.stringify({ numero: num, nova_categoria: cat }) 
            }).then(res => res.json()).then(dados => {
                const msg = document.getElementById('mensagemEditarQuarto');
                msg.style.color = dados.sucesso ? 'green' : 'red';
                msg.innerText = dados.mensagem;
                if(dados.sucesso) setTimeout(() => { fecharModalEditarQuarto(); carregarListaQuartos(); }, 1500);
            });
        }

        function abrirModalNovoQuarto() { document.getElementById('modalNovoQuarto').style.display = 'block'; document.getElementById('mensagemNovoQuarto').innerText = ''; }
        function fecharModalNovoQuarto() { document.getElementById('modalNovoQuarto').style.display = 'none'; document.getElementById('novo_numero_quarto').value = ''; }
        function salvarNovoQuarto() {
            const num = document.getElementById('novo_numero_quarto').value;
            const cat = document.getElementById('nova_categoria_quarto').value;
            fetch('../backend/api.php?acao=adicionar_quarto', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ numero: num, categoria: cat })
            }).then(res => res.json()).then(dados => {
                const msg = document.getElementById('mensagemNovoQuarto');
                msg.style.color = dados.sucesso ? 'green' : 'red';
                msg.innerText = dados.mensagem;
                if(dados.sucesso) setTimeout(() => { fecharModalNovoQuarto(); carregarListaQuartos(); }, 1500);
            });
        }

        function abrirModalCaixa() { document.getElementById('modalFecharCaixa').style.display = 'block'; document.getElementById('mensagemCaixa').innerText = ''; }
        function fecharModalCaixa() { document.getElementById('modalFecharCaixa').style.display = 'none'; }
        function confirmarFechamentoCaixa() {
            fetch('../backend/api.php?acao=fechar_caixa').then(res => res.json()).then(dados => {
                const msg = document.getElementById('mensagemCaixa');
                if(dados.sucesso) {
                    msg.style.color = 'green'; msg.innerText = dados.mensagem;
                    setTimeout(() => { fecharModalCaixa(); carregarPainel(); }, 1500);
                } else { msg.style.color = 'red'; msg.innerText = dados.mensagem; }
            });
        }

        function abrirModalHistoricoCaixa() {
            document.getElementById('modalHistoricoCaixa').style.display = 'block';
            fetch('../backend/api.php?acao=buscar_historico_caixa').then(res => res.json()).then(dados => {
                const tbody = document.getElementById('tabelaHistoricoCaixaBody');
                tbody.innerHTML = '';
                if(dados.sucesso && dados.historico.length > 0) {
                    dados.historico.forEach(dia => {
                        const valorFormatado = parseFloat(dia.total_dia).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                        tbody.innerHTML += `<tr><td><strong>${dia.data_formatada}</strong></td><td>${dia.qtd_locacoes}</td><td style="color: #28a745; font-weight: bold;">${valorFormatado}</td></tr>`;
                    });
                }
            });
        }
        function fecharModalHistoricoCaixa() { document.getElementById('modalHistoricoCaixa').style.display = 'none'; }

        // NOVO: Função para abrir o fechamento Mensal
        function abrirModalHistoricoMes() {
            document.getElementById('modalHistoricoMes').style.display = 'block';
            fetch('../backend/api.php?acao=buscar_historico_mes').then(res => res.json()).then(dados => {
                const tbody = document.getElementById('tabelaHistoricoMesBody');
                tbody.innerHTML = '';
                if(dados.sucesso && dados.historico.length > 0) {
                    dados.historico.forEach(mes => {
                        const valorFormatado = parseFloat(mes.total_mes).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                        tbody.innerHTML += `<tr><td><strong>${mes.mes_ano}</strong></td><td>${mes.qtd_locacoes}</td><td style="color: #007bff; font-weight: bold;">${valorFormatado}</td></tr>`;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="3" style="text-align: center;">Nenhum fechamento registrado.</td></tr>';
                }
            });
        }
        function fecharModalHistoricoMes() { document.getElementById('modalHistoricoMes').style.display = 'none'; }

        function abrirModalHistorico() {
            document.getElementById('modalHistorico').style.display = 'block';
            fetch('../backend/api.php?acao=buscar_historico').then(res => res.json()).then(dados => {
                const tbody = document.getElementById('tabelaHistoricoBody');
                tbody.innerHTML = '';
                if(dados.sucesso && dados.historico.length > 0) {
                    dados.historico.forEach(loc => {
                        const valorFormatado = parseFloat(loc.valor_total).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                        tbody.innerHTML += `<tr><td>${loc.nome}</td><td><strong>${loc.numero_quarto}</strong></td><td>${loc.entrada}</td><td>${loc.saida}</td><td style="color: #28a745; font-weight: bold;">${valorFormatado}</td></tr>`;
                    });
                }
            });
        }
        function fecharModalHistorico() { document.getElementById('modalHistorico').style.display = 'none'; }

        function carregarQuartosInativos() {
            fetch('../backend/api.php?acao=buscar_quartos_inativos').then(res => res.json()).then(dados => {
                const card = document.getElementById('cardInativos');
                const tbody = document.getElementById('tabelaInativos');
                tbody.innerHTML = '';
                if (dados.sucesso && dados.quartos.length > 0) {
                    card.style.display = 'block';
                    dados.quartos.forEach(q => {
                        tbody.innerHTML += `
                            <tr>
                                <td><strong>${q.numero_quarto}</strong></td>
                                <td>${q.nome_categoria}</td>
                                <td style="text-align: center;">
                                    <button class="btn-acao" style="background: #28a745;" onclick="reativarQuarto(${q.numero_quarto})">Reativar</button>
                                </td>
                            </tr>`;
                    });
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function reativarQuarto(numero) {
            fetch('../backend/api.php?acao=reativar_quarto', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ numero: numero })
            }).then(res => res.json()).then(dados => {
                alert(dados.mensagem);
                if (dados.sucesso) { carregarListaQuartos(); carregarQuartosInativos(); }
            });
        }

        function confirmarDesativar(numero) {
            if (confirm(`Desativar o quarto ${numero}? Ele será ocultado do sistema mas o histórico será preservado.`)) {
                fetch('../backend/api.php?acao=desativar_quarto', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ numero: numero })
                }).then(res => res.json()).then(dados => {
                    alert(dados.mensagem);
                    if (dados.sucesso) carregarListaQuartos();
                });
            }
        }

        carregarPainel();
        carregarListaQuartos();
        carregarQuartosInativos();
    </script>
</body>
</html>