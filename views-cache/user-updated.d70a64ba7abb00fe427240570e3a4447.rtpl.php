<?php if(!class_exists('Rain\Tpl')){exit;}?><!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Mensagem Automatica do Sistema
  </h1>
</section>

<!-- Main content -->
<section class="content">

  <div class="row">
  	<div class="col-md-12">
  		<div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title" style="<?php if( $user_status == 1 ){ ?>color: #00AA00<?php }else{ ?>color: red<?php } ?>"><?php echo htmlspecialchars( $user_msg, ENT_COMPAT, 'UTF-8', FALSE ); ?></h3>
        </div>
        <p>
          <a href="/admin/users/<?php echo htmlspecialchars( $user["iduser"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" class="btn btn-primary btn-xs">
            <i class="fa fa-edit"></i> Editar Usuario
          </a>
        </p>
        <!-- /.box-header -->
        <!-- form start -->
        <form role="form" action="/admin/users/<?php echo htmlspecialchars( $user["iduser"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" method="post">
          <div class="box-body">
            <div class="form-group">
              <label for="desperson">Nome</label>
              <input type="text" class="form-control" value="<?php echo htmlspecialchars( $user["desperson"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" disabled="disabled">
            </div>
            <div class="form-group">
              <label for="deslogin">Login</label>
              <input type="text" class="form-control" value="<?php echo htmlspecialchars( $user["deslogin"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" disabled="disabled">
            </div>
            <div class="form-group">
              <label for="despassword">Senha</label>
              <input type="password" class="form-control" value="<?php echo htmlspecialchars( $user["despassword"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" disabled="disabled">
            </div>
            <div class="form-group">
              <label for="nrphone">Telefone</label>
              <input type="tel" class="form-control" value="<?php echo htmlspecialchars( $user["nrphone"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" disabled="disabled">
            </div>
            <div class="form-group">
              <label for="desemail">E-mail</label>
              <input type="email" class="form-control" value="<?php echo htmlspecialchars( $user["desemail"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" disabled="disabled">
            </div>
            <div class="checkbox">
              <label>
                <input type="checkbox" <?php if( $user["inadmin"] == 1 ){ ?>checked<?php } ?> disabled="disabled">Acesso de Administrador
              </label>
            </div>
          </div>
          <!-- /.box-body -->
      </div>
  	</div>
  </div>

</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->