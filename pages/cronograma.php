<?php
session_start();

// 1. Verifica se a variável de sessão existe e se o usuário está logado
if (!isset($_SESSION['id']) || !isset($_SESSION['nome'])) {
    header("Location: ../login.php");
    exit();
}

include '../conexao.php';

// 2. Verifica se o ID da sessão ainda é válido no banco de dados
$id = intval($_SESSION['id']);
$res = $conn->query("SELECT foto, google_email FROM usuario WHERE id_usuario = $id");
$dadosUser = $res->fetch_assoc();

if (!$dadosUser) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// E-mail da conta Google vinculada: é ELE que define quais cronogramas aparecem
$emailGoogle = $dadosUser['google_email'] ?? null;

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('America/Sao_Paulo');
$hoje = date('Y-m-d');

// CONEXÃO COM O BANCO (PDO)
try {
    $pdo = new PDO("mysql:host=localhost;dbname=phronesis;charset=utf8", "root", "Home@spSENAI2025!");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro banco: " . $e->getMessage());
}

// ---- LOGOUT (desvincula o Google desta conta do Phronesis) ----
if (isset($_GET['logout'])) {
    $conn->query("UPDATE usuario SET google_token = NULL, google_email = NULL WHERE id_usuario = $id");
    unset($_SESSION['upload_token'], $_SESSION['google_email'], $_SESSION['temp_cronograma'], $_SESSION['temp_params'], $_SESSION['editando_id']);
    header('Location: meu_calendario.php');
    exit;
}

// Sem token ou sem e-mail vinculado: volta para a tela de conexão
if (!isset($_SESSION['upload_token']) || empty($emailGoogle)) {
    header('Location: meu_calendario.php');
    exit;
}

// Configuração Google
$client = new Google\Client();
$client->setAuthConfig('../credentials.json');
$client->setAccessToken($_SESSION['upload_token']);

// Token vencido: a tela de conexão renova e devolve para cá
if ($client->isAccessTokenExpired()) {
    header('Location: meu_calendario.php');
    exit;
}
$service = new Google\Service\Calendar($client);

// Busca matérias para o formulário
$sql = "SELECT id_materia, nome_materia, cor_hex FROM materia ORDER BY nome_materia ASC";
$consulta = $pdo->query($sql);
$lista_materias_db = $consulta->fetchAll(PDO::FETCH_ASSOC);

$materias_com_conteudos = [];
try {
    $sql_conteudos = "SELECT m.nome_materia, t.nome_topico AS nome_conteudo
                      FROM materia m
                      LEFT JOIN materia_topico t ON m.id_materia = t.id_materia 
                      ORDER BY m.nome_materia ASC, t.nome_topico ASC";
    $stmt_cont = $pdo->query($sql_conteudos);
    $lista_completa = $stmt_cont->fetchAll(PDO::FETCH_ASSOC);

    if (empty($lista_completa) || (count($lista_completa) === 1 && empty($lista_completa[0]['nome_conteudo']))) {
        $sql_conteudos = "SELECT m.nome_materia, c.nome_conteudo 
                          FROM materia m
                          LEFT JOIN conteudo c ON m.id_materia = c.id_materia 
                          ORDER BY m.nome_materia ASC, c.nome_conteudo ASC";
        $stmt_cont = $pdo->query($sql_conteudos);
        $lista_completa = $stmt_cont->fetchAll(PDO::FETCH_ASSOC);
    }

    foreach ($lista_completa as $linha) {
        $mat = $linha['nome_materia'];
        if (!isset($materias_com_conteudos[$mat])) {
            $materias_com_conteudos[$mat] = [];
        }
        if (!empty($linha['nome_conteudo'])) {
            $materias_com_conteudos[$mat][] = $linha['nome_conteudo'];
        }
    }
} catch (Exception $e) {
    $consulta_fallback = $pdo->query("SELECT nome_materia FROM materia ORDER BY nome_materia ASC");
    while ($row = $consulta_fallback->fetch(PDO::FETCH_ASSOC)) {
        $materias_com_conteudos[$row['nome_materia']] = [];
    }
}

// ---- BUSCA CRONOGRAMAS DA CONTA GOOGLE (compartilhados entre contas com o mesmo e-mail) ----
$stmtCronos = $pdo->prepare("SELECT id_cronograma, titulo_cronograma AS titulo, google_calendar_id AS calendar_id, DATE_FORMAT(data_criacao, '%d/%m/%Y %H:%i') AS data FROM cronograma WHERE google_email = ? ORDER BY id_cronograma DESC");
$stmtCronos->execute([$emailGoogle]);
$meus_cronogramas_db = $stmtCronos->fetchAll(PDO::FETCH_ASSOC);

$msg_feedback = "";
$id_visualizacao = $_GET['ver'] ?? null;
$mostrar_modal = false;

