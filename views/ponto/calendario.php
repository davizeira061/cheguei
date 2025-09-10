<!-- CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">

<!-- JS -->
<script src="<?= BASE_URL ?>/js/script.js"></script>

<!-- Título -->
<h1 class="mb-4">Calendário de Pontos</h1>

<!-- Filtro apenas para Admin -->
<?php if ($_SESSION['user_perfil'] === 'admin' && !empty($users)): ?>
<div class="card mb-4">
    <div class="card-header">Filtro</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label for="usuario_id_calendario" class="form-label">Visualizar calendário de:</label>
                <select id="usuario_id_calendario" class="form-select">
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

<!-- Container do calendário -->
<div id="calendar" class="bg-white p-3 rounded"></div>

<!-- Modal Detalhes -->
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalhes do Dia</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Data:</strong> <span id="eventDate"></span></p>
        <p><strong>Status:</strong> <span id="eventStatus"></span></p>
        <p><strong>Saldo do dia:</strong> <span id="eventSaldo"></span></p>
        <h6>Registros:</h6>
        <ul id="eventRegistros" class="list-group"></ul>
      </div>
    </div>
  </div>
</div>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const userSelect = document.getElementById('usuario_id_calendario');
    const initialUserId = userSelect ? userSelect.value : '<?= $_SESSION['user_id'] ?>';

    // Fonte de eventos dinâmica
    const createEventSource = (userId) => ({
        url: '<?= BASE_URL ?>/ponto/calendarioJson',
        method: 'GET',
        extraParams: { usuario_id: userId, _: new Date().getTime() },
        failure: () => alert('Erro ao carregar eventos!')
    });

    // Instancia o calendário
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        buttonText: { today: 'Hoje', month: 'Mês', week: 'Semana' },
        eventSources: [ createEventSource(initialUserId) ],

        eventClick: function(info) {
            const props = info.event.extendedProps;
            const date = info.event.start
                ? info.event.start.toLocaleDateString('pt-BR')
                : '';

            document.getElementById('eventDate').innerText = date;
            document.getElementById('eventStatus').innerText = props.status || '—';
            document.getElementById('eventSaldo').innerText = props.saldo || '—';

            const registrosList = document.getElementById('eventRegistros');
            registrosList.innerHTML = '';

            if (props.registros && props.registros.length > 0) {
                props.registros.forEach(r => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item';
                    li.innerText = `${r.tipo}: ${r.hora}`;
                    registrosList.appendChild(li);
                });
            } else {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.innerText = 'Nenhum registro detalhado.';
                registrosList.appendChild(li);
            }

            new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
        }
    });

    calendar.render();

    // Filtro de usuários (se admin)
    if (userSelect) {
        userSelect.addEventListener('change', function() {
            calendar.removeAllEventSources();
            calendar.addEventSource(createEventSource(this.value));
            calendar.refetchEvents();
        });
    }
});
</script>
