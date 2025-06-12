let loans = [];
let nextId = 1;

// Inicializar data atual e carregar dados
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('loanDate').valueAsDate = new Date();
    loadComputers();
    loadLoansFromBackend();
});

// Mostrar painel
function showPanel(panelId) {
    document.querySelectorAll('.content-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
    document.getElementById(panelId).classList.add('active');
    event.target.classList.add('active');
}

// Carregar computadores do banco de dados
async function loadComputers() {
    try {
        const res = await fetch('back/get_computers.php');
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        
        const computers = await res.json();
        const select = document.getElementById('object');
        select.innerHTML = '<option value="">Selecione o objeto</option>';

        computers.forEach(computer => {
            const option = document.createElement('option');
            option.value = computer.nome_pc;
            option.textContent = computer.nome_pc;
            select.appendChild(option);
        });
    } catch (err) {
        console.error('Erro ao carregar computadores:', err);
        alert('Erro ao carregar lista de computadores.');
    }
}

// Submeter novo empréstimo
document.getElementById('loanForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const loan = {
        studentName: document.getElementById('studentName').value,
        course: document.getElementById('course').value,
        object: document.getElementById('object').value,
        loanDate: document.getElementById('loanDate').value,
        returnDate: document.getElementById('returnDate').value,
    };

    if (!loan.object) {
        alert('Por favor, selecione um objeto da lista');
        return;
    }

    try {
        const response = await fetch('back/save_loan.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(loan)
        });

        if (!response.ok) throw new Error(`Erro no servidor: ${response.status}`);
        
        const savedLoan = await response.json();
        savedLoan.id = Number(savedLoan.id); // Força o ID como número

        loans.push(savedLoan);

        this.reset();
        document.getElementById('loanDate').valueAsDate = new Date();

        updateStats();
        renderLoansTable();
        alert('Empréstimo registrado com sucesso! 🎉');
    } catch (error) {
        console.error('Erro ao salvar empréstimo:', error);
        alert('Erro ao salvar empréstimo: ' + error.message);
    }
});

// Devolver item
async function returnItem(id) {
    if (!confirm('Confirmar devolução deste item?')) return;

    try {
        console.log('Enviando para devolução, ID:', id);

        const response = await fetch('back/update_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: Number(id) })
        });

        if (!response.ok) throw new Error(`Erro no servidor: ${response.status}`);

        const updatedLoan = await response.json();
        updatedLoan.id = Number(updatedLoan.id);
        console.log('Resposta do backend:', updatedLoan);

        const index = loans.findIndex(l => l.id === updatedLoan.id);

        if (index !== -1) {
            loans[index] = {
                ...loans[index],
                ...updatedLoan,
                actualReturnDate: new Date().toISOString().split('T')[0]
            };
            updateStats();
            renderLoansTable();
            alert('Item devolvido com sucesso! ✅');
        }
    } catch (error) {
        console.error('Erro ao devolver item:', error);
        alert('Erro ao devolver item: ' + error.message);
    }
}

// Atualizar estatísticas
function updateStats() {
    const total = loans.length;
    const ativos = loans.filter(l => l.status === 'emprestado').length;
    const devolvidos = loans.filter(l => l.status === 'devolvido').length;

    document.getElementById('totalLoans').textContent = total;
    document.getElementById('activeLoans').textContent = ativos;
    document.getElementById('returnedLoans').textContent = devolvidos;
}