$calendar_id_exibir = null;
$titulo_exibir = '';
$materias_checklist = [];

if ($id_visualizacao !== null) {
    $stmtVis = $pdo->prepare("SELECT google_calendar_id, titulo_cronograma FROM cronograma WHERE id_cronograma = ? AND google_email = ?");
    $stmtVis->execute([intval($id_visualizacao), $emailGoogle]);
    $crono_ver = $stmtVis->fetch(PDO::FETCH_ASSOC);

    if ($crono_ver) {
        $id_visualizacao = intval($id_visualizacao);
        $calendar_id_exibir = $crono_ver['google_calendar_id'];
        $titulo_exibir = $crono_ver['titulo_cronograma'];

        // --- BUSCA AS MATÉRIAS SELECIONADAS PARA A CHECKLIST DO CRONOGRAMA ATIVO ---
        $sql_mat_crono = "SELECT DISTINCT m.id_materia, m.nome_materia, m.cor_hex 
                          FROM cronograma_item ci
                          JOIN materia m ON ci.id_materia = m.id_materia
                          WHERE ci.id_cronograma = ?";
        $stmt_mat_crono = $pdo->prepare($sql_mat_crono);
        $stmt_mat_crono->execute([$id_visualizacao]);
        $lista_mat_crono = $stmt_mat_crono->fetchAll(PDO::FETCH_ASSOC);

        // Todos os status deste cronograma de uma vez (evita uma consulta por tópico)
        $stmt_status = $pdo->prepare("SELECT id_topico, status FROM cronograma_topico_status WHERE id_cronograma = ?");
        $stmt_status->execute([$id_visualizacao]);
        $mapa_status = [];
        foreach ($stmt_status->fetchAll(PDO::FETCH_ASSOC) as $s) {
            $mapa_status[(int)$s['id_topico']] = (int)$s['status'];
        }

        $stmt_topico = $pdo->prepare("SELECT id_topico, nome_topico FROM materia_topico WHERE id_materia = ? ORDER BY nome_topico ASC");

        foreach ($lista_mat_crono as $mat) {
            $id_materia = $mat['id_materia'];

            $stmt_topico->execute([$id_materia]);
            $todos_topicos = $stmt_topico->fetchAll(PDO::FETCH_ASSOC);

            $topicos_formatados = [];
            $concluidos = 0;
            $total = count($todos_topicos);

            foreach ($todos_topicos as $top) {
                $id_topico = (int)$top['id_topico'];
                $checado = (($mapa_status[$id_topico] ?? 0) === 1);
                if ($checado) $concluidos++;

                $topicos_formatados[] = [
                    'id_topico' => $id_topico,
                    'nome_topico' => $top['nome_topico'],
                    'checado' => $checado
                ];
            }

            $porcentagem = $total > 0 ? round(($concluidos / $total) * 100) : 0;

            $materias_checklist[] = [
                'id_materia' => $id_materia,
                'nome_materia' => $mat['nome_materia'],
                'cor_hex' => $mat['cor_hex'] ?? '#7c3aed',
                'porcentagem' => $porcentagem,
                'topicos' => $topicos_formatados
            ];
        }
    } else {
        // Cronograma inexistente ou de outra conta Google
        $id_visualizacao = null;
    }
}

// --- EXCLUSÃO ---
if (isset($_GET['deletar_salvo'])) {
    $id_del = intval($_GET['deletar_salvo']);

    $stmtCheck = $pdo->prepare("SELECT google_calendar_id FROM cronograma WHERE id_cronograma = ? AND google_email = ?");
    $stmtCheck->execute([$id_del, $emailGoogle]);
    $crono_del = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($crono_del) {
        $calendarId = $crono_del['google_calendar_id'] ?? null;
        try {
            if ($calendarId && $calendarId !== 'primary') {
                $service->calendars->delete($calendarId);
            }
            $stmtDel = $pdo->prepare("DELETE FROM `cronograma` WHERE `id_cronograma` = ? AND `google_email` = ?");
            $stmtDel->execute([$id_del, $emailGoogle]);

            // Limpa o progresso dos tópicos (a tabela não tem chave estrangeira)
            $pdo->prepare("DELETE FROM `cronograma_topico_status` WHERE `id_cronograma` = ?")->execute([$id_del]);

            $msg_feedback = "🗑️ Cronograma removido com sucesso!";
        } catch (Exception $e) {
            $msg_feedback = "⚠️ Erro ao remover: " . $e->getMessage();
        }
    }
    header('Location: cronograma.php?msg=' . urlencode($msg_feedback));
    exit();
}

