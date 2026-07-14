document.addEventListener('DOMContentLoaded', function (e) {

    let form = document.getElementById('form-add-task');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        let title = document.getElementById('title').value;
        let description = document.getElementById('description').value;

        // const result = addTask(title, description);
        // console.log(result); Nesse formato ele consola uma promisse fulfilled

        const result = await addTask(title, description);
        console.log(result) // Nesse formato desenpacotado, consola o objeto da resposta: {ok: true, msg: 'Tarefa criada com sucesso', task_id: '21'}

        // Se o campo ok vier false, exibir a mensagem que o acompanha
        if (!result.ok) {
            alert(result.msg);
            return;
        }

        // Exibir mensagem de sucesso com o id da tarefa e resetar o formulário
        alert(result.msg + ' Id:' + result.task_id);
        document.getElementById('form-add-task').reset();
        document.getElementById('title').focus();
    });
});

// Single responsability - Só faz post da tarefa para o backend salvar no banco de dados e retorna o resultado
async function addTask(title, description) {

    // Validar o título da tarefa
    let valid_title = await validate_task_title(title);

    if (!valid_title.ok) {
        window.alert(valid_title.msg);
        return false;
    }


    const task = { title, description, action: 'createTask' };


    try {
        // Envia requisição POST para criação da tarefa no banco de dados
        const response = await fetch('../utils/task_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(task)
        });

        // Resposa da requisição
        const data = await response.json();

        // Tratamento e retorno da resposta
        if (data.response_type == 'error') {
            return { ok: false, msg: data.msg };
        }

        return { ok: true, msg: data.msg, task_id: data.task_id };
    } catch (error) {
        // Tratamento de erros de execução
        console.error(error);
        return { ok: false, msg: 'Erro de conexão com o servidor' };
    }
}

// Single responsability - Só valida o título da tarefa conforme regras de negócio
function validate_task_title(title) {
    if (title.length <= 5) {
        return {
            ok: false,
            msg: 'O título precisa fazer algum sentido'
        }
    }

    if (title === '' || title === null || title === undefined || title === false) {
        return {
            ok: false,
            msg: 'O título da tarefa é obrigatório'
        }
    }

    return {
        ok: true,
    }

}