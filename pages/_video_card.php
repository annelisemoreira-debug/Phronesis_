<?php
// pages/_video_card.php
if (!function_exists('e')) {
    function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

function renderVideoCard($vId, $titulo, $isFav, $isSeen) {
    $curto = mb_strimwidth($titulo, 0, 70, '...', 'UTF-8');
    ?>
    <article class="video-card" data-id="<?= e($vId) ?>" data-titulo="<?= e($titulo) ?>">
        <button type="button" class="video-thumb" data-yt="<?= e($vId) ?>" aria-label="Assistir: <?= e($titulo) ?>">
            <img src="https://i.ytimg.com/vi/<?= e($vId) ?>/hqdefault.jpg" alt="" loading="lazy">
            <span class="play-icon"><i class="fa-solid fa-play"></i></span>
        </button>
        <div class="video-title-card <?= $isSeen ? 'video-visto' : '' ?>">
            <a href="https://www.youtube.com/watch?v=<?= e($vId) ?>" target="_blank" rel="noopener"
               class="video-title-link link-detectavel"><?= e($curto) ?></a>
            <span class="btn-actions-group">
                <button type="button" class="seen-btn <?= $isSeen ? 'seen-on' : 'seen-off' ?>"
                        aria-label="Marcar como visto" title="Marcar como visto">
                    <i class="fa-solid fa-circle-check"></i>
                </button>
                <button type="button" class="fav-btn <?= $isFav ? 'star-on' : 'star-off' ?>"
                        aria-label="Salvar vídeo" title="Salvar vídeo">
                    <i class="fa-solid fa-star"></i>
                </button>
            </span>
        </div>
    </article>
    <?php
}