// Prepara edição
$dados_edicao = null;
if (isset($_GET['editar'])) {
    $id_ed = intval($_GET['editar']);
    $stmtEd = $pdo->prepare("SELECT google_calendar_id, titulo_cronograma FROM cronograma WHERE id_cronograma = ? AND google_email = ?");
    $stmtEd->execute([$id_ed, $emailGoogle]);
    $crono_ed = $stmtEd->fetch(PDO::FETCH_ASSOC);
    if ($crono_ed) {
        $_SESSION['editando_id'] = $id_ed;
        $id_visualizacao = null;
        $calendar_id_exibir = null;

        // Recupera do banco as escolhas feitas quando o cronograma foi criado
        $stmtIt = $pdo->prepare("SELECT id_materia, data_estudo, data_fim FROM cronograma_item WHERE id_cronograma = ? ORDER BY data_estudo ASC");
        $stmtIt->execute([$id_ed]);
        $itens_ed = $stmtIt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($itens_ed)) {
            $dias_ed = [];
            $mat_dia_ed = [];
            $hora_ed = null;
            $horas_ed = null;

            foreach ($itens_ed as $it) {
                $ini = new DateTime($it['data_estudo']);
                $fim = new DateTime($it['data_fim']);
                $dn = (int)$ini->format('N');
                $dias_ed[$dn] = $dn;

                $idm = (int)$it['id_materia'];
                if (!in_array($idm, $mat_dia_ed[$dn] ?? [], true)) {
                    $mat_dia_ed[$dn][] = $idm;
                }

                // Hora de início = menor horário do dia; duração = tamanho de uma sessão
                $h = $ini->format('H:i');
                if ($hora_ed === null || $h < $hora_ed) $hora_ed = $h;
                if ($horas_ed === null) {
                    $horas_ed = round(($fim->getTimestamp() - $ini->getTimestamp()) / 3600, 2);
                }
            }
            ksort($dias_ed);

            $primeiro_dia = substr($itens_ed[0]['data_estudo'], 0, 10);
            $ultimo_dia   = substr($itens_ed[count($itens_ed) - 1]['data_estudo'], 0, 10);
            // A data de início não pode ficar no passado ao regerar
            $inicio_ed = max($primeiro_dia, $hoje);
            $fim_ed    = ($ultimo_dia >= $inicio_ed) ? $ultimo_dia : '';

            $edit_dados = [
                'gerar'           => 1,
                'nome_cronograma' => $crono_ed['titulo_cronograma'],
                'data_inicio'     => $inicio_ed,
                'data_fim'        => $fim_ed,
                'dias_semana'     => array_values($dias_ed),
                'materias_dia'    => $mat_dia_ed,
                'hora_inicio'     => $hora_ed,
                'horas_estudo'    => (string)$horas_ed
            ];
        }
    }
}

if (isset($_GET['msg'])) $msg_feedback = $_GET['msg'];

