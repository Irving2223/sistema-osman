<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>U.P.Q.L - Inicio De Sesión</title>
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
                                <div class="card-header text-center">
                                    <img src="image/logo.png" class="mb-4" style="max-width: 50%; height: auto;" />
                                    <h3 class="font-weight-light my-4">Unidad de Producción de Química</h3>
                                </div>
                                <div class="card-body">
                                    <form action="login.php" method="post">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" type="text" placeholder="Usuario" name="usuario" maxlength="20" minlength="3" required/>
                                            <label for="inputEmail">Usuario</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="clave" type="password" placeholder="Password" maxlength="16" minlength="6" required/>
                                            <label for="inputPassword">Contraseña</label>
                                        </div>
                                        <div class="col-12">
                                            <?php
                                            session_start(); 
                                            if(isset($_SESSION['status'])) {
                                            ?>
                                                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                                    <?= $_SESSION['status']; ?>
                                                </div>
                                            <?php 
                                                unset($_SESSION['status']);
                                            } 
                                            ?>
                                            <?php
                                            if(isset($_SESSION['error'])) {
                                            ?>
                                                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                                    <?= $_SESSION['error']; ?>
                                                </div>
                                            <?php 
                                                unset($_SESSION['error']);
                                            } 
                                            ?>
                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <a class="small" href="recuperar.php">¿Olvido contraseña?</a>
                                                <button type="submit" class="btn btn-primary">Ingresar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div id="layoutAuthentication_footer">
            <?php include('footer.php'); ?>
        </div>
    </div>
</body>
</html>
