<div class="modal" tabindex="-1" role="dialog" id="modal-delete-{{$p->id}}">

	{{Form::Open(array('action'=>array('ProdutosOutputsController@destroy',$p->id),'method'=>'delete'))}}

  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Atenção</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Excluir saída de produto?</p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">excluir</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">cancelar</button>
      </div>
    </div>
  </div>

	{{Form::Close()}}

</div>
