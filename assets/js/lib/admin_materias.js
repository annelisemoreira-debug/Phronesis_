// assets/js/lib/admin_materias.js

// === INJEÇÃO IMEDIATA (EVITA FLASH BRANCO E DE FONTE) ===
// Executa imediatamente para aplicar o tema escuro antes de renderizar a página
(function () {
    const savedTheme = localStorage.getItem('site-theme');
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark-theme');
        document.body.classList.add('dark-theme');
    }

    // Tamanho de fonte escolhido na página de acessibilidade
    const tamanhoSalvo = localStorage.getItem('site-font-size') || 'normal';
    document.documentElement.classList.remove('font-normal', 'font-media', 'font-grande');
    document.documentElement.classList.add(`font-${tamanhoSalvo}`);
})();

// === 1. EXIBIR/OCULTAR PAINEL DE TÓPICOS ===
function toggleTopicosPanel(id) {
    const painel = document.getElementById(`painel-topicos-${id}`);
    if (painel) {
        painel.style.display = (painel.style.display === 'table-row') ? 'none' : 'table-row';
        ajustarRolagemMaterias();
    }
}

// === 2. PREPARAR ENVIO DO FORMULÁRIO INLINE ===
// Mantida no escopo global porque é chamada via atributo 'onsubmit' do HTML injetado
function prepararEnvio(id) {
    const valorInput = document.getElementById(`input-${id}`).value;
    document.getElementById(`hidden-nome-${id}`).value = valorInput;
}

// === 3. LÓGICA DE EDIÇÃO DE MATÉRIAS ===
function activarEdicao(id, nomeOriginal) {
    const linha = document.getElementById(`linha-${id}`);
    if (!linha) return;

    const colNome = linha.querySelector('.col-nome');
    const colAcoes = linha.querySelector('.col-acoes');

    const htmlOriginalNome = colNome.innerHTML;
    const htmlOriginalAcoes = colAcoes.innerHTML;

    colNome.innerHTML = `<input type="text" class="input-inline" id="input-${id}">`;
    document.getElementById(`input-${id}`).value = nomeOriginal;
    document.getElementById(`input-${id}`).focus();

    colAcoes.innerHTML = `
        <div class="action-buttons" style="justify-content: center;">
            <form method="POST" style="display: inline;" onsubmit="prepararEnvio(${id});">
                <input type="hidden" name="id_materia" value="${id}">
                <input type="hidden" name="novo_nome" id="hidden-nome-${id}">
                <button type="submit" name="edit_materia" class="btn-salvar">Salvar</button>
            </form>
            <button type="button" class="btn-cancelar" id="cancelar-${id}">X</button>
        </div>
    `;

    document.getElementById(`cancelar-${id}`).addEventListener('click', () => {
        colNome.innerHTML = htmlOriginalNome;
        colAcoes.innerHTML = htmlOriginalAcoes;
    });
}

function ativarEdicaoMateria(id, nomeOriginal) {
    activarEdicao(id, nomeOriginal);
}

// === 4. LÓGICA DE EDIÇÃO DE TÓPICOS ===
function ativarEdicaoTopico(idTopico, nomeOriginal) {
    const tag = document.getElementById(`tag-topico-${idTopico}`);
    if (!tag) return;

    const htmlOriginal = tag.innerHTML;

    tag.innerHTML = `
        <form method="POST" style="display: flex; align-items: center; gap: 5px;">
            <input type="hidden" name="id_topico" value="${idTopico}">
            <input type="text" name="novo_nome_topico" id="input-topico-${idTopico}" style="padding: 4px 8px; font-size: 12px; border-radius: 4px; border: 1px solid #0284c7; outline:none;">
            <button type="submit" name="edit_topico" class="btn-salvar" style="padding: 4px 8px;">✔</button>
            <button type="button" class="btn-cancelar" id="cancel-topico-${idTopico}" style="padding: 4px 8px; background: #ef4444;">X</button>
        </form>
    `;

    // Define o valor via JS para não quebrar com aspas no nome
    document.getElementById(`input-topico-${idTopico}`).value = nomeOriginal;

    document.getElementById(`cancel-topico-${idTopico}`).addEventListener('click', () => {
        tag.innerHTML = htmlOriginal;
    });
}

// === 5. EVENTOS: EDIÇÃO INLINE DE MATÉRIAS ===
document.addEventListener('DOMContentLoaded', function () {
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-ativar-edicao');
        if (btn) {
            const id = btn.getAttribute('data-id');
            const nomeOriginal = btn.getAttribute('data-nome');
            ativarEdicaoMateria(id, nomeOriginal);
        }
    });
});

