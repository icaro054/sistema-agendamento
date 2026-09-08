document.getElementById('cpf').addEventListener('input', function(e) {
    let valor = e.target.value.replace(/\D/g, ''); // Remove tudo que não é número
    if (valor.length > 11) valor = valor.slice(0, 11); // Limita a 11 números
    
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = valor;
});

// Máscara de Telefone ((00) 00000-0000)
document.getElementById('telefone').addEventListener('input', function(e) {
    let valor = e.target.value.replace(/\D/g, '');
    if (valor.length > 11) valor = valor.slice(0, 11);
    
    valor = valor.replace(/^(\d{2})(\d)/g, '($1) $2');
    valor = valor.replace(/(\d)(\d{4})$/, '$1-$2');
    e.target.value = valor;
});


document.getElementById('formCadastro').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Máscara de CPF (000.000.000-00)
    
    const btnCadastrar = document.getElementById('btnCadastrar');
    const mensagem = document.getElementById('mensagemCadastro');
    
    // Coleta os dados dos inputs
    const dados = {
        nome: document.getElementById('nome').value,
        cpf: document.getElementById('cpf').value,
        telefone: document.getElementById('telefone').value,
        nascimento: document.getElementById('nascimento').value,
        email: document.getElementById('email').value,
        senha: document.getElementById('senha').value
    };

    btnCadastrar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Salvando...';
    btnCadastrar.disabled = true;
    mensagem.style.display = 'none';

    try {
        const resposta = await fetch('../backend/controllers/CadastroController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });

        const retorno = await resposta.json();

        mensagem.style.display = 'block';
        if (retorno.sucesso) {
            mensagem.style.backgroundColor = '#d1e7dd';
            mensagem.style.color = '#0f5132';
            mensagem.innerHTML = '<i class="fa-solid fa-check"></i> ' + retorno.mensagem;
            
            // Redireciona para o login após 2 segundos
            setTimeout(() => { window.location.href = 'index.html'; }, 2000);
        } else {
            mensagem.style.backgroundColor = '#fee2e2';
            mensagem.style.color = '#dc2626';
            mensagem.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + retorno.mensagem;
            btnCadastrar.innerHTML = 'Concluir Meu Cadastro <i class="fa-solid fa-check"></i>';
            btnCadastrar.disabled = false;
        }
    } catch (erro) {
        console.error(erro);
        mensagem.style.display = 'block';
        mensagem.style.backgroundColor = '#fee2e2';
        mensagem.style.color = '#dc2626';
        mensagem.innerText = 'Erro ao conectar com o servidor.';
        btnCadastrar.disabled = false;
    }
});