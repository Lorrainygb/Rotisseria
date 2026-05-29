document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // CADASTRO
    const btnCadastro = document.getElementById('btn-cadastro');
    if (btnCadastro) {
        btnCadastro.onclick = function() {
            const nome = document.getElementById('nome').value;
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;

            if (!nome || !email || !senha) return alert('Preencha todos os campos!');

            fetch('../api/cadastrar.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({nome, email, senha})
            })
            .then(r => r.json())
            .then(data => {
                alert(data.erro || data.mensagem);
                if (!data.erro) window.location.href = 'login.html';
            })
            .catch(() => alert('Erro de conexão'));
        };
    }

    // ============================================
    // LOGIN
    const btnEntrar = document.getElementById('btn_entrar');
    if (btnEntrar) {
        btnEntrar.onclick = function() {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;

            if (!email || !senha) return abrirModal();

            fetch('../api/login.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({email, senha})
            })
            .then(r => r.json())
            .then(data => {
                if (data.erro) {
                    abrirModal();
                } else {
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('user', JSON.stringify(data));
                    alert('Login OK! ' + data.funcionario.nome);
                }
            })
            .catch(() => alert('Erro de conexão'));
        };
    }

    // ============================================
    // RECUPERAR SENHA
    const btnContinuar = document.getElementById('continuar');
    if (btnContinuar) {
        btnContinuar.onclick = function() {
            const email = document.getElementById('email').value;
            if (!email) return alert('Digite seu email!');

            fetch('../api/recuperar.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({email})
            })
            .then(r => r.json())
            .then(data => {
                alert(data.mensagem);
                if (data.mensagem) {
                    btnContinuar.disabled = true;
                    btnContinuar.textContent = 'Email enviado!';
                }
            })
            .catch(() => alert('Erro de conexão'));
        };
    }

    // ============================================
    // MODAL
    const btnFechar = document.getElementById('fecha-modal');
    if (btnFechar) btnFechar.onclick = fecharModal;
});

// ============================================
// NOVA SENHA (página específica)
if (window.location.pathname.includes('definicao_nova_senha.html')) {
    // ============================================
    // TOKEN + REDEFINIR
    const token = new URLSearchParams(window.location.search).get('token');
    
    if (!token) {
        alert('Link inválido!');
        window.location.href = 'login.html';
    }

    document.getElementById('confirmar').onclick = function() {
        const novaSenha = document.getElementById('nova_senha').value;
        const confirmaSenha = document.getElementById('confirma_nova_senha').value;

        if (!novaSenha || !confirmaSenha) return alert('Preencha todos os campos!');
        if (novaSenha !== confirmaSenha) return alert('Senhas não coincidem!');
        if (novaSenha.length < 6) return alert('Senha deve ter 6+ caracteres!');

        fetch('../api/redefinir.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({token, novaSenha})
        })
        .then(r => r.json())
        .then(data => {
            alert(data.mensagem || data.erro);
            if (data.mensagem) window.location.href = 'login.html';
        })
        .catch(() => alert('Erro de conexão'));
    };
}

// ============================================
// FUNÇÕES GLOBAIS
function abrirModal() {
    const modal = document.getElementById('janela-modal');
    if (modal) modal.style.display = 'flex';
}

function fecharModal() {
    const modal = document.getElementById('janela-modal');
    if (modal) modal.style.display = 'none';
}