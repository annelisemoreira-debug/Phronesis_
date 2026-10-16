<?php // espera $lista_materias_db (id_materia, nome_materia, cor_hex) ?>
<div class="menu-materias" id="menuMaterias">
    <div class="menu-materias-head">
        <strong>Matérias</strong>
        <button type="button" id="btnVerTodas">Ver todas</button>
    </div>
    <ul>
        <?php foreach ($lista_materias_db as $m): $cor = $m['cor_hex'] ?? '#7c3aed'; ?>
        <li>
            <label>
                <span class="mm-icone" style="background:<?= htmlspecialchars($cor) ?>">
                    <i class="fa-solid fa-book"></i>
                </span>
                <span class="mm-nome"><?= htmlspecialchars($m['nome_materia']) ?></span>
                <input type="checkbox" class="custom-checkbox mm-check" value="<?= $m['id_materia'] ?>" checked>
            </label>
        </li>
        <?php endforeach; ?>
    </ul>
</div>