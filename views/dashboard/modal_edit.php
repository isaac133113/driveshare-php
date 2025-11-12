<div class="modal-header">
    <h5 class="modal-title">Editar Ruta</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <form id="editRutaForm">
        <input type="hidden" name="id" value="<?= $ruta['id'] ?>">
        <div class="mb-3">
            <label>Data</label>
            <input type="date" name="data_ruta" class="form-control" value="<?= $ruta['data_ruta'] ?>">
        </div>
        <div class="mb-3">
            <label>Hora Inici</label>
            <input type="time" name="hora_inici" class="form-control" value="<?= $ruta['hora_inici'] ?>">
        </div>
        <div class="mb-3">
            <label>Hora Fi</label>
            <input type="time" name="hora_fi" class="form-control" value="<?= $ruta['hora_fi'] ?>">
        </div>
        <div class="mb-3">
            <label>Origen</label>
            <input type="text" name="origen" class="form-control" value="<?= $ruta['origen'] ?>">
        </div>
        <div class="mb-3">
            <label>Desti</label>
            <input type="text" name="desti" class="form-control" value="<?= $ruta['desti'] ?>">
        </div>
        <div class="mb-3">
            <label for="plazas_disponibles" class="form-label">Plazas disponibles</label>
            <input type="number" class="form-control" id="plazas_disponibles" name="plazas_disponibles"
                value="<?= htmlspecialchars($ruta['plazas_disponibles']) ?>" min="1">
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>

<script>
document.getElementById('editRutaForm').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    fetch('horaris.php?action=updateAjax', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Ruta actualitzada correctament!');
                location.reload(); // o actualizar solo la fila
            } else {
                alert('Error al actualitzar la ruta.');
            }
        });
});
</script>
