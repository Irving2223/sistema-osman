<?php
include("header.php");
include("conexion.php");

?>
<div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h3 class="mt-4">Listado de Proveedores</h3>
                        <a href="añadir_proveedor.php"><button type="button" class="btn btn-dark">Añadir</button></a>
                       
                        
                        <div class="card mb-4">
                        <br>
                            
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Rif</th>
                                            <th>Correo</th>
                                            <th>Telefono</th>
                                            <th>Acciones</th>
                                            
                                        </tr>
                                    </thead>
                                   
                                    <tbody>

                                    <?php

                                    $sql = "SELECT * FROM proveedores ";
                                    $result = mysqli_query($conexion, $sql);
            
                                     while($row = mysqli_fetch_assoc($result)) { ?>
                                      <tr>
                                      <td> <?php echo $row['nombre']; ?></td>
                                      <td> <?php echo $row['rif']; ?></td>
                                      <td> <?php echo $row['correo']; ?></td>
                                      <td> <?php echo $row['telefono']; ?></td>


                                      <td> <a href="editar_proveedores.php?id_proveedor=<?php echo $row['id_proveedor']; ?> "> <button class="btn btn-warning">
            <i class="fas fa-edit"></i>
        </button></a>
    <a href="javascript:preguntar(<?php echo $row["id_proveedor"]?>)"  > <button type="button" class="btn btn-danger"><i class="fas fa-trash"></i></button></a></td>

                                      <?php


                                    }
                                      ?>
                                           
                                     </tr>
                                        
                                       
                                      
                               
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>



<?php
include("footer.php");

?>