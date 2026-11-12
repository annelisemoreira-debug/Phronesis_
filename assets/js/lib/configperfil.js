// assets/js/lib/configperfil.js
//
// Tema, tamanho de fonte e menu lateral (drawer) mobile são tratados por
// acessibilidade.js, carregado no <head> da página. Aqui ficam apenas as
// funções específicas do perfil: ampliar foto (lightbox com zoom),
// recortar/remover foto e excluir conta.

$(function () {
    'use strict';

    var ENDPOINT = 'configperfil.php';

    // Envia uma ação ao PHP e devolve a promise do $.ajax (resposta JSON).
    function enviarAcao(dados) {
        return $.ajax({
            type: 'POST',
            url: ENDPOINT,
            data: dados,
            dataType: 'json'
        });
    }

    // =========================================================================
    // FOTO DE PERFIL: LIGHTBOX COM ZOOM
    // =========================================================================
    var $preview   = $('#previewModal');
    var $previewImg = $('#previewImage');
    var $stage     = $('#previewStage');
    var $zoomLabel = $('#zoomLevel');
    var $avatar    = $('#currentAvatar');

    var ZOOM_MIN = 1;
    var ZOOM_MAX = 5;
    var ZOOM_CLIQUE = 2.5;
    var ZOOM_PASSO = 0.5;

    var zoom = 1, panX = 0, panY = 0;
    var pointers = {};
    var startPan = null;
    var lastPinchDist = 0;
    var dragMoved = false;
    var fecharTimer = null;

    function limitarPan() {
        var img = $previewImg[0];
        var maxX = img.offsetWidth * (zoom - 1) / 2;
        var maxY = img.offsetHeight * (zoom - 1) / 2;
        panX = Math.max(-maxX, Math.min(maxX, panX));
        panY = Math.max(-maxY, Math.min(maxY, panY));
    }

    function aplicarZoom() {
        if (zoom <= ZOOM_MIN) { panX = 0; panY = 0; }
        limitarPan();
        $stage.css('transform', 'translate(' + panX + 'px, ' + panY + 'px) scale(' + zoom + ')');
        $zoomLabel.text(Math.round(zoom * 100) + '%');
        $preview.toggleClass('zoomed', zoom > ZOOM_MIN);
    }

    function setZoom(valor) {
        zoom = Math.max(ZOOM_MIN, Math.min(ZOOM_MAX, valor));
        aplicarZoom();
    }

    function resetZoom() {
        zoom = 1; panX = 0; panY = 0;
        aplicarZoom();
    }

    function abrirPreview() {
        var srcOriginal = $avatar.attr('src');
        var src = srcOriginal;

        // Foto do Google vem pequena (192px): pede uma versão maior
        if (src.indexOf('googleusercontent.com') !== -1) {
            src = src.replace(/=s\d+-c$/, '=s800-c');
        }

        clearTimeout(fecharTimer);
        resetZoom();

        // Se a versão grande falhar, usa a imagem que já está no avatar
        $previewImg.off('error').on('error', function () {
            $previewImg.off('error');
            if ($previewImg.attr('src') !== srcOriginal) {
                $previewImg.attr('src', srcOriginal);
            }
        });

        $previewImg.attr('src', src);
        $preview.css('display', 'flex').attr('aria-hidden', 'false');
        setTimeout(function () { $preview.addClass('show'); }, 10);
    }

    function fecharPreview() {
        $preview.removeClass('show').attr('aria-hidden', 'true');
        fecharTimer = setTimeout(function () {
            $preview.css('display', 'none');
            resetZoom();
        }, 250);
    }

    // Abrir: clique ou Enter/Espaço na foto
    $avatar.on('click', abrirPreview);
    $avatar.on('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            abrirPreview();
        }
    });

    // Fechar: X ou fundo escuro
    $('#btnClosePreview').on('click', fecharPreview);
    $preview.on('click', function (e) {
        if (e.target === this) fecharPreview();
    });

    // Botões de zoom
    $('#btnZoomIn').on('click', function () { setZoom(zoom + ZOOM_PASSO); });
    $('#btnZoomOut').on('click', function () { setZoom(zoom - ZOOM_PASSO); });
    $('#btnZoomReset').on('click', resetZoom);

    // Clique na imagem alterna entre normal e ampliada (ignora arraste)
    $previewImg.on('click', function () {
        if (dragMoved) { dragMoved = false; return; }
        setZoom(zoom > ZOOM_MIN ? ZOOM_MIN : ZOOM_CLIQUE);
    });

    // Roda do mouse (listener nativo para poder usar preventDefault)
    $preview[0].addEventListener('wheel', function (e) {
        if (!$preview.hasClass('show')) return;
        e.preventDefault();
        setZoom(zoom * (e.deltaY < 0 ? 1.15 : 1 / 1.15));
    }, { passive: false });

    // Arrastar (1 dedo/mouse) e pinça (2 dedos)
    $previewImg.on('pointerdown', function (e) {
        var ev = e.originalEvent;
        this.setPointerCapture(ev.pointerId);
        pointers[ev.pointerId] = { x: ev.clientX, y: ev.clientY };
        startPan = { x: panX, y: panY, px: ev.clientX, py: ev.clientY };
        lastPinchDist = 0;
        dragMoved = false;
        $stage.addClass('dragging');
    });

    $previewImg.on('pointermove', function (e) {
        var ev = e.originalEvent;
        if (!pointers[ev.pointerId]) return;
        pointers[ev.pointerId] = { x: ev.clientX, y: ev.clientY };
        var ids = Object.keys(pointers);

        if (ids.length === 2) {
            var a = pointers[ids[0]], b = pointers[ids[1]];
            var dist = Math.hypot(a.x - b.x, a.y - b.y);
            if (lastPinchDist) setZoom(zoom * dist / lastPinchDist);
            lastPinchDist = dist;
            dragMoved = true;
        } else if (ids.length === 1 && zoom > ZOOM_MIN) {
            var dx = ev.clientX - startPan.px;
            var dy = ev.clientY - startPan.py;
            if (Math.abs(dx) + Math.abs(dy) > 5) dragMoved = true;
            panX = startPan.x + dx;
            panY = startPan.y + dy;
            aplicarZoom();
        }
    });

    $previewImg.on('pointerup pointercancel', function (e) {
        delete pointers[e.originalEvent.pointerId];
        lastPinchDist = 0;
        var ids = Object.keys(pointers);

        if (ids.length === 1) {
            // sobrou um dedo: recomeça o arraste a partir dele
            var p = pointers[ids[0]];
            startPan = { x: panX, y: panY, px: p.x, py: p.y };
        } else if (ids.length === 0) {
            $stage.removeClass('dragging');
        }
    });

    // =========================================================================
    // FOTO DE PERFIL: SELECIONAR, RECORTAR, REMOVER
    // =========================================================================
    var $cropModal = $('#cropModal');
    var $imageToCrop = $('#imageToCrop');
    var $inputFoto = $('#foto_perfil');
    var cropper = null;

    function destruirCropper() {
        if (cropper) { cropper.destroy(); cropper = null; }
    }

    function fecharCrop() {
        $cropModal.hide();
        destruirCropper();
        $inputFoto.val('');
    }

    $('#btnSelectFoto').on('click', function () {
        $inputFoto.trigger('click');
    });

    // Remover foto
    $('#btnRemoveFoto').on('click', function () {
        if (!confirm('Tem certeza que deseja remover sua foto de perfil e usar a padrão?')) return;

        enviarAcao({ action: 'delete_image' })
            .done(function (r) {
                if (r.status === 'success') window.location.reload();
                else alert('Erro ao remover: ' + r.message);
            })
            .fail(function () {
                alert('Erro técnico ao remover a foto.');
            });
    });

    // Ao escolher um arquivo, abre o recorte
    $inputFoto.on('change', function (e) {
        var files = e.target.files;
        if (!files || !files.length) return;

        if (files[0].type.indexOf('image/') !== 0) {
            alert('Selecione um arquivo de imagem.');
            $(this).val('');
            return;
        }

        var reader = new FileReader();
        reader.onload = function (ev) {
            $imageToCrop.attr('src', ev.target.result);
            $cropModal.css('display', 'flex');
            destruirCropper();

            // Moldura quadrada fixa; a imagem sempre preenche a área (viewMode 3)
            cropper = new Cropper($imageToCrop[0], {
                aspectRatio: 1,
                viewMode: 3,
                dragMode: 'move',
                autoCropArea: 1,
                cropBoxMovable: false,
                cropBoxResizable: false,
                toggleDragModeOnDblclick: false,
                guides: false,
                center: false,
                highlight: false,
                background: false
            });
        };
        reader.readAsDataURL(files[0]);
    });

    $('#btnCancelCrop').on('click', fecharCrop);

    // Confirmar e salvar foto recortada
    $('#btnConfirmCrop').on('click', function () {
        if (!cropper) return;

        var canvas = cropper.getCroppedCanvas({
            width: 400,
            height: 400,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        enviarAcao({ action: 'crop_image', image_data: canvas.toDataURL('image/webp') })
            .done(function (r) {
                if (r.status === 'success') window.location.reload();
                else alert('Erro: ' + r.message);
            })
            .fail(function () {
                alert('Erro técnico ao salvar a imagem.');
            });
    });

    // =========================================================================
    // EXCLUSÃO DE CONTA (sem alert de sucesso: redireciona direto)
    // =========================================================================
    var $deleteModal = $('#deleteAccountModal');
    var $senha = $('#confirmDeletePassword');

    function fecharDelete() {
        $deleteModal.hide();
    }

    function excluirConta(dados, $botao) {
        $botao.prop('disabled', true);

        enviarAcao(dados)
            .done(function (r) {
                if (r.status === 'success') {
                    window.location.href = r.redirect;
                } else {
                    $botao.prop('disabled', false);
                    alert(r.message);
                }
            })
            .fail(function () {
                $botao.prop('disabled', false);
                alert('Erro técnico ao tentar processar a solicitação.');
            });
    }

    $('#btnOpenDeleteModal').on('click', function () {
        $senha.val('');
        $deleteModal.css('display', 'flex');
        $senha.trigger('focus');
    });

    $('#btnCancelDelete').on('click', fecharDelete);

    // Conta comum: confirma com senha
    function confirmarComSenha() {
        var password = $senha.val();
        if (!password) {
            alert('Por favor, digite sua senha.');
            return;
        }
        excluirConta({ action: 'delete_account', password: password }, $('#btnConfirmDelete'));
    }

    $('#btnConfirmDelete').on('click', confirmarComSenha);
    $senha.on('keydown', function (e) {
        if (e.key === 'Enter') confirmarComSenha();
    });

    // Conta Google: exclusão direta
    $('#btnConfirmDeleteGoogle').on('click', function () {
        excluirConta({ action: 'delete_account_google' }, $(this));
    });

    // =========================================================================
    // TECLADO (um único listener para os modais)
    // =========================================================================
    $(document).on('keydown', function (e) {
        // Lightbox da foto
        if ($preview.hasClass('show')) {
            if (e.key === 'Escape') fecharPreview();
            else if (e.key === '+' || e.key === '=') setZoom(zoom + ZOOM_PASSO);
            else if (e.key === '-') setZoom(zoom - ZOOM_PASSO);
            else if (e.key === '0') resetZoom();
            return;
        }

        if (e.key === 'Escape') {
            if ($cropModal.is(':visible')) fecharCrop();
            if ($deleteModal.is(':visible')) fecharDelete();
        }
    });
});