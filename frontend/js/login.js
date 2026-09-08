// Máscara de CPF automática no login
document.getElementById('cpf').addEventListener('input', function(e) {
    let valor = e.target.value.replace(/\D/g, '');
    if (valor.length > 11) valor = valor.slice(0, 11);
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = valor;
});

document.getElementById('formLogin').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Agora capturamos o CPF em vez do usuário genérico
    const cpf = document.getElementById('cpf').value;
    const senha = document.getElementById('senha').value;
    const mensagemErro = document.getElementById('mensagemErro');
    const btnEntrar = document.getElementById('btnEntrar');
    
    btnEntrar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Autenticando...';
    btnEntrar.disabled = true;
    mensagemErro.style.display = 'none';

    try {
        const resposta = await fetch('../backend/controllers/LoginController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cpf: cpf, senha: senha })
        });

        const dados = await resposta.json();

        if (dados.sucesso) {
            // O PHP agora vai nos dizer para qual painel redirecionar (Paciente, Médico, etc)
            window.location.href = dados.redirecionamento;
        } else {
            mensagemErro.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + dados.mensagem;
            mensagemErro.style.display = 'block';
            btnEntrar.innerHTML = 'Acessar Sistema <i class="fa-solid fa-arrow-right"></i>';
            btnEntrar.disabled = false;
        }

    } catch (erro) {
        console.error(erro);
        mensagemErro.innerText = 'Erro ao conectar com o servidor.';
        mensagemErro.style.display = 'block';
        btnEntrar.disabled = false;
    }
});x