// --- GERAÇÃO TEMPORÁRIA (cada dia da semana tem as suas próprias matérias) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar'])) {
    $data_inicio = new DateTime($_POST['data_inicio']);
    $data_fim = new DateTime($_POST['data_fim']);
    $data_hoje_obj = new DateTime($hoje);
    $dias_permitidos = array_map('intval', $_POST['dias_semana'] ?? []);
    $intervalo_seguranca = $data_inicio->diff($data_fim);

    $nomes_dias = [
        1 => 'Segunda-Feira', 2 => 'Terça-Feira', 3 => 'Quarta-Feira', 4 => 'Quinta-Feira',
        5 => 'Sexta-Feira', 6 => 'Sábado', 7 => 'Domingo'
    ];
    $nomes_map = array_column($lista_materias_db, 'nome_materia', 'id_materia');

    // Monta o mapa  dia da semana => [ids das matérias daquele dia]
    $materias_por_dia = [];
    $dia_sem_materia = null;
    $max_materias_dia = 0;

    foreach ($dias_permitidos as $d) {
        $ids = [];
        foreach ((array)($_POST['materias_dia'][$d] ?? []) as $idm) {
            $idm = (int)$idm;
            if (isset($nomes_map[$idm]) && !in_array($idm, $ids, true)) {
                $ids[] = $idm;
            }
        }
        if (empty($ids) && $dia_sem_materia === null) {
            $dia_sem_materia = $d;
        }
        $materias_por_dia[$d] = $ids;
        $max_materias_dia = max($max_materias_dia, count($ids));
    }

    // --- VALIDAÇÃO DO LIMITE DE 24H (usa o dia com mais matérias) ---
    $horas_estudo_val = (float) ($_POST['horas_estudo'] ?? 0);
    $partes_hora = explode(':', $_POST['hora_inicio'] ?? '00:00');
    $inicio_em_minutos = ((int)$partes_hora[0] * 60) + (int)($partes_hora[1] ?? 0);
    $total_em_minutos = (int) round($horas_estudo_val * 60 * $max_materias_dia);
    $fim_em_minutos = $inicio_em_minutos + $total_em_minutos;

    if (empty($dias_permitidos)) {
        $msg_feedback = "❌ Erro: Selecione ao menos um dia da semana.";
    } elseif ($dia_sem_materia !== null) {
        $msg_feedback = "❌ Erro: Escolha ao menos uma matéria para " . ($nomes_dias[$dia_sem_materia] ?? 'o dia selecionado') . ".";
    } elseif ($data_inicio < $data_hoje_obj) {
        $msg_feedback = "❌ Erro: A data de início não pode ser anterior a hoje.";
    } elseif ($data_fim < $data_inicio) {
        $msg_feedback = "❌ Erro: Data final não pode ser menor que a inicial.";
    } elseif ($intervalo_seguranca->days > 366) {
        $msg_feedback = "❌ Erro: O período máximo permitido para um cronograma é de 1 ano.";
    } elseif ($horas_estudo_val <= 0) {
        $msg_feedback = "❌ Erro: A duração deve ser maior que zero.";
    } elseif ($horas_estudo_val >= 24) {
        $msg_feedback = "❌ O limite de horas permitidas é abaixo de 24h.";
    } elseif ($fim_em_minutos > 1440) {
        $msg_feedback = "❌ O limite de horas permitidas é abaixo de 24h. Com "
            . $max_materias_dia . " matéria(s) de " . $horas_estudo_val
            . "h começando às " . sprintf('%02d:%02d', $partes_hora[0], $partes_hora[1] ?? 0)
            . ", o cronograma passaria da meia-noite. Reduza a duração, o nº de matérias do dia ou adiante o horário de início.";
    } else {
        $_SESSION['ultimo_nome'] = !empty($_POST['nome_cronograma']) ? $_POST['nome_cronograma'] : "Plano de Estudos";

        $cronograma_gerado = [];
        $data_atual = clone $data_inicio;
        $intervalo = $data_inicio->diff($data_fim);

        for ($i = 0; $i <= $intervalo->days; $i++) {
            $dia_n = (int)$data_atual->format('N');

            // Só gera para dias marcados, usando as matérias DAQUELE dia
            if (isset($materias_por_dia[$dia_n])) {
                $horario_acumulado = new DateTime($data_atual->format('Y-m-d') . ' ' . $_POST['hora_inicio']);

                foreach ($materias_por_dia[$dia_n] as $id_da_vez) {
                    $inicio_sessao = clone $horario_acumulado;
                    $fim_sessao = clone $inicio_sessao;
                    $fim_sessao->modify('+' . $horas_estudo_val . ' hours');

                    $cronograma_gerado[] = [
                        'id_materia' => $id_da_vez,
                        'materia' => $nomes_map[$id_da_vez],
                        'inicio_iso' => $inicio_sessao->format('Y-m-d\TH:i:s'),
                        'fim_iso' => $fim_sessao->format('Y-m-d\TH:i:s')
                    ];

                    $horario_acumulado->modify('+' . $horas_estudo_val . ' hours');
                }
            }
            $data_atual->modify('+1 day');
        }
        $_SESSION['temp_cronograma'] = $cronograma_gerado;
        $_SESSION['temp_params'] = $_POST;
        $mostrar_modal = true;
    }
}

