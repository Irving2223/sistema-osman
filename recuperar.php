<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Recuperar Contraseña</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body style="background-color: #FF8C42;">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header">
                                    <div class="text-center">
                                        <img src="image/logo.png" class="mb-4" style="max-width: 50%; height: auto; display: inline-block;" />
                                    </div>
                                    <h3 class="text-center font-weight-light my-4">Recuperar Contraseña</h3></div>
                                    <div class="card-body">
                                        <div class="small mb-3 text-muted">Ingresa tu usuario.</div>
                                        <form action="recuperar_clave.php" method="post">
                                            <div class="form-floating mb-3">
                                                <input class="form-control" name="usuario" type="text" placeholder="name@example.com" maxlength="20" minlength="3" required/>
                                                <label for="inputEmail">Usuario</label>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <a class="small" href="index.php">Volver al inicio</a>
                                               <button type="submit" class="btn btn-primary">Recuperar</button>
                                            </div>
                                        </form>
                                    </div>
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <?php include('footer.php'); ?>
           

