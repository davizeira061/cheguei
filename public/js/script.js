document.addEventListener('DOMContentLoaded', function () {
    const confirmacaoModalElement = document.getElementById('confirmacaoModal');

    // Verifique se o modal existe antes de inicializar
    if (confirmacaoModalElement) {
        const confirmacaoModal = new bootstrap.Modal(confirmacaoModalElement);
        const btnConfirmarRegistro = document.getElementById('btn-confirmar-registro');
        const forms = document.querySelectorAll('.registro-ponto-form');
        let formASerEnviado = null;

        forms.forEach(form => {
            form.addEventListener('submit', function (event) {
                event.preventDefault(); // Impede o envio imediato do formulário
                formASerEnviado = this; // Guarda o formulário que acionou o evento

                // Atualiza o corpo do modal para ser mais informativo
                const tipoPonto = this.querySelector('input[name="tipo"]').value.replace('_', ' ');
                const modalBody = confirmacaoModalElement.querySelector('.modal-body');
                modalBody.textContent = `Deseja realmente registrar a ${tipoPonto}?`;

                confirmacaoModal.show();
            });
        });

        if (btnConfirmarRegistro) {
            btnConfirmarRegistro.addEventListener('click', function () {
                if (formASerEnviado) {
                    formASerEnviado.submit(); // Envia o formulário guardado
                }
            });
        }

        // Limpa a referência ao formulário quando o modal é fechado
        confirmacaoModalElement.addEventListener('hidden.bs.modal', function () {
            formASerEnviado = null;
        });
    }
});