// Renderizar tabela
function renderLoansTable(filtered = null) {
    const list = filtered || loans;
    const tbody = document.getElementById('loansTableBody');

    if (list.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="no-data">Nenhum empréstimo encontrado</td></tr>';
        return;
    }

    tbody.innerHTML = list.map(loan => `
        <tr>
            <td>${loan.studentName}</td>
            <td>${loan.course}</td>
            <td>${loan.object}</td>
            <td>${formatDate(loan.loanDate)}</td>
            <td>${formatDate(loan.returnDate)}</td>
            <td><span class="status-badge status-${loan.status}">${loan.status === 'emprestado' ? 'Emprestado' : 'Devolvido'}</span></td>
            <td>
                ${loan.status === 'emprestado'
                    ? `<button class="btn btn-success btn-small" onclick="returnItem(${loan.id})">Devolver</button>`
                    : `<span style="color: #198754;">✅ Devolvido em ${loan.actualReturnDate ? formatDate(loan.actualReturnDate) : formatDate(loan.returnDate)}</span>`
                }
            </td>
        </tr>
    `).join('');
}

// Buscar empréstimos
document.getElementById('searchInput').addEventListener('input', function (e) {
    const term = e.target.value.toLowerCase();
    const filtered = loans.filter(l =>
        l.studentName.toLowerCase().includes(term) ||
        l.course.toLowerCase().includes(term) ||
        l.object.toLowerCase().includes(term)
    );
    renderLoansTable(filtered);
});

// Gerar relatório
function generateReport() {
    const status = document.getElementById('statusFilter').value;
    const course = document.getElementById('courseFilter').value;

    let filtered = [...loans];
    if (status) filtered = filtered.filter(l => l.status === status);
    if (course) filtered = filtered.filter(l => l.course === course);

    const container = document.getElementById('reportResults');

    if (filtered.length === 0) {
        container.innerHTML = '<div class="no-data">Nenhum resultado encontrado para os filtros selecionados</div>';
        return;
    }

    const total = filtered.length;
    const ativos = filtered.filter(l => l.status === 'emprestado').length;
    const devolvidos = filtered.filter(l => l.status === 'devolvido').length;

    container.innerHTML = `
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 8px 25px rgba(0,0,0,0.1);">
            <h3 style="margin-bottom: 20px; color: #333;">📊 Resultado do Relatório</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 25px;">
                <div style="text-align: center; padding: 15px; background: linear-gradient(135deg, #fbbf24, #f59e0b); border-radius: 8px; color: #1a365d;">
                    <div style="font-size: 1.8em;">${total}</div>
                    <div>Total</div>
                </div>
                <div style="text-align: center; padding: 15px; background: linear-gradient(135deg, #1a365d, #2c5282); border-radius: 8px; color: white;">
                    <div style="font-size: 1.8em;">${ativos}</div>
                    <div>Emprestados</div>
                </div>
                <div style="text-align: center; padding: 15px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 8px; color: white;">
                    <div style="font-size: 1.8em;">${devolvidos}</div>
                    <div>Devolvidos</div>
                </div>
            </div>
            <table class="loans-table">
                <thead>
                    <tr><th>Aluno</th><th>Curso</th><th>Objeto</th><th>Data Empréstimo</th><th>Status</th></tr>
                </thead>
                <tbody>
                    ${filtered.map(l => `
                        <tr>
                            <td>${l.studentName}</td>
                            <td>${l.course}</td>
                            <td>${l.object}</td>
                            <td>${formatDate(l.loanDate)}</td>
                            <td><span class="status-badge status-${l.status}">${l.status === 'emprestado' ? 'Emprestado' : 'Devolvido'}</span></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

// Formatador de data
function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr + 'T00:00:00');
    return date.toLocaleDateString('pt-BR');
}

// Carregar empréstimos do banco de dados
async function loadLoansFromBackend() {
    try {
        const res = await fetch('back/get_data.php');
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        
        const data = await res.json();
        if (data.error) throw new Error(data.error);

        loans = data.map(l => ({
            ...l,
            id: Number(l.id),
            actualReturnDate: l.status === 'devolvido' ? (l.actualReturnDate || l.returnDate) : null
        }));

        nextId = loans.reduce((max, l) => Math.max(max, l.id), 0) + 1;

        updateStats();
        renderLoansTable();
    } catch (err) {
        console.error('Erro ao carregar dados:', err);
        alert('Erro ao carregar os dados dos empréstimos: ' + err.message);
    }
}
