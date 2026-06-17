<?php
session_start();
require_once 'conexao.php';
header('Content-Type: application/json');

// Garante o fuso horário correto do Brasil em todas as operações
date_default_timezone_set('America/Sao_Paulo');

$acao = $_GET['acao'] ?? '';
$dados = json_decode(file_get_contents("php://input"));

// ==========================================
//   ROTAS COMUNS (RECEPÇÃO E GERÊNCIA)
// ==========================================
switch ($acao) {
    
    case 'buscar_quartos':
        try {
            $sql = "SELECT q.numero_quarto, q.status_quarto, cq.nome_categoria, cq.valor_hora 
                    FROM quarto q 
                    INNER JOIN categoria_quarto cq ON q.codigo_categoria = cq.codigo_categoria 
                    ORDER BY q.numero_quarto ASC";
            $stmt = $pdo->query($sql);
            echo json_encode(['sucesso' => true, 'quartos' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
        exit;

    case 'fazer_checkin':
        if (!empty($dados->quarto) && !empty($dados->nome) && !empty($dados->cpf) && !empty($dados->telefone) && !empty($dados->data_nascimento) && !empty($dados->tempo_estimado)) {
            try {
                $pdo->beginTransaction();
                
                $sqlCliente = "INSERT INTO cliente (nome, cpf, telefone, data_nascimento) VALUES (:nome, :cpf, :telefone, :data_nascimento)";
                $stmtCliente = $pdo->prepare($sqlCliente);
                $stmtCliente->execute([
                    ':nome' => $dados->nome, 
                    ':cpf' => $dados->cpf, 
                    ':telefone' => $dados->telefone, 
                    ':data_nascimento' => $dados->data_nascimento
                ]);
                $id_cliente = $pdo->lastInsertId();

                $sqlLocacao = "INSERT INTO locacao (id_cliente, numero_quarto, data_hora_entrada, tempo_estimado_horas, placa_veiculo, status_caixa) 
                               VALUES (:id_cliente, :quarto, NOW(), :tempo, :placa, 'Aberto')";
                $stmtLocacao = $pdo->prepare($sqlLocacao);
                $stmtLocacao->execute([
                    ':id_cliente' => $id_cliente, 
                    ':quarto' => $dados->quarto, 
                    ':tempo' => $dados->tempo_estimado, 
                    ':placa' => $dados->placa_veiculo ?? null
                ]);

                $sqlQuarto = "UPDATE quarto SET status_quarto = 'Ocupado' WHERE numero_quarto = :quarto";
                $stmtQuarto = $pdo->prepare($sqlQuarto);
                $stmtQuarto->execute([':quarto' => $dados->quarto]);

                $pdo->commit();
                echo json_encode(['sucesso' => true, 'mensagem' => 'Check-in realizado com sucesso!']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos obrigatórios.']);
        }
        exit;

    case 'obter_detalhes_checkout':
        if (!empty($_GET['quarto'])) {
            try {
                $sql = "SELECT l.id_locacao, l.data_hora_entrada, l.tempo_estimado_horas, c.nome, cq.valor_hora 
                        FROM locacao l
                        INNER JOIN cliente c ON l.id_cliente = c.id_cliente
                        INNER JOIN quarto q ON l.numero_quarto = q.numero_quarto
                        INNER JOIN categoria_quarto cq ON q.codigo_categoria = cq.codigo_categoria
                        WHERE l.numero_quarto = :quarto AND l.data_hora_saida IS NULL LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':quarto' => $_GET['quarto']]);
                $locacao = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($locacao) {
                    $entrada = new DateTime($locacao['data_hora_entrada']);
                    $agora = new DateTime();
                    
                    $intervalo = $entrada->diff($agora);
                    $horas_reais = ($intervalo->days * 24) + $intervalo->h;
                    
                    // Tolerância de 15 minutos
                    if ($intervalo->i > 15) { 
                        $horas_reais++; 
                    }
                    
                    $horas_cobradas = max($horas_reais, $locacao['tempo_estimado_horas']);
                    $valorTotal = $horas_cobradas * $locacao['valor_hora'];
                    
                    echo json_encode([
                        'sucesso' => true,
                        'id_locacao' => $locacao['id_locacao'],
                        'nome' => $locacao['nome'],
                        'entrada' => $entrada->format('d/m/Y H:i'),
                        'tempo_estimado' => $locacao['tempo_estimado_horas'],
                        'tempo_real' => $horas_cobradas . 'h',
                        'total' => number_format($valorTotal, 2, '.', '')
                    ]);
                } else {
                    echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhuma locação ativa encontrada.']);
                }
            } catch (Exception $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
            }
        }
        exit;

    case 'fazer_checkout':
        if (!empty($dados->id_locacao) && !empty($dados->quarto) && !empty($dados->valor_total)) {
            try {
                $pdo->beginTransaction();
                
                $sqlLocacao = "UPDATE locacao SET data_hora_saida = NOW(), valor_total = :total WHERE id_locacao = :id";
                $stmtLocacao = $pdo->prepare($sqlLocacao);
                $stmtLocacao->execute([':total' => $dados->valor_total, ':id' => $dados->id_locacao]);

                $sqlQuarto = "UPDATE quarto SET status_quarto = 'Em Limpeza' WHERE numero_quarto = :quarto";
                $stmtQuarto = $pdo->prepare($sqlQuarto);
                $stmtQuarto->execute([':quarto' => $dados->quarto]);

                $pdo->commit();
                echo json_encode(['sucesso' => true, 'mensagem' => 'Check-out efetuado! Quarto enviado para a limpeza.']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
            }
        }
        exit;

    case 'finalizar_limpeza':
        if (!empty($dados->quarto)) {
            try {
                $sql = "UPDATE quarto SET status_quarto = 'Livre' WHERE numero_quarto = :quarto";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':quarto' => $dados->quarto]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Quarto higienizado e liberado!']);
            } catch (Exception $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
            }
        }
        exit;
}

// ==========================================
//   BARREIRA DE SEGURANÇA ADMINISTRATIVA
// ==========================================
if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] !== 'admin') {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso negado.']);
    exit;
}

// ==========================================
//   ROTAS EXCLUSIVAS DO ADMINISTRADOR
// ==========================================
switch ($acao) {
    
    case 'dados_painel':
        try {
            $sqlFat = "SELECT SUM(valor_total) as total FROM locacao WHERE valor_total IS NOT NULL AND status_caixa = 'Aberto'";
            $stmtFat = $pdo->query($sqlFat);
            $faturamento = $stmtFat->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $sqlCat = "SELECT * FROM categoria_quarto";
            $stmtCat = $pdo->query($sqlCat);
            $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['sucesso' => true, 'faturamento' => $faturamento, 'categorias' => $categorias]);
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
        break;

    case 'atualizar_preco':
        if (!empty($dados->codigo) && !empty($dados->novo_preco)) {
            try {
                $sql = "UPDATE categoria_quarto SET valor_hora = :preco WHERE codigo_categoria = :codigo";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':preco' => $dados->novo_preco, ':codigo' => $dados->codigo]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Preço atualizado com sucesso!']);
            } catch (Exception $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
            }
        }
        break;

    case 'fechar_caixa':
        try {
            $sql = "UPDATE locacao SET status_caixa = 'Fechado' WHERE status_caixa = 'Aberto' AND valor_total IS NOT NULL";
            $pdo->query($sql);
            echo json_encode(['sucesso' => true, 'mensagem' => 'Faturamento fechado com sucesso! Turno encerrado.']);
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
        break;

    case 'buscar_historico':
        try {
            $sql = "SELECT l.id_locacao, l.data_hora_entrada, l.data_hora_saida, l.valor_total, c.nome, q.numero_quarto
                    FROM locacao l
                    INNER JOIN cliente c ON l.id_cliente = c.id_cliente
                    INNER JOIN quarto q ON l.numero_quarto = q.numero_quarto
                    WHERE l.data_hora_saida IS NOT NULL
                    ORDER BY l.data_hora_saida DESC LIMIT 50";
            $stmt = $pdo->query($sql);
            $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($historico as &$linha) {
                $linha['entrada'] = date('d/m/Y H:i', strtotime($linha['data_hora_entrada']));
                $linha['saida'] = date('d/m/Y H:i', strtotime($linha['data_hora_saida']));
            }
            echo json_encode(['sucesso' => true, 'historico' => $historico]);
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
        break;

    case 'buscar_historico_caixa':
        try {
            $sql = "SELECT DATE(data_hora_saida) as data_fechamento, SUM(valor_total) as total_dia, COUNT(id_locacao) as qtd_locacoes
                    FROM locacao WHERE status_caixa = 'Fechado' AND data_hora_saida IS NOT NULL
                    GROUP BY DATE(data_hora_saida) ORDER BY data_fechamento DESC LIMIT 30";
            $stmt = $pdo->query($sql);
            $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($historico as &$linha) {
                $linha['data_formatada'] = date('d/m/Y', strtotime($linha['data_fechamento']));
            }
            echo json_encode(['sucesso' => true, 'historico' => $historico]);
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
        break;

    case 'buscar_historico_mes':
        try {
            $sql = "SELECT DATE_FORMAT(data_hora_saida, '%m/%Y') as mes_ano, 
                           SUM(valor_total) as total_mes, 
                           COUNT(id_locacao) as qtd_locacoes
                    FROM locacao 
                    WHERE status_caixa = 'Fechado' AND data_hora_saida IS NOT NULL
                    GROUP BY YEAR(data_hora_saida), MONTH(data_hora_saida) 
                    ORDER BY YEAR(data_hora_saida) DESC, MONTH(data_hora_saida) DESC 
                    LIMIT 24";
            
            $stmt = $pdo->query($sql);
            $historico_mes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['sucesso' => true, 'historico' => $historico_mes]);
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
        }
        break;

    case 'adicionar_quarto':
        if (!empty($dados->numero) && !empty($dados->categoria)) {
            try {
                $stmtCheck = $pdo->prepare("SELECT numero_quarto FROM quarto WHERE numero_quarto = :numero");
                $stmtCheck->execute([':numero' => $dados->numero]);
                if ($stmtCheck->rowCount() > 0) {
                    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro: Este número de quarto já existe!']);
                    exit;
                }
                $sql = "INSERT INTO quarto (numero_quarto, codigo_categoria, status_quarto) VALUES (:numero, :categoria, 'Livre')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':numero' => $dados->numero, ':categoria' => $dados->categoria]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Novo quarto construído e liberado!']);
            } catch (Exception $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
            }
        }
        break;

    case 'editar_quarto':
        if (!empty($dados->numero) && !empty($dados->nova_categoria)) {
            try {
                $sql = "UPDATE quarto SET codigo_categoria = :categoria WHERE numero_quarto = :numero";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':categoria' => $dados->nova_categoria, ':numero' => $dados->numero]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Categoria do quarto atualizada com sucesso!']);
            } catch (Exception $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()]);
            }
        }
        break;

    default:
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação não encontrada ou inválida.']);
        break;
}
?>