// --- SALVAR ---
if (isset($_POST['sincronizar']) && isset($_SESSION['temp_cronograma'])) {
    set_time_limit(300);
    try {
        $pdo->beginTransaction();
        $id_usuario_logado = $id;
        $calendarIdParaUso = null;
        $id_cronograma_existente = null;

        if (isset($_SESSION['editando_id'])) {
            $id_cronograma_existente = intval($_SESSION['editando_id']);
            $stmt = $pdo->prepare("SELECT google_calendar_id FROM cronograma WHERE id_cronograma = ? AND google_email = ?");
            $stmt->execute([$id_cronograma_existente, $emailGoogle]);
            $calendarIdParaUso = $stmt->fetchColumn();
        }

        if ($id_cronograma_existente && $calendarIdParaUso) {
            $stmtUpd = $pdo->prepare("UPDATE `cronograma` SET `titulo_cronograma` = ? WHERE `id_cronograma` = ? AND `google_email` = ?");
            $stmtUpd->execute([$_SESSION['ultimo_nome'], $id_cronograma_existente, $emailGoogle]);

            $pdo->prepare("DELETE FROM `cronograma_item` WHERE `id_cronograma` = ?")->execute([$id_cronograma_existente]);
            $id_cronograma_db = $id_cronograma_existente;

            try {
                $events = $service->events->listEvents($calendarIdParaUso);
                foreach ($events->getItems() as $event) {
                    $service->events->delete($calendarIdParaUso, $event->getId());
                }
            } catch (Exception $e) {}
        } else {
            $newCalendar = new Google\Service\Calendar\Calendar();
            $newCalendar->setSummary('Phronesis: ' . $_SESSION['ultimo_nome']);
            $newCalendar->setTimeZone('America/Sao_Paulo');
            $createdCalendar = $service->calendars->insert($newCalendar);
            $calendarIdParaUso = $createdCalendar->getId();

            $stmtCrono = $pdo->prepare("INSERT INTO `cronograma` (`id_usuario`, `google_email`, `titulo_cronograma`, `google_calendar_id`, `data_criacao`) VALUES (?, ?, ?, ?, NOW())");
            $stmtCrono->execute([$id_usuario_logado, $emailGoogle, $_SESSION['ultimo_nome'], $calendarIdParaUso]);
            $id_cronograma_db = $pdo->lastInsertId();
        }

        foreach ($_SESSION['temp_cronograma'] as $evento) {
            $event = new Google\Service\Calendar\Event([
                'summary' => 'Estudo: ' . $evento['materia'],
                'start' => ['dateTime' => $evento['inicio_iso'], 'timeZone' => 'America/Sao_Paulo'],
                'end'   => ['dateTime' => $evento['fim_iso'], 'timeZone' => 'America/Sao_Paulo'],
            ]);
            $google_event = $service->events->insert($calendarIdParaUso, $event);

            $stmtItem = $pdo->prepare("INSERT INTO `cronograma_item` (`id_cronograma`, `id_materia`, `google_event_id`, `data_estudo`, `data_fim`) VALUES (?, ?, ?, ?, ?)");
            $stmtItem->execute([
                $id_cronograma_db,
                $evento['id_materia'],
                $google_event->getId(),
                str_replace('T', ' ', $evento['inicio_iso']),
                str_replace('T', ' ', $evento['fim_iso'])
            ]);
        }

        $pdo->commit();

        $idx_final = $id_cronograma_db;
        unset($_SESSION['editando_id'], $_SESSION['temp_cronograma'], $_SESSION['temp_params']);

        header('Location: cronograma.php?ver=' . $idx_final . '&msg=' . urlencode("✅ Cronograma salvo com sucesso!"));
        exit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $msg_feedback = "❌ Erro: " . $e->getMessage();
    }
}

// Valores dos campos de data (já escapados) para o formulário
// DADOS DO FORMULÁRIO: ao abrir a página (GET) ele sempre começa vazio.
// Só reaproveita o que acabou de ser enviado (ex.: erro de validação) ou, se o
// salvamento falhar, os parâmetros do cronograma que estava sendo confirmado.
if (isset($_POST['gerar'])) {
    $form_dados = $_POST;
} elseif (isset($_POST['sincronizar'])) {
    $form_dados = $_SESSION['temp_params'] ?? [];
} else {
    // Editando: vem preenchido com o que já foi salvo. Abertura normal: vazio.
    $form_dados = $edit_dados ?? [];
    // Abertura da página: descarta qualquer geração anterior não salva
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        unset($_SESSION['temp_cronograma'], $_SESSION['temp_params']);
    }
}

$valor_data_inicio = htmlspecialchars($form_dados['data_inicio'] ?? $hoje);
$valor_data_fim    = htmlspecialchars($form_dados['data_fim'] ?? '');
// O mínimo do campo "fim" acompanha o início escolhido (nunca antes de hoje)
$min_data_fim = (!empty($form_dados['data_inicio']) && $form_dados['data_inicio'] >= $hoje)
    ? htmlspecialchars($form_dados['data_inicio'])
    : $hoje;

// Estado inicial dos dias/matérias: tudo DESMARCADO na primeira visita;
// depois, o que o usuário enviou/escolheu
$dias_marcados = isset($form_dados['gerar'])
    ? array_map('intval', $form_dados['dias_semana'] ?? [])
    : [];
$materias_dia_post = $form_dados['materias_dia'] ?? [];
$materias_json = array_map(fn($m) => [
    'id'   => (int)$m['id_materia'],
    'nome' => $m['nome_materia'],
    'cor'  => $m['cor_hex'] ?? '#7c3aed'
], $lista_materias_db);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phronesis - Cronograma</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/cronograma.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="icon" type="image/png" href="../assets/img/logos/favicon.webp">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Aplica o tema salvo antes de renderizar (evita "flash" branco)
        (function () {
            const savedTheme = localStorage.getItem('site-theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-theme');
            }
        })();
        // OBS: a função salvarProgressoTopico() fica apenas em assets/js/lib/cronograma.js
    </script>
</head>