// === 6. EDIÇÃO DE LIVROS (janela modal) ===
document.addEventListener('DOMContentLoaded', function () {
    const dlg = document.getElementById('dlg-livro');
    if (!dlg) return;

    document.querySelectorAll('.btn-editar-livro').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.dataset;
            dlg.querySelector('[name="id_livro"]').value = d.id;
            dlg.querySelector('[name="titulo"]').value = d.titulo;
            dlg.querySelector('[name="autor"]').value = d.autor;
            dlg.querySelector('[name="resumo"]').value = d.resumo;
            dlg.querySelector('[name="capa"]').value = '';

            const marcados = d.vests ? d.vests.split(',') : [];
            dlg.querySelectorAll('input[name="vestibulares[]"]').forEach(c => {
                c.checked = marcados.includes(c.value);
            });
            dlg.showModal();
        });
    });

    dlg.querySelector('.fechar-dlg').addEventListener('click', () => dlg.close());

    // Fecha ao clicar fora
    dlg.addEventListener('click', (e) => {
        const r = dlg.getBoundingClientRect();
        if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom) dlg.close();
    });
});

// === 7. ROLAGEM DA LISTA DE LIVROS (após o 5º) ===
function ajustarRolagemLivros() {
    const wrap = document.querySelector('.tabela-livros.rolagem');
    if (!wrap) return;

    const linhas = wrap.querySelectorAll('tbody tr');
    if (linhas.length <= 5) return;

    let altura = wrap.querySelector('thead').offsetHeight;
    for (let i = 0; i < 5; i++) altura += linhas[i].offsetHeight;

    wrap.style.maxHeight = altura + 'px';
}

document.addEventListener('DOMContentLoaded', ajustarRolagemLivros);
window.addEventListener('load', ajustarRolagemLivros);
window.addEventListener('resize', ajustarRolagemLivros);

// === 8. ROLAGEM DA LISTA DE MATÉRIAS (após a 15ª) ===
const LIMITE_MATERIAS_VISIVEIS = 15;

function ajustarRolagemMaterias() {
    const wrap = document.querySelector('.tabela-materias');
    if (!wrap) return;

    const linhas = wrap.querySelectorAll('tbody tr');
    let altura = wrap.querySelector('thead').offsetHeight;
    let contagem = 0;
    let excedeu = false;

    for (const tr of linhas) {
        const ehMateria = tr.id.startsWith('linha-');
        if (ehMateria && tr.offsetHeight > 0) {
            contagem++;
            if (contagem > LIMITE_MATERIAS_VISIVEIS) { excedeu = true; break; }
        }
        altura += tr.offsetHeight; // painéis abertos dentro das 15 primeiras entram na conta
    }

    if (excedeu) {
        wrap.classList.add('rolagem');
        wrap.style.maxHeight = altura + 'px';
    } else {
        wrap.classList.remove('rolagem');
        wrap.style.maxHeight = '';
    }
}

document.addEventListener('DOMContentLoaded', ajustarRolagemMaterias);
window.addEventListener('load', ajustarRolagemMaterias);
window.addEventListener('resize', ajustarRolagemMaterias);

// === 9. PESQUISA DE MATÉRIAS E TÓPICOS ===
function removerAcentos(texto) {
    return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

function filtrarMateriasETopicos() {
    const termo = removerAcentos(document.getElementById('campoBuscaMateria').value.toLowerCase().trim());
    const linhasMaterias = document.querySelectorAll('tr[id^="linha-"]');

    linhasMaterias.forEach(linha => {
        const idMateria = linha.id.replace('linha-', '');

        const nomeMateria = removerAcentos(linha.querySelector('.texto-nome').textContent.toLowerCase());
        const painelTopicos = document.getElementById('painel-topicos-' + idMateria);
        const textoTopicos = painelTopicos ? removerAcentos(painelTopicos.textContent.toLowerCase()) : '';

        const encontrouMateria = nomeMateria.includes(termo);
        const encontrouTopico = textoTopicos.includes(termo);

        if (termo === '' || encontrouMateria || encontrouTopico) {
            linha.style.display = '';

            // Abre o painel se a busca bater com um tópico
            if (encontrouTopico && termo !== '') {
                if (painelTopicos) painelTopicos.style.display = 'table-row';
            } else if (painelTopicos && !painelTopicos.classList.contains('ativo')) {
                painelTopicos.style.display = 'none';
            }
        } else {
            linha.style.display = 'none';
            if (painelTopicos) painelTopicos.style.display = 'none';
        }
    });

    ajustarRolagemMaterias();
}

// === 10. CONFIRMAÇÃO DE EXCLUSÃO (SweetAlert2) ===
// Alerta padrão do site: "Tem certeza?" + descrição do item + botões roxo/vermelho.
// Usa delegação de eventos: funciona também nos botões recriados pela edição inline.
document.addEventListener('click', function (e) {
    const link = e.target.closest('.js-confirmar-exclusao');
    if (!link) return;

    const texto = link.dataset.texto || 'Esta ação não pode ser desfeita.';

    // Plano B: se o SweetAlert2 não carregar, usa o confirm padrão
    if (typeof Swal === 'undefined') {
        if (!confirm(texto)) e.preventDefault();
        return;
    }

    e.preventDefault();

    Swal.fire({
        icon: 'warning',
        title: 'Tem certeza?',
        text: texto,
        showCancelButton: true,
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar',
        focusCancel: true,          // evita excluir por engano com Enter
        buttonsStyling: false,      // usa o estilo do nosso CSS
        customClass: {
            popup: 'swal-phronesis',
            confirmButton: 'swal-btn-excluir',
            cancelButton: 'swal-btn-cancelar'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = link.href;
        }
    });
});