// assets/js/lib/configperfil.js

// Garante a aplicação do tema escuro no topo imediatamente para evitar o "flash" branco
if (localStorage.getItem('site-theme') === 'dark') {
    document.documentElement.classList.add('dark-theme');
}

$(document).ready(function() {
    var $modal = $('#cropModal'), $image = $('#imageToCrop'), cropper;

    // AÇÃO DO BOTÃO SELECIONAR FOTO
    $('#btnSelectFoto').on('click', function() { 
        $('#foto_perfil').click(); 
    });

    // AÇÃO DO BOTÃO REMOVER FOTO
    $('#btnRemoveFoto').on('click', function() {
        if(confirm("Tem certeza que deseja remover sua foto de perfil e usar a padrão?")) {
            $.ajax({
                type: 'POST',
                url: 'configperfil.php',
                data: { action: 'delete_image' },
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        window.location.reload();
                    } else {
                        alert("Erro ao remover: " + response.message);
                    }
                }
            });
        }
    });

    // QUANDO ADICIONA UMA IMAGEM NO INPUT FILE
    $('#foto_perfil').on('change', function(e) {
        var files = e.target.files;
        if (files && files.length > 0) {
            var reader = new FileReader();
            reader.onload = function(event) {
                $image.attr('src', event.target.result);
                $modal.css('display', 'flex');
                
                if (cropper) cropper.destroy();
                
                cropper = new Cropper($image[0], { 
                    aspectRatio: 1, 
                    viewMode: 1, 
                    dragMode: 'move',
                    autoCropArea: 0.8
                });
            };
            reader.readAsDataURL(files[0]);
        }
    });

    // CANCELAR O CORTE DA FOTO
    $('#btnCancelCrop').on('click', function() { 
        $modal.hide(); 
        if (cropper) cropper.destroy(); 
        $('#foto_perfil').val('');
    });

    // CONFIRMAR E SALVAR FOTO CORTADA
    $('#btnConfirmCrop').on('click', function() {
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
            success: function(response) { 
                if(response.status === 'success') {
                    window.location.reload();
                } else {
                    alert("Erro: " + response.message);
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("Erro técnico ao salvar a imagem.");
            }
        });
    });

    // =========================================================================
    // --- LÓGICA DO MODAL DE EXCLUSÃO DE CONTA (GERAL) ---
    // =========================================================================
    var $deleteModal = $('#deleteAccountModal');

    // Abre o modal de exclusão para qualquer tipo de usuário
    $('#btnOpenDeleteModal').on('click', function() {
        $('#confirmDeletePassword').val(''); 
        $deleteModal.css('display', 'flex');
    });

    // Fecha o modal ao clicar em Cancelar
    $('#btnCancelDelete').on('click', function() {
        $deleteModal.hide();
    });

    // FLUXO 1: Envia a senha via AJAX (Apenas para contas comuns com senha)
    $('#btnConfirmDelete').on('click', function() {
        var password = $('#confirmDeletePassword').val();

        if (!password) {
            alert("Por favor, digite sua senha.");
            return;
        }

        $.ajax({
            type: 'POST',
            url: 'configperfil.php',
            data: { 
                action: 'delete_account', 
                password: password 
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert("Sua conta e todos os dados foram excluídos com sucesso.");
                    window.location.href = response.redirect;
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert("Erro técnico ao tentar processar a solicitação.");
            }
        });
    });

    // FLUXO 2: Envia a exclusão direta via AJAX (Apenas para contas conectadas pelo Google)
    $('#btnConfirmDeleteGoogle').on('click', function() {
        $.ajax({
            type: 'POST',
            url: 'configperfil.php',
            data: { 
                action: 'delete_account_google' 
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert("Seus dados foram removidos com sucesso do nosso sistema.");
                    window.location.href = response.redirect;
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert("Erro técnico ao tentar apagar os dados.");
            }
        });
    });
});

// Configurações adicionais de acessibilidade e tema carregadas imediatamente
(function () {
    const savedTheme = localStorage.getItem('site-theme') || 'light';
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark-theme');
    }

    const savedSize = localStorage.getItem('site-font-size') || 'normal';
    document.documentElement.classList.add('font-' + savedSize);
})();

