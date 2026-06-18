// js/scripts.js

const DIAS_MAP = [
    { nome: 'Segunda-Feira', valor: 1 },
    { nome: 'Terça-Feira', valor: 2 },
    { nome: 'Quarta-Feira', valor: 3 },
    { nome: 'Quinta-Feira', valor: 4 },
    { nome: 'Sexta-Feira', valor: 5 },
    { nome: 'Sábado', valor: 6 },
    { nome: 'Domingo', valor: 7 }
];

// Executa imediatamente para definir o tema (evita o "flash" branco no tema escuro)
(function() {
    const savedTheme = localStorage.getItem('site-theme') || 'light';
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark-theme');
    }
})();

function obterDiasSelecionados() {
    const container = document.getElementById('containerDiasSemana');
    if (container && container.dataset.selecionados) {
        try {
            return JSON.parse(container.dataset.selecionados);
        } catch (e) {
            console.error("Erro ao processar dias selecionados do PHP:", e);
        }
    }
    return [1, 2, 3, 4, 5, 6, 7]; // Fallback padrão
}

function atualizarDiasSemana() {
    const inputInicio = document.getElementById('data_inicio').value;
    const inputFim = document.getElementById('data_fim').value;
    const container = document.getElementById('containerDiasSemana');
    
    if (!container) return;

    const diasSelecionadosAnteriormente = obterDiasSelecionados();

    if (!inputInicio || !inputFim || inputInicio.length !== 10 || inputFim.length !== 10) {
        renderizarDias(diasSelecionadosAnteriormente, null);
        return;
    }

    const d1 = new Date(inputInicio + 'T00:00:00');
    const d2 = new Date(inputFim + 'T00:00:00');

    if (d1.getFullYear() < 1000 || d2.getFullYear() < 1000 || d2 < d1) {
        renderizarDias(diasSelecionadosAnteriormente, null);
        return;
    }

    const diffTempo = Math.abs(d2 - d1);
    const diffDias = Math.ceil(diffTempo / (1000 * 60 * 60 * 24));

    if (diffDias < 6) {
        let diasValidos = [];
        let dataVar = new Date(d1.getTime());

        for (let i = 0; i <= diffDias; i++) {
            let numDia = dataVar.getDay(); 
            let diaIso = numDia === 0 ? 7 : numDia;
            if (!diasValidos.includes(diaIso)) {
                diasValidos.push(diaIso);
            }
            dataVar.setDate(dataVar.getDate() + 1);
        }
        renderizarDias(diasSelecionadosAnteriormente, diasValidos);
    } else {
        renderizarDias(diasSelecionadosAnteriormente, null);
    }
}

function renderizarDias(selecionados, filtrados) {
    const container = document.getElementById('containerDiasSemana');
    if (!container) return;
    container.innerHTML = '';

    DIAS_MAP.forEach(dia => {
        const label = document.createElement('label');
        label.className = 'row-item';

        const estaDisponivel = filtrados === null || filtrados.includes(dia.valor);
        if (!estaDisponivel) {
            label.classList.add('dia-desabilitado');
        }

        const span = document.createElement('span');
        span.textContent = dia.nome;

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'dias_semana[]';
        checkbox.value = dia.valor;
        checkbox.className = 'custom-checkbox';
        
        if (estaDisponivel && selecionados.map(Number).includes(dia.valor)) {
            checkbox.checked = true;
        }
        if (!estaDisponivel) {
            checkbox.disabled = true;
        }

        label.appendChild(span);
        label.appendChild(checkbox);
        container.appendChild(label);
    });
}

// Exibe tela de loading customizada para a Edição
function dispararLoadingEdicao(event, url) {
    event.preventDefault();
    document.getElementById('loadingText').innerText = "🛠️ Carregando dados para edição...";
    document.getElementById('loadingSubtext').innerText = "Buscando parâmetros do seu cronograma no banco de dados.";
    document.getElementById('loadingScreen').style.display = "flex";
    window.location.href = url;
}

// Exibe tela de loading customizada para a Exclusão
function dispararLoadingExclusao(url) {
    document.getElementById('loadingText').innerText = "🗑️ Removendo cronograma...";
    document.getElementById('loadingSubtext').innerText = "Limpando os eventos vinculados ao seu Google Agenda.";
    document.getElementById('loadingScreen').style.display = "flex";
    window.location.href = url;
}

function confirmDelete(event, id) {
    event.preventDefault();
    Swal.fire({
        title: 'Tem certeza?',
        text: "Isso removerá a agenda criada no Google Agenda definitivamente!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#7c3aed',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            dispararLoadingExclusao(`?deletar_salvo=${id}`);
        }
    });
}

// Inicialização dos Event Listeners após o carregamento do DOM
document.addEventListener("DOMContentLoaded", function() {
    // Escuta mudanças nos inputs de data para atualizar os dias dinamicamente
    const inputInicio = document.getElementById('data_inicio');
    const inputFim = document.getElementById('data_fim');
    
    if (inputInicio) inputInicio.addEventListener('change', atualizarDiasSemana);
    if (inputFim) inputFim.addEventListener('change', atualizarDiasSemana);

    // Executa a primeira renderização dos dias
    atualizarDiasSemana();

    const modalForm = document.querySelector("#confirmModal form");
    const confirmModal = document.getElementById("confirmModal");
    const loadingScreen = document.getElementById("loadingScreen");

    if (modalForm) {
        modalForm.addEventListener("submit", function() {
            if (confirmModal) confirmModal.style.display = "none";
            
            document.getElementById('loadingText').innerText = "🗓️ Sincronizando com o Google Agenda...";
            document.getElementById('loadingSubtext').innerText = "Isso pode levar alguns segundos dependendo do tamanho do seu cronograma.";
            if (loadingScreen) loadingScreen.style.display = "flex";
        });
    }

    const btnToggleMaterias = document.getElementById('btnToggleMaterias');
    const conteinerMaterias = document.getElementById('conteinerMaterias');

    if (btnToggleMaterias && conteinerMaterias) {
        btnToggleMaterias.addEventListener('click', function() {
            const isVisible = conteinerMaterias.style.display === 'flex';
            conteinerMaterias.style.display = isVisible ? 'none' : 'flex';
            
            const icon = this.querySelector('.fa-chevron-down, .fa-chevron-up');
            if(icon) {
                icon.classList.toggle('fa-chevron-down', isVisible);
                icon.classList.toggle('fa-chevron-up', !isVisible);
            }
        });
    }

    document.querySelectorAll('.materia-accordion-header').forEach(header => {
        header.addEventListener('click', function() {
            const item = this.parentElement;
            item.classList.toggle('active');
            const content = item.querySelector('.materia-accordion-content');
            if (content) {
                if (item.classList.contains('active')) {
                    content.style.maxHeight = content.scrollHeight + "px";
                } else {
                    content.style.maxHeight = "0px";
                }
                
                setTimeout(() => {
                    if(item.classList.contains('active')) {
                        header.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                }, 310);
            }
        });
    });
});

(function () {
        // Aplica o Tema Escuro imediatamente se estiver salvo
        const savedTheme = localStorage.getItem('site-theme') || 'light';
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark-theme');
        }

        // Aplica o Tamanho da Fonte imediatamente se estiver salvo
        const savedSize = localStorage.getItem('site-font-size') || 'normal';
        document.documentElement.classList.add('font-' + savedSize);
    })();