<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gerenciador de Objetos de TI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <div class="container py-4">
        <div class="text-center mb-4">
            <h1>💻 Gerenciador de Objetos de TI</h1>
            <p>Sistema de controle de empréstimos de equipamentos tecnológicos</p>
        </div>

        <div class="row text-center mb-4">
            <div class="col-md-4">
                <div class="stat-card p-3 border rounded">
                    <div class="stat-number" id="totalLoans">0</div>
                    <div class="stat-label">Total de Empréstimos</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card p-3 border rounded">
                    <div class="stat-number" id="activeLoans">0</div>
                    <div class="stat-label">Empréstimos Ativos</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card p-3 border rounded">
                    <div class="stat-number" id="returnedLoans">0</div>
                    <div class="stat-label">Devolvidos</div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <button class="btn btn-outline-primary me-2 nav-tab active" onclick="showPanel('novo-emprestimo')">Novo Empréstimo</button>
            <button class="btn btn-outline-secondary nav-tab" onclick="showPanel('lista-emprestimos')">Lista de Empréstimos</button>
        </div>

        <!-- Painel Novo Empréstimo -->
        <div id="novo-emprestimo" class="content-panel active">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>📋 Novo Empréstimo</h2>
                <!-- Botão para abrir modal Bootstrap -->
                <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addObjectModal">
                    ➕ Adicionar Objeto
                </button>
            </div>

            <form id="loanForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="studentName" class="form-label">Nome do Aluno</label>
                        <input type="text" class="form-control" id="studentName" required placeholder="Digite o nome completo" />
                    </div>
                    <div class="col-md-6">
                        <label for="course" class="form-label">Curso</label>
                        <select id="course" class="form-select" required>
                            <option value="">Selecione o curso</option>
                            <option>Análise e Desenvolvimento de Sistemas</option>
                            <option>Ciência da Computação</option>
                            <option>Engenharia de Software</option>
                            <option>Sistemas de Informação</option>
                            <option>Redes de Computadores</option>
                            <option>Enfermagem</option>
                            <option>Administração</option>
                            <option>Gestão Financeira</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="object" class="form-label">Objeto</label>
                        <select id="object" class="form-select" required>
                            <option value="">Selecione o objeto</option>
                            <!-- Opções adicionadas dinamicamente -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="loanDate" class="form-label">Data do Empréstimo</label>
                        <input type="date" id="loanDate" class="form-control" required />
                    </div>
                    <div class="col-md-3">
                        <label for="returnDate" class="form-label">Data Prevista de Devolução</label>
                        <input type="date" id="returnDate" class="form-control" required />
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Registrar Empréstimo</button>
            </form>
        </div>

        <!-- Painel Lista de Empréstimos -->
        <div id="lista-emprestimos" class="content-panel" style="display: none;">
            <h2 class="mb-3">📊 Lista de Empréstimos</h2>
            <input type="text" id="searchInput" class="form-control mb-3" placeholder="Buscar por nome do aluno, curso ou objeto..." />
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Aluno</th>
                            <th>Curso</th>
                            <th>Objeto</th>
                            <th>Data Empréstimo</th>
                            <th>Data Devolução</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="loansTableBody">
                        <tr>
                            <td colspan="7" class="text-center">Nenhum empréstimo registrado ainda</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Bootstrap -->
        <div class="modal fade" id="addObjectModal" tabindex="-1" aria-labelledby="addObjectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="simpleObjectForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addObjectModalLabel">Adicionar Novo Objeto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <label for="newObjectName" class="form-label">Nome do Objeto</label>
                            <input type="text" name="nome_pc" id="newObjectName" class="form-control" required placeholder="Ex: PC 01" />
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Salvar Objeto</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <script src="get.js"></script>
   <script>
    document.getElementById('simpleObjectForm').addEventListener('submit', function (e) {
        e.preventDefault();
        
        const nome = document.getElementById('newObjectName').value.trim();
        if (nome) {
            fetch('back/save_object.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `nome_pc=${encodeURIComponent(nome)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const select = document.getElementById('object');
                    const option = document.createElement('option');
                    option.value = nome;
                    option.textContent = nome;
                    select.appendChild(option);
                    select.value = nome;

                    const modal = bootstrap.Modal.getInstance(document.getElementById('addObjectModal'));
                    modal.hide();
                    document.getElementById('simpleObjectForm').reset();
                } else {
                    alert("Erro: " + data.message);
                }
            })
            .catch(error => console.error('Erro:', error));
        }
    });

    function showPanel(panelId) {
        document.querySelectorAll('.content-panel').forEach(p => p.style.display = 'none');
        document.getElementById(panelId).style.display = 'block';
        document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
        document.querySelector(`.nav-tab[onclick="showPanel('${panelId}')"]`).classList.add('active');
    }
</script>

</body>
</html>
