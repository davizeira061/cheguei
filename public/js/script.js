document.addEventListener('DOMContentLoaded', function () {
    const confirmacaoModalElement = document.getElementById('confirmacaoModal');

    if (confirmacaoModalElement) {
        const confirmacaoModal = new bootstrap.Modal(confirmacaoModalElement);
        const btnConfirmarRegistro = document.getElementById('btn-confirmar-registro');
        const forms = document.querySelectorAll('.registro-ponto-form');
        let formASerEnviado = null;

        forms.forEach(form => {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                formASerEnviado = this;

                // Limpa quaisquer dados de geolocalização de tentativas anteriores
                const oldGeoInputs = formASerEnviado.querySelectorAll('.geo-input');
                oldGeoInputs.forEach(input => input.remove());

                const tipoPonto = this.querySelector('input[name="tipo"]').value.replace(/_/g, ' ');
                const modalBody = confirmacaoModalElement.querySelector('.modal-body');
                modalBody.textContent = `Deseja realmente registrar a ${tipoPonto}?`;

                // Tenta obter a geolocalização do navegador
                if ('geolocation' in navigator) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            // Sucesso: adiciona os dados ao formulário
                            const { latitude, longitude } = position.coords;

                            const latInput = document.createElement('input');
                            latInput.type = 'hidden';
                            latInput.name = 'latitude';
                            latInput.value = latitude.toFixed(8);
                            latInput.className = 'geo-input';

                            const lonInput = document.createElement('input');
                            lonInput.type = 'hidden';
                            lonInput.name = 'longitude';
                            lonInput.value = longitude.toFixed(8);
                            lonInput.className = 'geo-input';

                            const sourceInput = document.createElement('input');
                            sourceInput.type = 'hidden';
                            sourceInput.name = 'location_source';
                            sourceInput.value = 'browser';
                            sourceInput.className = 'geo-input';

                            formASerEnviado.appendChild(latInput);
                            formASerEnviado.appendChild(lonInput);
                            formASerEnviado.appendChild(sourceInput);

                            // Mostra o modal após obter a localização
                            confirmacaoModal.show();
                        },
                        () => {
                            // Erro ou permissão negada: apenas mostra o modal
                            confirmacaoModal.show();
                        },
                        { timeout: 5000, enableHighAccuracy: true }
                    );
                } else {
                    // Geolocation não é suportado: apenas mostra o modal
                    confirmacaoModal.show();
                }
            });
        });

        if (btnConfirmarRegistro) {
            btnConfirmarRegistro.addEventListener('click', function () {
                if (formASerEnviado) {
                    formASerEnviado.submit();
                }
            });
        }

        confirmacaoModalElement.addEventListener('hidden.bs.modal', function () {
            formASerEnviado = null;
        });
    }
});
