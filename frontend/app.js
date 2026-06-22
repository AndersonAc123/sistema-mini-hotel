// ==========================================
// CARREGAMENTO DOS QUARTOS (GRID)
// ==========================================
function carregarQuartos() {
    fetch('../backend/api.php?acao=buscar_quartos')
    .then(r => r.json())
    .then(dados => {
        if (!dados.sucesso) return;
        const grid = document.getElementById('listaQuartos');
        grid.innerHTML = '';

        dados.quartos.forEach(q => {
            let topColor, badgeBg, badgeText;

            if (q.status_quarto === 'Livre') {
                topColor  = '#22c55e';
                badgeBg   = '#dcfce7';
                badgeText = '#15803d';
            } else if (q.status_quarto === 'Ocupado') {
                topColor  = '#ef4444';
                badgeBg   = '#fee2e2';
                badgeText = '#b91c1c';
            } else {
                topColor  = '#f59e0b';
                badgeBg   = '#fef3c7';
                badgeText = '#b45309';
            }

            const card = document.createElement('div');
            card.style.cssText = `
                background:#fff;
                border-radius:1rem;
                border-top:4px solid ${topColor};
                box-shadow:0 1px 4px rgba(0,0,0,0.08);
                cursor:pointer;
                padding:1.25rem 1rem;
                text-align:center;
                transition:box-shadow 0.2s, transform 0.15s;
            `;

            card.innerHTML = `
                <p style="font-size:10px;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:2px;">Quarto</p>
                <h3 style="font-size:2rem;font-weight:800;color:#1c1917;margin:0 0 4px 0;">${q.numero_quarto}</h3>
                <p style="font-size:12px;color:#78716c;margin:0 0 2px 0;">${q.nome_categoria}</p>
                <p style="font-size:13px;font-weight:600;color:#7c3aed;margin:0 0 10px 0;">R$ ${parseFloat(q.valor_hora).toFixed(2)}/h</p>
                <span style="display:inline-block;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;background:${badgeBg};color:${badgeText};">
                    ${q.status_quarto}
                </span>
            `;

            card.addEventListener('mouseenter', () => {
                card.style.boxShadow = '0 4px 16px rgba(0,0,0,0.13)';
                card.style.transform = 'translateY(-2px)';
            });
            card.addEventListener('mouseleave', () => {
                card.style.boxShadow = '0 1px 4px rgba(0,0,0,0.08)';
                card.style.transform = 'translateY(0)';
            });

            card.addEventListener('click', () => {
                if (q.status_quarto === 'Livre')        abrirModal(q.numero_quarto);
                else if (q.status_quarto === 'Ocupado') abrirModalCheckout(q.numero_quarto);
                else                                     perguntarLiberacaoLimpeza(q.numero_quarto);
            });

            grid.appendChild(card);
        });
    })
    .catch(e => console.error('Erro ao buscar quartos:', e));
}

carregarQuartos();

// ==========================================
// MÁSCARAS DE ENTRADA
// ==========================================
document.getElementById('data_nascimento').addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g, '');
    v = v.replace(/(\d{2})(\d)/, '$1/$2');
    v = v.replace(/(\d{2})(\d)/, '$1/$2');
    e.target.value = v;
});

document.getElementById('cpf').addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g, '');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = v;
});

document.getElementById('telefone').addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g, '');
    v = v.replace(/^(\d{2})(\d)/g, '($1) $2');
    v = v.replace(/(\d{5})(\d)/, '$1-$2');
    e.target.value = v;
});

// ==========================================
// MODAL: CHECK-IN
// ==========================================
function abrirModal(numeroQuarto) {
    document.getElementById('modalCheckin').classList.remove('hidden');
    document.getElementById('tituloModal').innerText = `Entrada — Quarto ${numeroQuarto}`;
    document.getElementById('quarto_selecionado').value = numeroQuarto;
    document.getElementById('mensagemModal').classList.add('hidden');

    // Preenche data/hora de entrada com o momento atual (horário local)
    const agora = new Date();
    agora.setSeconds(0, 0);
    const offset = agora.getTimezoneOffset() * 60000;
    document.getElementById('data_hora_entrada').value =
        new Date(agora - offset).toISOString().slice(0, 16);
}

function fecharModal() {
    document.getElementById('modalCheckin').classList.add('hidden');
    document.getElementById('formCheckin').reset();
    document.getElementById('mensagemModal').classList.add('hidden');
}

document.getElementById('formCheckin').addEventListener('submit', function(e) {
    e.preventDefault();

    const dataDigitada = document.getElementById('data_nascimento').value;
    const partes = dataDigitada.split('/');
    let dataParaBanco = '';
    if (partes.length === 3) dataParaBanco = `${partes[2]}-${partes[1]}-${partes[0]}`;

    const dados = {
        quarto:           document.getElementById('quarto_selecionado').value,
        nome:             document.getElementById('nome').value,
        cpf:              document.getElementById('cpf').value,
        telefone:         document.getElementById('telefone').value,
        data_nascimento:  dataParaBanco,
        placa_veiculo:    document.getElementById('placa_veiculo').value,
        data_hora_entrada: document.getElementById('data_hora_entrada').value
    };

    fetch('../backend/api.php?acao=fazer_checkin', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(dados)
    })
    .then(r => r.json())
    .then(resp => {
        const el = document.getElementById('mensagemModal');
        el.classList.remove('hidden');
        if (resp.sucesso) {
            el.style.color = '#16a34a';
            el.innerText = resp.mensagem;
            setTimeout(() => { fecharModal(); carregarQuartos(); }, 1400);
        } else {
            el.style.color = '#dc2626';
            el.innerText = resp.mensagem;
        }
    });
});

