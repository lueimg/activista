<!-- /.modal -->
<div class="modal fade" id="grupoModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header logo">
        <button class="btn btn-sm btn-default pull-right" data-dismiss="modal">
            <i class="fa fa-close"></i>
        </button>
        <h4 class="modal-title">New message</h4>
      </div>
      <div class="modal-body">
        <form id="form_grupo" name="form_grupo" action="" method="post">
          <div class="form-group">
            <label class="control-label">Tipo Equipo
                <a id="error_nombre" style="display:none" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="bottom" title="Ingrese Nombre">
                    <i class="fa fa-exclamation"></i>
                </a>
            </label>
            <select class="form-control" id='slct_grupo' name="slct_grupo" onChange='ValidaTerritorial(this.value);'>
              <option value="">.::Seleccione::.</option>
            </select>
          </div>
          <div class="form-group">
            <label class="control-label">Nombre del Equipo
                <a id="error_nombre" style="display:none" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="bottom" title="Ingrese Nombre">
                    <i class="fa fa-exclamation"></i>
                </a>
            </label>
            <input type="text" class="form-control" placeholder="Ingrese Nombre" name="txt_nombre" id="txt_nombre">
          </div>
          <div class="form-group ocultar">
            <label class="control-label">Región</label>
            <select class="form-control" id='slct_region' name="slct_region" onChange='CargarProvincia(this.value)'>
              <option value="">.::Seleccione::.</option>
            </select>
          </div>
          <div class="form-group ocultar">
            <label class="control-label">Provincia</label>
            <select class="form-control" id='slct_provincia' name="slct_provincia" onChange='CargarDistrito(this.value)'>
              <option value="">.::Seleccione::.</option>
            </select>
          </div>
          <div class="form-group ocultar">
            <label class="control-label">Distrito</label>
            <select class="form-control" id='slct_distrito' name="slct_distrito">
              <option value="">.::Seleccione::.</option>
            </select>
          </div>
          <div class="form-group ocultar">
            <label class="control-label">Localidad</label>
            <input type="text" class="form-control" placeholder="Ingrese Localidad" name="txt_localidad" id="txt_localidad">
          </div>
          <div class="form-group ocultar">
            <label class="control-label">Dirección</label>
            <input type="text" class="form-control" placeholder="Ingrese Dirección" name="txt_direccion" id="txt_direccion">
          </div>
          <div class="form-group ocultar">
            <label class="control-label">Teléfono</label>
            <input type="text" class="form-control" placeholder="Ingrese Teléfono" name="txt_telefono" id="txt_telefono">
          </div>
          <div class="form-group">
            <label class="control-label">Estado:
            </label>
            <select class="form-control" name="slct_estado" id="slct_estado">
                <option value='0'>Inactivo</option>
                <option value='1' selected>Activo</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Guardar</button>
      </div>
    </div>
  </div>
</div>
<!-- /.modal -->
