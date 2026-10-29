<?php
// Caminho base do site: funciona em qualquer pasta (pages/, perfil/, etc.)
$__root = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$__base = str_replace($__root, '', str_replace('\\', '/', realpath(__DIR__ . '/..')));
?>
<link rel="stylesheet" href="<?= $__base ?>/assets/css/footer.css">

<footer class="site-footer">
    <span>© 2026 Phronesis_ • Todos os direitos reservados</span>
    <span class="footer-slogan">Disciplina hoje, liberdade amanhã.</span>
</footer>