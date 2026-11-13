<?php
// google_helper.php
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Monta o serviço do Google Agenda com o token salvo do usuário.
 * Retorna null se não houver token válido (ex.: ele clicou em "Desconectar").
 */
function criarServicoGoogleDoUsuario(mysqli $conn, int $idUsuario): ?Google\Service\Calendar
{
    $stmt = $conn->prepare('SELECT google_token FROM usuario WHERE id_usuario = ?');
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $token = json_decode((string) ($row['google_token'] ?? ''), true);
    if (!is_array($token)) {
        $token = $_SESSION['upload_token'] ?? null; // plano B: token da sessão
    }
    if (!is_array($token)) {
        return null;
    }

    $client = new Google\Client();
    $client->setAuthConfig(__DIR__ . '/credentials.json');
    $client->setAccessToken($token);

    if ($client->isAccessTokenExpired()) {
        $refresh = $client->getRefreshToken();
        if (!$refresh) {
            return null;
        }
        $novo = $client->fetchAccessTokenWithRefreshToken($refresh);
        if (isset($novo['error'])) {
            return null;
        }
    }

    return new Google\Service\Calendar($client);
}

/**
 * Apaga do Google Agenda e do banco TODOS os cronogramas criados pelo usuário.
 * Chame ANTES de excluir a linha da tabela usuario (o token está nela).
 *
 * @return array ['removidos' => int, 'falhas' => int]
 */
function apagarCronogramasDoUsuario(mysqli $conn, int $idUsuario): array
{
    set_time_limit(300);
    $resultado = ['removidos' => 0, 'falhas' => 0];

    $stmt = $conn->prepare('SELECT id_cronograma, google_calendar_id FROM cronograma WHERE id_usuario = ?');
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $cronogramas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (!$cronogramas) {
        return $resultado;
    }

    $service = criarServicoGoogleDoUsuario($conn, $idUsuario);

    foreach ($cronogramas as $c) {
        $idCrono    = (int) $c['id_cronograma'];
        $calendarId = $c['google_calendar_id'];

        if ($calendarId && $calendarId !== 'primary') {
            if (!$service) {
                $resultado['falhas']++;
                error_log("Phronesis: sem token Google para apagar o calendário $calendarId");
            } else {
                try {
                    $service->calendars->delete($calendarId);
                } catch (Google\Service\Exception $e) {
                    // 404/410 = já não existe no Google, então conta como removido
                    if (!in_array($e->getCode(), [404, 410], true)) {
                        $resultado['falhas']++;
                        error_log("Phronesis: erro ao apagar $calendarId: " . $e->getMessage());
                    }
                } catch (Exception $e) {
                    $resultado['falhas']++;
                    error_log("Phronesis: erro ao apagar $calendarId: " . $e->getMessage());
                }
            }
        }

        // cronograma_item e cronograma_topico_status caem junto (ON DELETE CASCADE)
        $conn->query("DELETE FROM `cronograma` WHERE id_cronograma = $idCrono");
        $resultado['removidos']++;
    }

    return $resultado;
}