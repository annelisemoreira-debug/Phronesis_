// assets/js/lib/admin_materias.js

// Executa imediatamente para aplicar o tema escuro antes de renderizar a página
(function () {
    const savedTheme = localStorage.getItem('site-theme');
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark-theme');
        document.body.classList.add('dark-theme');
    }
})();

// === 1. EXIBIR/OCULTAR PAINEL DE TÓPICOS ===
function toggleTopicosPanel(id) {
    const painel = document.getElementById(`painel-topicos-${id}`);
    if (painel) {
        painel.style.display = (painel.style.display === 'table-row') ? 'none' : 'table-row';
    }
}

// === 2. PREPARAR ENVIO DO FORMULÁRIO INLINE ===
// Mantida no escopo global porque é chamada via string no atributo 'onsubmit' do HTML injetado
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
            <input type="text" name="novo_nome_topico" value="${nomeOriginal}" style="padding: 4px 8px; font-size: 12px; border-radius: 4px; border: 1px solid #0284c7; outline:none;">
            <button type="submit" name="edit_topico" class="btn-salvar" style="padding: 4px 8px;">✔</button>
            <button type="button" class="btn-cancelar" id="cancel-topico-${idTopico}" style="padding: 4px 8px; background: #ef4444;">X</button>
        </form>
    `;

    document.getElementById(`cancel-topico-${idTopico}`).addEventListener('click', () => {
        tag.innerHTML = htmlOriginal;
    });
}

// === 5. INICIALIZAÇÃO DOS ESCUTADORES DE EVENTOS ===
document.addEventListener("DOMContentLoaded", function() {
    // Monitoramento dinâmico (Event Delegation) de edição inline de MATÉRIAS
    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-ativar-edicao');
        if (btn) {
            const id = btn.getAttribute('data-id');
            const nomeOriginal = btn.getAttribute('data-nome');
            ativarEdicaoMateria(id, nomeOriginal);
        }
    });
});

// ===== ADICIONE AO FINAL DE assets/js/lib/admin_materias.js =====

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