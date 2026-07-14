document.addEventListener('DOMContentLoaded', function (e) {
    // CARREGAR A LISTA DE TAREFAS DO USUÁRIO
    refresh_tasks_list()


    let form_add_task = document.getElementById('form-add-task');
    let div_list_of_user_tasks = document.getElementById('list-of-user-tasks');


    // EVENTO: ENVIO DE FORMULÁRIO PARA CADASTRAR UMA TAREFA
    form_add_task.addEventListener('submit', async function (e) {
        e.preventDefault();

        let title = document.getElementById('title').value;
        let description = document.getElementById('description').value;

        const result = await addTask(title, description);


        // Se o campo ok vier false, exibir a mensagem que o acompanha
        if (!result.ok) {
            alert(result.msg);
            return;
        }

        // Exibir mensagem de sucesso com o id da tarefa e resetar o formulário
        alert(result.msg + ' Id:' + result.task_id);
        refresh_tasks_list();
        document.getElementById('form-add-task').reset();
        document.getElementById('title').focus();
    });

    // EVENTO: CLIQUE NOS BOTÕES DE AÇÃO
    div_list_of_user_tasks.addEventListener('click', async function (e) {

        // EVENTO: COMPLETAR UMA TAREFA
        if (e.target.classList.contains('btn-complete-task')) {
            let btn_complete_task = e.target;
            let task_id = btn_complete_task.dataset['task_id'];

            btn_complete_task.innerText = 'Aguarde...'
            let result = await completeTask(task_id);

            if (result.ok) {
                refresh_tasks_list()
            } else {
                console.error(result.msg)
            }
        }

        // EVENTO: DELETAR UMA TAREFA
        if (e.target.classList.contains('btn-delete-task')) {
            let btn_complete_task = e.target;
            let task_id = btn_complete_task.dataset['task_id'];

            alert('Deletar:' + task_id)
        }

        // EVENTO: ARQUIVAR UMA TAREFA
        if (e.target.classList.contains('btn-archive-task')) {
            let btn_archive_task = e.target;
            let task_id = btn_archive_task.dataset['task_id'];

            btn_archive_task.innerText = 'Arquivando...';
            let result = await btn_archive_task(task_id);
        }
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

// Single responability - Só busca as tarefas e retorna
async function getAllTaksByUserId() {
    const payload = { action: 'getAllTaksByUserId' }

    const response = await fetch('../utils/task_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    // Resposa da requisição
    const data = await response.json();

    if (data.response_type !== 'success') {
        return { ok: false, msg: data.msg };
    }

    return { ok: true, msg: data.msg, tasks: data.tasks };
}


// Single responsability - Só envia requisição POST para completar a tarefa
async function completeTask(task_id) {

    try {
        // Envia requisição POST para completar a tarefa
        const payload = { task_id, action: 'completeTask' }

        await new Promise(resolve => setTimeout(resolve, 1500));

        const response = await fetch('../utils/task_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        return { ok: true, msg: data.msg, task_id: data.task_id }

    } catch (error) {
        console.error(error);
        return { ok: false, msg: 'Erro de conexão com o servidor' }
    }
}


// Single responsability - Só envia requisição POST para arquivar a tarefa
async function archiveTask(task_id) {

}


async function refresh_tasks_list() {
    let data = await getAllTaksByUserId();

    const div_list_of_tasks = document.getElementById('list-of-user-tasks');

    if (data.ok) {
        let user_tasks = JSON.parse(data.tasks);

        let template = '';

        user_tasks.forEach(task => {
            let task_status_class = undefined;
            let task_status_formated = undefined;
            let btns_visibilty = {
                btn_complete_task: '',
                btn_delete_task: '',
                btn_archive_task: '',
                btn_edit_task: ''
            };

            switch (task.status) {
                case 'pending':
                    task_status_class = 'task_pending';
                    task_status_formated = 'Pendente';

                    btns_visibilty['btn_complete_task'] = '';
                    btns_visibilty['btn_delete_task'] = '';
                    btns_visibilty['btn_archive_task'] = '';
                    btns_visibilty['btn_edit_task'] = '';
                    break;
                case 'done':
                    task_status_class = 'task_done';
                    task_status_formated = 'Concluída';

                    btns_visibilty['btn_complete_task'] = 'hidden';
                    btns_visibilty['btn_delete_task'] = 'hidden';
                    btns_visibilty['btn_archive_task'] = '';
                    btns_visibilty['btn_edit_task'] = 'hidden';
                    break;
                default:
                    task_status_class = 'task_undefined';
                    task_status_formated = 'Indefinida';

                    btns_visibilty['btn_complete_task'] = 'hidden';
                    btns_visibilty['btn_delete_task'] = '';
                    btns_visibilty['btn_archive_task'] = '';
                    btns_visibilty['btn_edit_task'] = '';
                    break;
            }

            template += `
            <div class="task-item">
                <h2>${task.title}</h2>
                <p>${task.description}</p>
                
                <div class="task_action_butons">
                    <div>
                        <button class="btn-complete-task ${btns_visibilty['btn_complete_task']}" data-task_id="${task.id}">Concluir</button>

                        <button class="btn-edit-task ${btns_visibilty['btn_edit_task']}" data-task_id="${task.id}">Editar</button>

                        <button class="btn-delete-task ${btns_visibilty['btn_delete_task']}" data-task_id="${task.id}">Deletar</button>

                        <button class="btn-archive-task ${btns_visibilty['btn_archive_task']}" data-task_id="${task.id}">Arquivar</button>
                    </div>

                    <span class="${task_status_class}">
                        ${task_status_formated}
                    </span>
                </div>
            </div>`
        });

        div_list_of_tasks.innerHTML = template;
        return;

    } else {
        div_list_of_tasks.innerHTML = `
            <div class="error-banner">
                ${data.msg}
            </div>
        `
        return;
    }
}