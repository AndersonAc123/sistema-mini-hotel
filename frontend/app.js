function carregarQuartos() {
    fetch('../backend/api.php?acao=buscar_quartos')
    .then(resposta => resposta.json())
    .then(dados => {
        if(dados.sucesso) {
            const divGrid = document.getElementById('listaQuartos');
            divGrid.innerHTML = ''; 

            dados.quartos.forEach(quarto => {
                let classeStatus = 'livre';
                let corTexto = 'green';
                
                if (quarto.status_quarto === 'Ocupado') {
                    classeStatus = 'ocupado';
                    corTexto = 'red';
                } else if (quarto.status_quarto === 'Em Limpeza') {
                    classeStatus = 'limpeza';
                    corTexto = '#ffc107';
                }

                const card = document.createElement('div');
                card.className = `card-quarto ${classeStatus}`;
                card.innerHTML = `
                    <h3>${quarto.numero_quarto}</h3>
                    <p>${quarto.nome_categoria}</p>
                    <p><strong>R$ ${quarto.valor_hora}</strong>/h</p>
                    <p style="font-size: 12px; font-weight: bold; color: ${corTexto};">${quarto.status_quarto}</p>
                `;

                card.addEventListener('click', () => {
                    if(quarto.status_quarto === 'Livre') {
                        abrirModal(quarto.numero_quarto);
                    } else if(quarto.status_quarto === 'Ocupado') {
                        abrirModalCheckout(quarto.numero_quarto);
                    } else if(quarto.status_quarto === 'Em Limpeza') {
                        perguntarLiberacaoLimpeza(quarto.numero_quarto);
                    }
                });

                divGrid.appendChild(card);
            });
        }
    })
    .catch(erro => console.error('Erro ao buscar quartos:', erro));
}

carregarQuartos();

// ==========================================
// MÁSCARAS AUTOMÁTICAS DE ENTRADA
// ==========================================
document.getElementById('data_nascimento').addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g, ''); // Remove tudo que não for número
    v = v.replace(/(\d{2})(\d)/, '$1/$2');     // Coloca a primeira barra
    v = v.replace(/(\d{2})(\d)/, '$1/$2');     // Coloca a segunda barra
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
// FUNÇÕES DO MODAL DE CHECK-IN
// ==========================================
function abrirModal(numeroQuarto) {
    document.getElementById('modalCheckin').style.display = 'block';
    document.getElementById('tituloModal').innerText = `Entrada - Quarto ${numeroQuarto}`;
    document.getElementById('quarto_selecionado').value = numeroQuarto;
}

function fecharModal() {
    document.getElementById('modalCheckin').style.display = 'none';
    document.getElementById('formCheckin').reset();
    document.getElementById('mensagemModal').innerText = '';
}

document.getElementById('formCheckin').addEventListener('submit', function(evento) {
    evento.preventDefault();

    // JS atua como tradutor: Transforma DD/MM/AAAA para AAAA-MM-DD
    const dataDigitada = document.getElementById('data_nascimento').value;
    const partesData = dataDigitada.split('/');
    let dataParaOBanco = '';
    
    // Confirma se o usuário digitou a data completa
    if (partesData.length === 3) {
        dataParaOBanco = `${partesData[2]}-${partesData[1]}-${partesData[0]}`;
    }

    const dadosCheckin = {
        quarto: document.getElementById('quarto_selecionado').value,
        nome: document.getElementById('nome').value,
        cpf: document.getElementById('cpf').value,
        telefone: document.getElementById('telefone').value,
        data_nascimento: dataParaOBanco, // Envia para o MySQL no formato americano
        placa_veiculo: document.getElementById('placa_veiculo').value,
        tempo_estimado: document.getElementById('tempo_estimado').value
    };

    fetch('../backend/api.php?acao=fazer_checkin', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dadosCheckin)
    })
    .then(resposta => resposta.json())
    .then(dados => {
        const divMensagem = document.getElementById('mensagemModal');
        if(dados.sucesso) {
            divMensagem.style.color = 'green';
            divMensagem.innerText = dados.mensagem;
            setTimeout(() => { fecharModal(); carregarQuartos(); }, 1500);
        } else {
            divMensagem.style.color = 'red';
            divMensagem.innerText = dados.mensagem;
        }
    });
});

// ==========================================
// FUNÇÕES DO MODAL DE CHECK-OUT
// ==========================================
function abrirModalCheckout(numeroQuarto) {
    document.getElementById('modalCheckout').style.display = 'block';
    document.getElementById('numQuartoCheckout').innerText = numeroQuarto;
    document.getElementById('quarto_checkout').value = numeroQuarto;

    fetch(`../backend/api.php?acao=obter_detalhes_checkout&quarto=${numeroQuarto}`)
    .then(res => res.json())
    .then(dados => {
        if(dados.sucesso) {
            document.getElementById('checkoutNome').innerText = dados.nome;
            document.getElementById('checkoutEntrada').innerText = dados.entrada;
            document.getElementById('checkoutTempo').innerText = dados.tempo_estimado;
            document.getElementById('checkoutReal').innerText = dados.tempo_real; 
            document.getElementById('checkoutTotal').innerText = dados.total;
            document.getElementById('id_locacao_checkout').value = dados.id_locacao;
        } else {
            alert(dados.mensagem);
            fecharModalCheckout();
        }
    });
}

function fecharModalCheckout() {
    document.getElementById('modalCheckout').style.display = 'none';
    document.getElementById('mensagemCheckout').innerText = '';
}

function confirmarCheckout() {
    const dadosCheckout = {
        id_locacao: document.getElementById('id_locacao_checkout').value,
        quarto: document.getElementById('quarto_checkout').value,
        valor_total: document.getElementById('checkoutTotal').innerText
    };

    fetch('../backend/api.php?acao=fazer_checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dadosCheckout)
    })
    .then(res => res.json())
    .then(dados => {
        const divMensagem = document.getElementById('mensagemCheckout');
        if(dados.sucesso) {
            divMensagem.style.color = 'green';
            divMensagem.innerText = dados.mensagem;
            setTimeout(() => { fecharModalCheckout(); carregarQuartos(); }, 1500);
        } else {
            divMensagem.style.color = 'red';
            divMensagem.innerText = dados.mensagem;
        }
    });
}

// ==========================================
// INTERAÇÃO COM QUARTO EM LIMPEZA
// ==========================================
function perguntarLiberacaoLimpeza(numeroQuarto) {
    document.getElementById('modalLimpeza').style.display = 'block';
    document.getElementById('numQuartoLimpeza').innerText = numeroQuarto;
    document.getElementById('quarto_limpeza_id').value = numeroQuarto;
}

function fecharModalLimpeza() {
    document.getElementById('modalLimpeza').style.display = 'none';
}

function confirmarLimpeza() {
    const numeroQuarto = document.getElementById('quarto_limpeza_id').value;
    
    fetch('../backend/api.php?acao=finalizar_limpeza', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ quarto: numeroQuarto })
    })
    .then(res => res.json())
    .then(dados => {
        if(dados.sucesso) {
            fecharModalLimpeza();
            carregarQuartos();
        } else {
            alert(dados.mensagem);
        }
    })
    .catch(erro => console.error('Erro ao liberar quarto:', erro));
}