<?php
session_start();
if (!isset($_SESSION['id_usuario'])) { header("Location: login.html"); exit; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel da Recepção</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .cabecalho { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn-admin { background: #ffc107; color: black; padding: 10px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .grid-quartos { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-top: 20px; }
        .card-quarto { background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); cursor: pointer; border-left: 5px solid gray; }
        .card-quarto h3 { margin: 0; font-size: 24px; }
        .card-quarto p { margin: 5px 0; color: #555; }
        .livre { border-left-color: #28a745; }
        .ocupado { border-left-color: #dc3545; }
        .limpeza { border-left-color: #ffc107; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); }
        .modal-conteudo { background-color: #fff; margin: 5% auto; padding: 20px; border-radius: 8px; width: 90%; max-width: 400px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .fechar-modal { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .fechar-modal:hover { color: black; }
        .modal input { display: block; width: 90%; margin-bottom: 10px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-confirmar { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 15px; }
        .btn-confirmar:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="cabecalho">
        <h2>Olá, <?= $_SESSION['nome'] ?>!</h2>
        <div>
            <?php if ($_SESSION['nivel'] == 'admin'): ?>
                <a href="painel.php" class="btn-admin">Painel Gerencial</a>
            <?php endif; ?> 
            <a href="login.html" style="margin-left: 15px; color: red; text-decoration: none;">Sair</a>
        </div>
    </div>

    <div class="grid-quartos" id="listaQuartos"></div>

    <div id="modalCheckin" class="modal">
        <div class="modal-conteudo">
            <span class="fechar-modal" onclick="fecharModal()">&times;</span>
            <h2 id="tituloModal">Entrada - Quarto</h2>
            
            <form id="formCheckin">
                <input type="hidden" id="quarto_selecionado">
                
                <label>Nome Completo *</label>
                <input type="text" id="nome" required>

                <label>CPF *</label>
                <input type="text" id="cpf" placeholder="000.000.000-00" maxlength="14" required>

                <label>Telefone *</label>
                <input type="text" id="telefone" placeholder="(00) 00000-0000" maxlength="15" required>

                <label>Data de Nascimento *</label>
                <input type="text" id="data_nascimento" placeholder="DD/MM/AAAA" maxlength="10" required>

                <label>Placa do Veículo (Opcional)</label>
                <input type="text" id="placa_veiculo" placeholder="ABC-1234">

                <label>Tempo Estimado (Horas) *</label>
                <input type="number" id="tempo_estimado" min="1" required>

                <button type="submit" class="btn-confirmar">Confirmar Check-in</button>
            </form>
            <div id="mensagemModal" style="margin-top: 10px; font-weight: bold;"></div>
        </div>
    </div>

    <div id="modalCheckout" class="modal">
        <div class="modal-conteudo">
            <span class="fechar-modal" onclick="fecharModalCheckout()">&times;</span>
            <h2>Finalizar Locação - Quarto <span id="numQuartoCheckout"></span></h2>
            
            <div id="detalhesCheckout" style="margin-top: 15px; line-height: 1.6;">
                <p><strong>Hóspede:</strong> <span id="checkoutNome">Carregando...</span></p>
                <p><strong>Entrada:</strong> <span id="checkoutEntrada">--/--/----</span></p>
                <p><strong>Tempo Estimado:</strong> <span id="checkoutTempo">0</span>h</p>
                <p style="color: #007bff;"><strong>Tempo Cobrado (com extras):</strong> <span id="checkoutReal">0</span></p>
                <hr>
                <h3 style="color: #dc3545;">Total a Pagar: R$ <span id="checkoutTotal">0.00</span></h3>
            </div>

            <input type="hidden" id="id_locacao_checkout">
            <input type="hidden" id="quarto_checkout">
            
            <button onclick="confirmarCheckout()" class="btn-confirmar" style="background: #dc3545;">Finalizar e Enviar para Limpeza</button>
            <div id="mensagemCheckout" style="margin-top: 10px; font-weight: bold;"></div>
        </div>
    </div>

    <div id="modalLimpeza" class="modal">
        <div class="modal-conteudo" style="text-align: center;">
            <span class="fechar-modal" onclick="fecharModalLimpeza()">&times;</span>
            <h2 style="color: #ffc107;">Quarto <span id="numQuartoLimpeza"></span></h2>
            
            <p style="margin-top: 15px; font-size: 18px;">A equipe finalizou a higienização deste quarto?</p>
            <p style="color: #555; font-size: 14px; margin-bottom: 20px;">Ao confirmar, o quarto voltará a ficar "Livre" (Verde) para locação.</p>

            <input type="hidden" id="quarto_limpeza_id">
            <button onclick="confirmarLimpeza()" class="btn-confirmar" style="background: #ffc107; color: black; font-weight: bold;">Sim, Liberar Quarto</button>
        </div>
    </div>

    <script src="app.js"></script>
</body>
</html>