<body>

    <?php include '../interface/header.php'; ?>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div id="confirmModal" class="modal" style="<?= $mostrar_modal ? 'display:flex;' : '' ?>">
        <div class="modal-content">
            <h3>📚 Confirmar Cronograma</h3>
            <p>Título: <b><?= htmlspecialchars($_SESSION['ultimo_nome'] ?? '') ?></b></p>
            <form method="POST">
                <button type="submit" name="sincronizar" class="btn-sync">Confirmar e Salvar</button>
                <button type="button" onclick="fecharModalConfirmacao()" class="btn-cancel-modal">Cancelar</button>
            </form>
        </div>
    </div>

    <div id="loadingScreen" class="modal" style="display: none;">
        <div class="modal-content loader-box">
            <h3 id="loadingText">🗓️ Sincronizando com o Google Agenda...</h3>
            <p id="loadingSubtext">Isso pode levar alguns segundos dependendo do tamanho do seu cronograma.</p>
            <div class="progress-bar-container">
                <div class="progress-bar-fill"></div>
            </div>
        </div>
    </div>

    <main class="main-layout">

        <section class="container-principal">
            <?php if ($msg_feedback): ?>
                <div class="alert-message"><?= htmlspecialchars($msg_feedback) ?></div>
            <?php endif; ?>

            <?php if ($calendar_id_exibir && !isset($_GET['editar'])): ?>
                <div class="agenda-header">
                    <h3><i class="fa-solid fa-calendar-days"></i> Agenda: <?= htmlspecialchars($titulo_exibir) ?></h3>
                    <a href="cronograma.php" class="btn-novo-crono">+ Novo Cronograma</a>
                </div>

                <div class="view-toggle">
                    <button type="button" data-mode="MONTH">Mês</button>
                    <button type="button" data-mode="WEEK">Semana</button>
                    <button type="button" data-mode="AGENDA">Agenda</button>
                </div>
                <iframe src="https://calendar.google.com/calendar/embed?src=<?= urlencode($calendar_id_exibir) ?>&ctz=America%2FSao_Paulo&wkst=1&bgcolor=%23fbf9ff&color=%237c3aed&showCalendars=0&showTabs=1" class="calendar-embed" style="border: none;" allowfullscreen></iframe>

                <div class="checklist-estudos-box">
                    <br><h3><i class="fa-solid fa-list-check"></i> Gerenciar Conteúdos Concluídos</h3>
                    <p style="font-size: 0.85rem; color: #a0aec0; margin-bottom: 15px;">Monitore seu avanço marcando as matérias estudadas neste cronograma:</p>

                    <div class="checklist-wrapper"> <hr><br>
                        <?php if (empty($materias_checklist)): ?>
                            <p style="color: #cbd5e1; font-style: italic; font-size: 0.9rem; text-align: center; padding: 20px 0;">
                                Nenhuma matéria vinculada encontrada para este cronograma.
                            </p>
                        <?php else: ?>
                            <?php foreach ($materias_checklist as $mc): ?>
                                <div class="checklist-item-container" id="checklist-materia-<?= $mc['id_materia'] ?>" style="display: block; width: 100%; margin-bottom: 10px;">

                                    <div class="checklist-folder-header" onclick="toggleChecklistFolder(<?= $mc['id_materia'] ?>)">
                                        <div style="display: flex; align-items: center; gap: 10px; font-weight: 500;">
                                            <i class="fa-solid fa-folder" style="color: <?= htmlspecialchars($mc['cor_hex']) ?>;"></i>
                                            <span><?= htmlspecialchars($mc['nome_materia']) ?></span>
                                            <i class="fa-solid fa-chevron-down fold-chevron" id="chk-chevron-<?= $mc['id_materia'] ?>" style="font-size: 0.8rem; margin-left: 5px; transition: transform 0.2s;"></i>
                                        </div>
                                        <div class="checklist-progress-container" style="display: flex; align-items: center; gap: 10px; min-width: 140px; justify-content: flex-end;">
                                            <div class="checklist-bar-bg" style="width: 100px; height: 8px; background-color: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                                <div class="checklist-bar-fill" id="chk-fill-<?= $mc['id_materia'] ?>" 
                                                    style="width: <?= $mc['porcentagem'] ?>%; height: 100%; background: <?= htmlspecialchars($mc['cor_hex']) ?>; border-radius: 4px; transition: width 0.3s ease-in-out;">
                                                </div>
                                            </div>
                                            <span class="checklist-percent-text" id="chk-text-<?= $mc['id_materia'] ?>" style="font-size: 0.85rem; font-weight: 600; color: #4a5568; min-width: 35px; text-align: right;">
                                                <?= $mc['porcentagem'] ?>%
                                            </span>
                                        </div>
                                    </div>

                                    <div class="checklist-folder-content" id="chk-content-<?= $mc['id_materia'] ?>" style="display: none; clear: both; width: 100%;">
                                        <?php if (empty($mc['topicos'])): ?>
                                            <p style="color: #a0aec0; font-style: italic; font-size: 0.85rem; padding: 10px 15px;">Nenhum conteúdo cadastrado para esta disciplina.</p>
                                        <?php else: ?>
                                            <?php foreach ($mc['topicos'] as $tp): ?>
                                                <div class="checklist-topico-row" style="display: flex; justify-content: space-between; align-items: center;">
                                                    <span><?= htmlspecialchars($tp['nome_topico']) ?></span>
                                                    <input type="checkbox" 
                                                           class="custom-checkbox chk-async-box check-topico" 
                                                           data-cronograma="<?= htmlspecialchars($id_visualizacao) ?>"
                                                           data-materia="<?= $mc['id_materia'] ?>"
                                                           data-topico="<?= $tp['id_topico'] ?>"
                                                           <?= $tp['checado'] ? 'checked' : '' ?>
                                                            >
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>

                <div class="card-header">
                    <div class="title-left">
                        <div class="icon-purple-box">
                            <i class="fa-regular fa-calendar-plus"></i>
                        </div>
                        <div>
                            <h2>Phronesis</h2>
                            <p>Crie e organize seu cronograma de estudos</p>
                            <span class="google-conta" title="Conta Google vinculada">
                                <i class="fa-brands fa-google"></i> <?= htmlspecialchars($emailGoogle) ?>
                            </span>
                        </div>
                    </div>
                    <a href="?logout=1" class="btn-desconectar">
                        Desconectar <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                </div>

                <form method="POST" id="formCronograma" class="form-grid">
                    <div class="full-width field-group">
                        <label>Nome do seu Cronograma:</label>
                        <input type="text" name="nome_cronograma" placeholder="Ex: Maratona ENEM 2026"
                            value="<?= htmlspecialchars($form_dados['nome_cronograma'] ?? '') ?>" required>
                    </div>

                    <div class="field-group input-icon-wrapper">
                        <label>Data Início:</label>
                        <div class="input-with-icon">
                            <input type="date" name="data_inicio" id="data_inicio" min="<?= $hoje ?>" value="<?= $valor_data_inicio ?>" required>
                            <i class="fa-regular fa-calendar input-icon-right"></i>
                        </div>
                    </div>

                    <div class="field-group input-icon-wrapper">
                        <label>Data Fim:</label>
                        <div class="input-with-icon">
                            <input type="date" name="data_fim" id="data_fim" min="<?= $min_data_fim ?>" value="<?= $valor_data_fim ?>" required>
                            <i class="fa-regular fa-calendar input-icon-right"></i>
                        </div>
                    </div>

                    <div class="full-width field-group">
                        <label>Dias da Semana e Matérias:</label>
                        <div class="list-container scrolling-list" id="containerDiasSemana"
                             data-selecionados='<?= htmlspecialchars(json_encode($dias_marcados), ENT_QUOTES) ?>'
                             data-materias='<?= htmlspecialchars(json_encode($materias_json), ENT_QUOTES) ?>'
                             data-materias-dia='<?= htmlspecialchars(json_encode($materias_dia_post), ENT_QUOTES) ?>'></div>
                        <small class="dica-dias">Marque o dia e clique na seta <i class="fa-solid fa-chevron-down"></i> para escolher as matérias daquele dia.</small>
                    </div>

                    <div class="field-group input-icon-wrapper">
                        <label>Hora Início:</label>
                        <div class="input-with-icon">
                            <input type="time" name="hora_inicio" value="<?= htmlspecialchars($form_dados['hora_inicio'] ?? '08:00') ?>" required>
                            <i class="fa-regular fa-clock input-icon-right"></i>
                        </div>
                    </div>

                    <div class="field-group">
                        <label>Duração por matéria (horas):</label>
                        <input type="number" name="horas_estudo" step="any" value="<?= htmlspecialchars($form_dados['horas_estudo'] ?? '2') ?>" required>
                    </div>

                    <div class="full-width dicas-box">
                        <div class="dicas-titulo"><i class="fa-regular fa-lightbulb"></i> Dicas para organizar seus estudos</div>
                        <div class="dicas-grid">
                            <div class="dica-item">
                                <i class="fa-solid fa-shuffle"></i>
                                <div>
                                    <strong>Intercale matérias</strong>
                                    <span>Alterne áreas diferentes ao longo da semana.</span>
                                </div>
                            </div>
                            <div class="dica-item">
                                <i class="fa-solid fa-mug-hot"></i>
                                <div>
                                    <strong>Faça pausas</strong>
                                    <span>Pequenos intervalos ajudam na concentração.</span>
                                </div>
                            </div>
                            <div class="dica-item">
                                <i class="fa-solid fa-arrows-rotate"></i>
                                <div>
                                    <strong>Reserve revisões</strong>
                                    <span>Retome os conteúdos estudados.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="gerar" class="btn-gen full-width">
                        <i class="fa-regular fa-calendar-check"></i> <?= isset($_GET['editar']) ? 'Regerar e Atualizar' : 'Gerar Cronograma' ?>
                    </button>
                </form>
            <?php endif; ?>
        </section>

    <aside class="sidebar-historico" id="sidebar">
        <button type="button" class="sidebar-close" id="btnFecharSidebar" aria-label="Fechar menu">&times;</button>
        <div class="sidebar-header">

            <?php if (empty($meus_cronogramas_db)): ?>
                <div class="empty-history-wrapper">
                    <div class="empty-illustration">
                        <div class="book-purple-icon">
                            <i class="fa-solid fa-book-open"></i>
                            <span class="sparkle-1">✦</span>
                            <span class="sparkle-2">✦</span>
                        </div>
                    </div>
                    <h4>Nenhum cronograma salvo.</h4>
                    <p>Crie seu primeiro cronograma para vê-lo aqui!</p>
                </div>
            <?php else: ?>
                <div class="history-list">
                    <?php foreach ($meus_cronogramas_db as $cron): ?>
                        <div class="cronograma-item">
                            <a href="?ver=<?= $cron['id_cronograma'] ?>" class="crono-link">
                                <span class="crono-title"><?= htmlspecialchars($cron['titulo']) ?></span>
                                <span class="crono-date"><?= $cron['data'] ?></span>
                            </a>
                            <div class="cronograma-actions">
                                <a href="?editar=<?= $cron['id_cronograma'] ?>" class="btn-action btn-edit" title="Editar" onclick="dispararLoadingEdicao(event, this.href)"><i class="fa-solid fa-pen"></i></a>
                                <a href="#" class="btn-action btn-del" title="Excluir" onclick="confirmDelete(event, '<?= $cron['id_cronograma'] ?>')"><i class="fa-solid fa-xmark"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!($calendar_id_exibir && !isset($_GET['editar']))): ?>
                <div class="bloco-navegar">
                <div class="conteiner-materias-expansivel" id="conteinerMaterias">
                    <?php if (!empty($materias_com_conteudos)): ?>
                        <?php foreach ($materias_com_conteudos as $materia_nome => $conteudos): ?>
                            <div class="materia-accordion-item">
                                <div class="materia-accordion-header">
                                    <span><i class="fa-regular fa-folder"></i> <?= htmlspecialchars($materia_nome) ?></span>
                                    <i class="fa-solid fa-chevron-down chevron"></i>
                                </div>
                                <div class="materia-accordion-content">
                                    <ul>
                                        <?php if (!empty($conteudos)): ?>
                                            <?php foreach ($conteudos as $conteudo): ?>
                                                <li><?= htmlspecialchars($conteudo) ?></li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li style="color: #8e8ea8; font-style: italic;">Nenhum conteúdo cadastrado para esta matéria ainda.</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #8e8ea8; font-size: 0.9rem; text-align: center; margin-top: 10px;">Nenhuma matéria carregada do banco.</p>
                    <?php endif; ?>
                </div>

                <button class="btn-toggle-materias" id="btnToggleMaterias">
                    <span>Navegar Conteúdos por Matéria</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                </div>
            <?php endif; ?>
        </div>
    </aside>
    </main>

    <div id="deleteConfirmModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>🗑️ Excluir Cronograma</h3>
            <p>Título: <b id="deleteCronoTitle"></b></p>
            <p>Isso removerá a agenda criada no Google Agenda definitivamente!</p>
            <div class="modal-actions-custom">
                <button type="button" id="btnConfirmarExclusaoFinal" class="btn-del-modal">Sim, excluir!</button>
                <button type="button" onclick="fecharModalExclusao()" class="btn-cancel-modal">Cancelar</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/lib/cronograma.js" defer></script>

    <nav class="bottom-nav" aria-label="Navegação principal">
        <a href="../index.php" class="bn-item"><i class="fa-solid fa-house"></i><span>Início</span></a>
        <a href="livros.php" class="bn-item"><i class="fa-solid fa-book"></i><span>Biblioteca</span></a>
        <a href="material.php" class="bn-item"><i class="fa-solid fa-tv"></i><span>Material</span></a>
        <a href="cronograma.php" class="bn-item ativo" aria-current="page"><i class="fa-regular fa-calendar-days"></i><span>Cronogramas</span></a>
        <a href="../perfil/configperfil.php" class="bn-item"><i class="fa-regular fa-user"></i><span>Perfil</span></a>
    </nav>

    <?php include '../perfil/vlibras.php'; ?>
    <?php include '../interface/footer.php';?>
</body>
</html>