// assets/js/lib/configperfil.js
//
// Tema, tamanho de fonte e menu lateral (drawer) mobile são tratados por
// acessibilidade.js, carregado no <head> da página. Aqui ficam apenas as
// funções específicas do perfil: ampliar foto, recortar/remover foto e
// excluir conta.

$(document).ready(function () {

    // =========================================================================
    // --- EXPANDIR FOTO DE PERFIL (LIGHTBOX COM ZOOM) ---
    // =========================================================================
    var $preview = $('#previewModal');
    var $previewImg = $('#previewImage');
    var $stage = $('#previewStage');
    var $zoomLabel = $('#zoomLevel');

    var ZOOM_MIN = 1, ZOOM_MAX = 5, ZOOM_CLIQUE = 2.5;
    var zoom = 1, panX = 0, panY = 0;
    var pointers = {}, startPan = null, lastPinchDist = 0, dragMoved = false;

    // Impede arrastar a foto para fora da área visível
    function limitarPan() {
        var w = $previewImg[0].offsetWidth;
        var h = $previewImg[0].offsetHeight;
        var maxX = w * (zoom - 1) / 2;
        var maxY = h * (zoom - 1) / 2;
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
        var srcOriginal = $('#currentAvatar').attr('src');
        var src = srcOriginal;

        // Foto do Google vem pequena (192px); pede uma versão maior
        if (src.indexOf('googleusercontent.com') !== -1) {
            src = src.replace(/=s\d+-c$/, '=s800-c');
        }

        resetZoom();

        // Se a versão grande falhar, usa a imagem que já está no avatar
        $previewImg.off('error').on('error', function () {
            $previewImg.off('error');
            if ($previewImg.attr('src') !== srcOriginal) {
                $previewImg.attr('src', srcOriginal);
            }
        });

        $previewImg.attr('src', src);
        $preview.css('display', 'flex');
        setTimeout(function () { $preview.addClass('show'); }, 10);
        $preview.attr('aria-hidden', 'false');
    }

    function fecharPreview() {
        $preview.removeClass('show');
        $preview.attr('aria-hidden', 'true');
        setTimeout(function () {
            $preview.css('display', 'none');
            resetZoom();
        }, 250);
    }

    // Abrir: clique ou Enter/Espaço na foto
    $('#currentAvatar').on('click', abrirPreview);
    $('#currentAvatar').on('keydown', function (e) {
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
    $('#btnZoomIn').on('click', function () { setZoom(zoom + 0.5); });
    $('#btnZoomOut').on('click', function () { setZoom(zoom - 0.5); });
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

    function soltarPointer(e) {
        var ev = e.originalEvent;
        delete pointers[ev.pointerId];
        lastPinchDist = 0;
        var ids = Object.keys(pointers);
        if (ids.length === 1) {
            // sobrou um dedo: recomeça o arraste a partir dele
            var p = pointers[ids[0]];
            startPan = { x: panX, y: panY, px: p.x, py: p.y };
        } else if (ids.length === 0) {
            $stage.removeClass('dragging');
        }
    }
    $previewImg.on('pointerup pointercancel', soltarPointer);

    // =========================================================================
    // --- FOTO DE PERFIL: SELECIONAR, RECORTAR, REMOVER ---
    // =========================================================================
    var $cropModal = $('#cropModal');
    var $imageToCrop = $('#imageToCrop');
    var cropper = null;

    function fecharCrop() {
        $cropModal.hide();
        if (cropper) { cropper.destroy(); cropper = null; }
        $('#foto_perfil').val('');
    }

    $('#btnSelectFoto').on('click', function () {
        $('#foto_perfil').click();
    });

    // Remover foto
    $('#btnRemoveFoto').on('click', function () {
        if (!confirm('Tem certeza que deseja remover sua foto de perfil e usar a padrão?')) return;

        $.ajax({
            type: 'POST',
            url: 'configperfil.php',
            data: { action: 'delete_image' },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    window.location.reload();
                } else {
                    alert('Erro ao remover: ' + response.message);
                }
            },
            error: function () {
                alert('Erro técnico ao remover a foto.');
            }
        });
    });

    // Ao escolher um arquivo, abre o recorte
    $('#foto_perfil').on('change', function (e) {
        var files = e.target.files;
        if (!files || files.length === 0) return;

        if (files[0].type.indexOf('image/') !== 0) {
            alert('Selecione um arquivo de imagem.');
            $(this).val('');
            return;
        }

        var reader = new FileReader();
        reader.onload = function (event) {
            $imageToCrop.attr('src', event.target.result);
            $cropModal.css('display', 'flex');

            if (cropper) cropper.destroy();

            // Moldura quadrada fixa; a imagem sempre preenche a área
            // (viewMode 3), então nunca sobra espaço vazio ou achatado.
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
        var base64data = canvas.toDataURL('image/webp');

        $.ajax({
            type: 'POST',
            url: 'configperfil.php',
            data: { action: 'crop_image', image_data: base64data },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    window.location.reload();
                } else {
                    alert('Erro: ' + response.message);
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                alert('Erro técnico ao salvar a imagem.');
            }
        });
    });

    // =========================================================================
    // --- EXCLUSÃO DE CONTA ---
    // =========================================================================
    var $deleteModal = $('#deleteAccountModal');

    $('#btnOpenDeleteModal').on('click', function () {
        $('#confirmDeletePassword').val('');
        $deleteModal.css('display', 'flex');
    });

    $('#btnCancelDelete').on('click', function () {
        $deleteModal.hide();
    });

    // Conta comum: confirma com senha
    $('#btnConfirmDelete').on('click', function () {
        var password = $('#confirmDeletePassword').val();

        if (!password) {
            alert('Por favor, digite sua senha.');
            return;
        }

        $.ajax({
            type: 'POST',
            url: 'configperfil.php',
            data: { action: 'delete_account', password: password },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    alert('Sua conta e todos os dados foram excluídos com sucesso.');
                    window.location.href = response.redirect;
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert('Erro técnico ao tentar processar a solicitação.');
            }
        });
    });

    // Conta Google: exclusão direta
    $('#btnConfirmDeleteGoogle').on('click', function () {
        $.ajax({
            type: 'POST',
            url: 'configperfil.php',
            data: { action: 'delete_account_google' },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    alert('Seus dados foram removidos com sucesso do nosso sistema.');
                    window.location.href = response.redirect;
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert('Erro técnico ao tentar apagar os dados.');
            }
        });
    });

    // =========================================================================
    // --- TECLADO (um único listener para os modais) ---
    // =========================================================================
    $(document).on('keydown', function (e) {
        // Lightbox da foto
        if ($preview.hasClass('show')) {
            if (e.key === 'Escape') fecharPreview();
            else if (e.key === '+' || e.key === '=') setZoom(zoom + 0.5);
            else if (e.key === '-') setZoom(zoom - 0.5);
            else if (e.key === '0') resetZoom();
            return;
        }

        // Demais modais
        if (e.key === 'Escape') {
            if ($cropModal.is(':visible')) fecharCrop();
            if ($deleteModal.is(':visible')) $deleteModal.hide();
        }
    });
});