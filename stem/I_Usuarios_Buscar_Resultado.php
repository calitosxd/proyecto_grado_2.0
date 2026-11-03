<?php
// Del BackEnd (L_Validar_Ingreso.php), se recibe los datos "rol" y "permisos" del usuario logueado correctamente.
if (isset($_POST['roles']) && isset($_POST['permisos'])) {
    $rol = $_POST['roles'];
    $permiso = $_POST['permisos'];
} else {
    // Si por algún motivo, no llegan datos, las variables se declaran vacías.
    $rol = null;
    $permiso = null;
}
// Impresion de prueba de que el dato "rol" y "permisos" llegaron correctamente.
//echo "Rol: " . $rol . "<br>";
//echo "Permisos: " . $permiso;
?>


<!--Concatenador con las partes Head-Heater-Aside. -->
<?php
require('ASSETS/PLANTILLAS/Plantilla-Head-Heater-Aside-Admin.php');
/*include('LOGICA/L_Consultar_Personas.php');*/
include('LOGICA/L_Funciones.php');
error_reporting(E_ERROR |  E_PARSE);
?>
<!--Página Consultar Estudiantes (Parte Secttion)-->
<section>
    <div class="Section-css">
    <center><br/>
    <!--Página Buscar Personas (Parte Secttion)--> 
    <?php
    /*Imprime la identificación que se esta buscando.*/
    if(isset($_POST["submit_Buscar_Persona"]))
    {
        if(empty($_POST["id_usuario"]) || is_numeric($_POST["id_usuario"]) === false)
        {
            $errores[] = "La identificación es requerida y no debe contener caracteres alfabéticos.";
        }
        if(isset($errores))
        {
            echo "<center><h2 style ='color: red;'>Por favor revizar los siguientes items:</h2></center></br>";
            /*Se muestra los errores generados al recorrer cada una de las validaciones.*/
            foreach ($errores as $error)
            {
                echo "<label>>>>$error<<<</label></br>";
            }
            echo "</br><a href='javascript:history.back(-1);' title='Ir la página anterior'><center><h3>REGRESAR</h3></center></a>";
        }
        if(empty($errores)) 
        {   
            $id_usuario = filtrado($_POST['id_usuario']);
                        
        }
        /*Con la identificación de la persona se procede a buscar toda la iformación de ella*/
        $datos_usuario = Funcion_Buscar_Usuario($id_usuario);
        /*Se monta el arreglo traido desde la funcion.*/
        foreach ($datos_usuario as $dato_usuario) 
        {

        }
        // El dato_usuario[2], es el arreglo donde en el campo "2", contiene la información de la contraseña, la cual se encuentra encriptada, se realiza el proceso de desencriptación para ser mostrada.
        $contrasena = $dato_usuario[1];
        $contrasena_desencriptada = Funcion_Contrasena_desencriptar($contrasena);

        if (empty($dato_usuario))
        {
        echo "<h2 style='color: red;'>El número de Identificación no se encuentra registrado.<h2/>";
        echo "</br><a href='javascript:history.back(-1);' title='Ir la página anterior'><center><h3>REGRESAR</h3></center></a>";
    }
        else
        {
            ?>
            <!-- ****************************************************************************************************** -->     
            <!-- Identificacion de la Persona en la tabla 'PERSONA' de la base de datos.            
                <h3 style="color:blue;">Persona con Identificación Número:</h3>-->
            <!-- <?php
            /* echo ("<h3 style ='color: red;'>[".$id_persona."]</h3></br>"); */
            ?> -->
            <!-- ****************************************************************************************************** -->
            <!-- Tabla donde se muestra todos los campos de la tabla 'PERSONA' de la base de datos. -->
            <table>
                <th>Identificación</th>
                <th>Edad</th>
                <th>Nombre</th>
                <th>Genero</th>
                <th>Dirección</th>
                <th>Ciudad</th>
                
                
                <?php
                if(isset($_POST["submit_Buscar_Persona"]))
                {
                    echo "<tr>";
                    echo "<td style='color: blue;'>";
                    echo $dato_usuario[0];
                    echo "</td>";
                    echo "<td>";
                    echo $dato_usuario[4];
                    echo "</td>";
                    echo "<td>";
                    echo $dato_usuario[2]." ".$dato_usuario[3];
                    echo "</td>";
                    echo "<td>";
                    echo $dato_usuario[5];
                    echo "</td>";
                    echo "<td>";
                    echo $dato_usuario[7];
                    echo "</td>";
                    echo "<td>";
                    echo $dato_usuario[8];
                    echo "</td>";
                    echo "</tr>";
                    ?>
                
                    <th>Rol</th>
                    <th>Teléfono</th>
                    <th>email</th>
                    <th>Código Barras</th>
                    <th>Estado</th>
                    <th>Fecha Registro</th>
                    <?php
                    echo "<tr>";
                        echo "<td>";
                            //Se traduce el numero del Rol, con su significado en palabras.
                            if ($dato_usuario[15] == 0) 
                                {
                                    echo "Super Admin";
                                
                                }
                            if ($dato_usuario[15] == 1) 
                                {
                                    echo "Administrativo";
                                
                                }
                            elseif ($dato_usuario[15] == 2) 
                                {
                                    echo "Docente";
                                
                                }
                            elseif ($dato_usuario[15] == 3) 
                                {
                                    echo "Estudiante";
                                }
                            elseif ($dato_usuario[15] == 4) 
                                {
                                    echo "Invitado";
                                }
                        echo "</td>";
                        echo "<td>";
                            echo $dato_usuario[6];
                        echo "</td>";
                        echo "<td>";
                            echo $dato_usuario[11];
                        echo "</td>";
                        echo "<td>";
                            ?>    
                            <!-- Se envía el número de identificación de la persona para generar un código de barras --> 
                            <a href="javascript:void(0);" onclick="printBarcode('<?php echo $id_usuario; ?>')">
                             <img src="LOGICA/L_Barcode.php?text=<?php echo $id_usuario; ?>&size=30&orientation=horizontal&codetype=code39&print=true&sizefactor=1"/>
                            </a>

                            <script type="text/javascript">
                                function printBarcode(id_usuario) {
                                    // Crear una nueva ventana
                                    var newWindow = window.open('', '_blank');
                                    // Escribir el contenido HTML con la imagen en la nueva ventana
                                    newWindow.document.write('<html><head><title>Print Barcode</title></head><body>');
                                    newWindow.document.write('<img src="LOGICA/L_Barcode.php?text=' + id_usuario + '&size=30&orientation=horizontal&codetype=code39&print=true&sizefactor=1"/>');
                                    newWindow.document.write('</body></html>');
                                    newWindow.document.close();
                                    // Esperar a que la imagen cargue completamente antes de imprimir
                                    newWindow.onload = function() {
                                        newWindow.focus();
                                        newWindow.print();
                                        newWindow.close();
                                    };
                                }
                            </script>
                            
                            <?php
                        echo "</td>";
                        echo "<td>";
                            if ($dato_usuario[14] == 1) 
                                {
                                    echo " <h3 style='color: green;'> Activo <h3/>";
                                }
                            if ($dato_usuario[14] == 0)
                                {
                                    echo " <h3 style='color: red;'> Inactivo <h3/>";
                                }
                        echo "</td>";
                        echo "<td>";
                            echo $dato_usuario[12];
                        echo "</td>";
                    echo "</tr>";                    
                }
                ?>
            </table>
            <br/>
            <table>
                <th>Universidad</th>
                <th>Programa</th>
                <th>Contraseña</th>
                <th>Permisos del Usuario</th>
                <?php
                echo "<tr>";
                    echo "<td>";
                    echo $dato_usuario[9];
                    echo "<td>";
                    echo $dato_usuario[10];
                    echo "</td>";
                    echo "<td>";
                    echo $contrasena_desencriptada;
                    echo "</td>";
                ?>
                <?php
                    // Muestra todas las posibles combinaciones del CRUD de APP 1.
                    ?>
                    <?php
                    if ($dato_usuario[13] == 0) 
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                    <?php  
                        }
                    elseif ($dato_usuario[13] == 10)
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                    <?php    
                        }                               
                    elseif ($dato_usuario[13] == 5)
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                    <?php    
                        }
                    elseif ($dato_usuario[13] == 15)
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                    <?php    
                        }
                    elseif ($dato_usuario[13] == 3)
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                    <?php    
                        }
                    elseif ($dato_usuario[13] == 13)
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                    <?php    
                        }
                    elseif ($dato_usuario[13] == 8)
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                    <?php    
                        }
                    elseif ($dato_usuario[13] == 18)
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" >Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                    <?php    
                        }
                    elseif ($dato_usuario[13] == 1)
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                    <?php    
                        }
                    elseif ($dato_usuario[13] == 11)
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                    <?php    
                        }
                        elseif ($dato_usuario[13] == 6)
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                    <?php    
                        }
                    elseif ($dato_usuario[13] == 16)
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" >Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                    <?php    
                        }
                    elseif ($dato_usuario[13] == 4)
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                    <?php    
                        }
                    elseif ($dato_usuario[13] == 14)
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                    <?php    
                        }
                    elseif ($dato_usuario[13] == 9)
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" >Eliminar</td>
                    <?php    
                        }
                    elseif ($dato_usuario[13] == 19)
                        {
                    ?>
                        <td><input type="checkbox" name="permiso[]" value="1" disabled="" checked="">Registrar<br/>
                            <input type="checkbox" name="permiso[]" value="2" disabled="" checked="">Ver<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Editar<br/>
                            <input type="checkbox" name="permiso[]" value="3" disabled="" checked="">Eliminar</td>
                    <?php    
                        }
                    ?>    
                <?php
                echo "</tr>"
                ?>
            </table>
            <br/>
            <!-- Botones que editan o eliminan una persona -->

            <!--Se diferencia los permisos-->
            <?php
                if($rol == 0){
            ?>
                    <table>
                        <!-- Permiso para el ROL admin modo dios sobre editar algun usuario. -->
                        <th ROWSPAN="1">
                            <form id="formEditar" method="POST" action="I_Usuarios_Editar.php">
                                <input type="hidden" name="id_usuario" value="<?php echo $dato_usuario[0]; ?>">
                                <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                                <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                                <a href="#" onclick="document.getElementById('formEditar').submit();" title="Editar a este usuario" class="btn">Editar</a>
                            </form>
                        </th>
                        <!-- Permiso para el ROL admin modo dios sobre eliminar algun usuario-->
                        <th ROWSPAN="1">
                            <form id="formEliminar" method="POST" action="LOGICA/L_Usuarios_Borrar.php">
                                <input type="hidden" name="id_usuario" value="<?php echo $dato_usuario[0]; ?>">
                                <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                                <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                                <a href="#" onclick="document.getElementById('formEliminar').submit();" title="Eliminar a este usuario" class="btn">Eliminar</a>
                            </form>
                        </th>
                        <th ROWSPAN="1">
                            <!-- <button onclick="javascript:history.back(-1);" title="Ir a la página anterior" class="btn">Regresar</button> -->
                            <a href='javascript:history.back(-1);' title='Ir la página anterior' class="btn" value="Regresar">Regresar</a>
                        </th>
                    </table>
                    <?php
                        }
                    ?>

            <?php
                if($rol == 1){
            ?>
                    <!-- Permiso para el ROL administrador sobre editar algun usuario. -->
                    <table>
                        <th ROWSPAN="1">
                            <form id="formEditar" method="POST" action="I_Usuarios_Editar.php">
                                <input type="hidden" name="id_usuario" value="<?php echo $dato_usuario[0]; ?>">
                                <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                                <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                                <a href="#" onclick="document.getElementById('formEditar').submit();" title="Editar a este usuario" class="btn">Editar</a>
                            </form>
                        </th>
                        <!-- Permiso para el ROL administrador sobre eliminar algun usuario-->
                        <th ROWSPAN="1">
                            <form id="formEliminar" method="POST" action="LOGICA/L_Usuarios_Borrar.php">
                                <input type="hidden" name="id_usuario" value="<?php echo $dato_usuario[0]; ?>">
                                <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                                <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                                <a href="#" onclick="document.getElementById('formEliminar').submit();" title="Eliminar a este usuario" class="btn">Eliminar</a>
                            </form>
                        </th>
                        <th ROWSPAN="1">
                            <!-- <button onclick="javascript:history.back(-1);" title="Ir a la página anterior" class="btn">Regresar</button> -->
                            <a href='javascript:history.back(-1);' title='Ir la página anterior' class="btn" value="Regresar">Regresar</a>
                        </th>
                    </table>
            <?php
                }
            ?>
            <?php
                if($rol == 2){
            ?>
                <table>
                    <!-- Permiso para el ROL docente sobre editar algun usuario. -->
                    <th ROWSPAN="1">
                        <form id="formEditar" method="POST" action="I_Usuarios_Editar.php">
                            <input type="hidden" name="id_usuario" value="<?php echo $dato_usuario[0]; ?>">
                            <input type="hidden" name="rol" value="<?php echo $rol; ?>">
                            <input type="hidden" name="permiso" value="<?php echo $permiso; ?>">
                            <a href="#" onclick="document.getElementById('formEditar').submit();" title="Editar a este usuario" class="btn">Editar</a>
                        </form>
                    </th>
                    <th ROWSPAN="1">
                        <!-- <button onclick="javascript:history.back(-1);" title="Ir a la página anterior" class="btn">Regresar</button> -->
                        <a href='javascript:history.back(-1);' title='Ir la página anterior' class="btn" value="Regresar">Regresar</a>
                    </th>
                </table>
            <?php
                }
            ?>
    <!--Esta parte viene desde arriba-->
    <?php
        }
    }
    ?>
        <!---------------------------------->
        </table>
    <br/>
</center>
</div>
</section>		
<!--Concatenador con la parte Footer. -->
<?php
require('ASSETS/PLANTILLAS/Plantilla-Footer.html');
error_reporting(E_ERROR |  E_PARSE);
?>