// ==========================================
// MODAL: CHECK-OUT
// ==========================================
function abrirModalCheckout(numeroQuarto) {
    document.getElementById('modalCheckout').classList.remove('hidden');
    document.getElementById('numQuartoCheckout').innerText = numeroQuarto;
    document.getElementById('quarto_checkout').value = numeroQuarto;
    document.getElementById('mensagemCheckout').classList.add('hidden');

    ['checkoutNome','checkoutEntrada','checkoutHoras','checkoutTotal']
        .forEach(id => document.getElementById(id).innerText = '...');

    // Preenche saída com horário atual (horário local)
    const agora = new Date();
    agora.setSeconds(0, 0);
    const offset = agora.getTimezoneOffset() * 60000;
    document.getElementById('data_hora_saida_input').value =
        new Date(agora - offset).toISOString().slice(0, 16);

    fetch(`../backend/api.php?acao=obter_detalhes_checkout&quarto=${numeroQuarto}`)
    .then(r => r.json())
    .then(dados => {
        if (dados.sucesso) {
            document.getElementById('checkoutNome').innerText    = dados.nome;
            document.getElementById('checkoutEntrada').innerText = dados.entrada;
            document.getElementById('id_locacao_checkout').value = dados.id_locacao;
            document.getElementById('entrada_iso_checkout').value = dados.data_hora_entrada_iso;
            document.getElementById('valor_hora_checkout').value  = dados.valor_hora;
            atualizarTotalCheckout();
        } else {
            alert(dados.mensagem);
            fecharModalCheckout();
        }
    });
}

// Recalcula horas e total sempre que o usuário muda a data/hora de saída
document.getElementById('data_hora_saida_input').addEventListener('change', atualizarTotalCheckout);

function atualizarTotalCheckout() {
    const entradaISO = document.getElementById('entrada_iso_checkout').value;
    const saidaVal   = document.getElementById('data_hora_saida_input').value;
    const valorHora  = parseFloat(document.getElementById('valor_hora_checkout').value || '0');

    if (!entradaISO || !saidaVal) return;

    const entrada = new Date(entradaISO.replace(' ', 'T'));
    const saida   = new Date(saidaVal);
    const minutos = (saida - entrada) / (1000 * 60);

    if (minutos <= 0) {
        document.getElementById('checkoutHoras').innerText = '—';
        document.getElementById('checkoutTotal').innerText = '0,00';
        return;
    }

    const horas = Math.max(1, Math.ceil(minutos / 60));
    const total = (horas * valorHora).toFixed(2).replace('.', ',');
    document.getElementById('checkoutHoras').innerText = `${horas}h`;
    document.getElementById('checkoutTotal').innerText = total;
}

function fecharModalCheckout() {
    document.getElementById('modalCheckout').classList.add('hidden');
    document.getElementById('mensagemCheckout').classList.add('hidden');
}

function confirmarCheckout() {
    const saidaVal = document.getElementById('data_hora_saida_input').value;
    if (!saidaVal) {
        alert('Informe a data e hora de saída.');
        return;
    }

    const dadosCheckout = {
        id_locacao:      document.getElementById('id_locacao_checkout').value,
        quarto:          document.getElementById('quarto_checkout').value,
        data_hora_saida: saidaVal
    };

    fetch('../backend/api.php?acao=fazer_checkout', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(dadosCheckout)
    })
    .then(r => r.json())
    .then(dados => {
        const el = document.getElementById('mensagemCheckout');
        el.classList.remove('hidden');
        if (dados.sucesso) {
            el.style.color = '#16a34a';
            el.innerText = dados.mensagem;
            setTimeout(() => { fecharModalCheckout(); carregarQuartos(); }, 1400);
        } else {
            el.style.color = '#dc2626';
            el.innerText = dados.mensagem;
        }
    });
}

// ==========================================
// MODAL: LIMPEZA
// ==========================================
function perguntarLiberacaoLimpeza(numeroQuarto) {
    document.getElementById('modalLimpeza').classList.remove('hidden');
    document.getElementById('numQuartoLimpeza').innerText = numeroQuarto;
    document.getElementById('quarto_limpeza_id').value = numeroQuarto;
}

function fecharModalLimpeza() {
    document.getElementById('modalLimpeza').classList.add('hidden');
}

function confirmarLimpeza() {
    const num = document.getElementById('quarto_limpeza_id').value;
    fetch('../backend/api.php?acao=finalizar_limpeza', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ quarto: num })
    })
    .then(r => r.json())
    .then(dados => {
        if (dados.sucesso) {
            fecharModalLimpeza();
            carregarQuartos();
        } else {
            alert(dados.mensagem);
        }
    });
}
