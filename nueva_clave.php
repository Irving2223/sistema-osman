 <?php

session_start();

$id = $_SESSION['id'];


?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Ingresar Nueva Clave</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>
    <body style="background-color: #FF8C42;">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header text-center">
                                        <img src="image\logo.png" class="mb-4" style="max-width: 50%; height: auto; display: block; margin: 0 auto;" />
                                        <h3 class="text-center font-weight-light my-4">Recuperar Contraseña</h3></div>
                                    <div class="card-body">
                                        <div class="small mb-3 text-muted">Ingresa tu nueva contraseña</div>
                                        <form action="update_clave.php" method="post" id="formulario">
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="password" name="clave" type="password" required  />
                                                <label for="inputEmail">Contraseña</label>
                                            </div>
                                              <div class="form-floating mb-3">
                                                <input class="form-control" id="confirm_password" name="clave1" type="password" required />
                                                <label for="inputEmail">Confimrar</label>
                                            </div>
                                            <div id="mensaje" style="color:red;"></div>
                                            <input type="hidden" name="id_usuario" class="form-control" value="<?php echo $id; ?>">
                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <a class="small" href="salir.php">Volver al inicio</a>
                                               <button type="submit" class="btn btn-primary">Recuperar</button>
                                            </div>
                                        </form>
                                    </div>
                                   <script>
        $(document).ready(function() {
            $('#formulario').on('submit', function(event) {
                event.preventDefault(); // Evita el envío del formulario

                var password = $('#password').val();
                var confirmPassword = $('#confirm_password').val();
                var mensaje = '';

                // Validar longitud mínima
                if (password.length < 6) {
                    mensaje = 'La contraseña debe tener al menos 6 caracteres.';
                } else if (confirmPassword.length < 6) {
                    mensaje = 'La confirmación de la contraseña debe tener al menos 6 caracteres.';
                } else if (password !== confirmPassword) {
                    mensaje = 'Las contraseñas no coinciden.';
                } else {
                    
                    this.submit(); // Descomenta esto si deseas enviar el formulario
                }

                $('#mensaje').text(mensaje);
            });
        });
    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
               <?php include('footer.php'); ?>
