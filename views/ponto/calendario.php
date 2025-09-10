<h1 class="mb-4">Calendário de Pontos</h1>

<!-- Filtro para Admin -->
<?php if ($_SESSION['user_perfil'] === 'admin' && !empty($users)): ?>
<div class="card mb-4">
    <div class="card-header">Filtro</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label for="usuario_id_calendario" class="form-label">Visualizar calendário de:</label>
                <select id="usuario_id_calendario" class="form-select">
                    <!-- O usuário logado é o padrão, mas pode selecionar outros -->
                    <?php
                    $selected_user_id = $_GET['usuario_id'] ?? $_SESSION['user_id'];
                    foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($selected_user_id == $u['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Container do Calendário -->
<div id="calendar" class="bg-white p-3 rounded"></div>

<!-- Modal para Detalhes do Dia -->
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventDetailModalLabel">Detalhes do Dia</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Data:</strong> <span id="eventDate"></span></p>
        <p><strong>Status:</strong> <span id="eventStatus"></span></p>
        <p><strong>Saldo do dia:</strong> <span id="eventSaldo"></span></p>
        <h6>Registros:</h6>
        <ul id="eventRegistros" class="list-group">
        </ul>
      </div>
    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const userSelect = document.getElementById('usuario_id_calendario');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana'
        },
        events: {
            url: '<?= BASE_URL ?>/ponto/calendarioJson',
            extraParams: function() {
                // Adiciona o usuario_id aos parâmetros da requisição
                return {
                    usuario_id: userSelect ? userSelect.value : '<?= $_SESSION['user_id'] ?>'
                };
            }
        },
        eventClick: function(info) {
            // Preenche o modal com os dados do evento
            const props = info.event.extendedProps;
            const date = info.event.start.toLocaleDateString('pt-BR');

            document.getElementById('eventDate').innerText = date;
            document.getElementById('eventStatus').innerText = props.status;
            document.getElementById('eventSaldo').innerText = props.saldo;

            const registrosList = document.getElementById('eventRegistros');
            registrosList.innerHTML = ''; // Limpa a lista
            props.registros.forEach(r => {
                const listItem = document.createElement('li');
                listItem.className = 'list-group-item';
                listItem.innerText = `${r.tipo}: ${r.hora}`;
                registrosList.appendChild(listItem);
            });

            const modal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
            modal.show();
        }
    });

    calendar.render();

    // Se o filtro de usuário existir, adiciona um listener para recarregar os eventos
    if (userSelect) {
        userSelect.addEventListener('change', function() {
            calendar.refetchEvents();
        });
    }
});
</script>
